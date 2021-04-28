<?php

/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Mailer\Email;
use Cake\ORM\TableRegistry;

class SentsController extends AppController
{

    public function initialize()
    {
        parent::initialize();

        $this->Auth->allow('add');

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'Crud.Index',
                'Crud.Edit',
                'Crud.View',
                'Crud.Add',
                'Crud.Delete',
            ],
            'listeners' => [
                'Crud.Api',
                'Crud.ApiPagination',
            ],
        ]);

        $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');
    }

    public function index()
    {

        $mails = $this->Sents->find('all');

        $this->set([
            'success' => true,
            'data' => [
                'mails' => $mails,
            ],
            '_serialize' => ['success', 'data'],
        ]);
    }

    public function add()
    {

        $data = $this->getRequest()->getData();

        $this->Crud->on('beforeSave', function (Event $event) use ($data) {

            $entity = $event->getSubject()->entity;
            $patched = [];
            $authUser = $this->_getAuthUser();
            $sitename = Configure::check('Site.name') ? Configure::read('Site.name') : __('My website');
            $defaultSubject = __('Mail from {0}', $sitename);

            /**
             * distinguish between different types of mail
             */
            if (!isset($authUser) && isset($data['user'])) {
                /**
                 * mail sent from landing page form AND unauthenticated user
                 * will auto create a new user since $data['user'] exists
                 */
                $admins = [];
                foreach ($this->_getAdmins() as $admin) {
                    $admins[$admin['email']] = $admin['name'];
                }

                /**
                 * check if the user already exists to prevent autocreation
                 */
                $user = $this->Sents->Users->find()
                    ->where(['Users.email' => $data['user']['email']])
                    ->first();

                if (isset($user)) {
                    /**
                     * user exists
                     * modify entity in order to stop autocreation
                     */
                    $entity->get('user')->isNew(false);
                    $entity->set('user', $user);

                    $from = [$user['email'] => $user['name']];
                    $name = $user['name'];
                    $subject = __('User {0}: {1}', $name, isset($data['subject']) ? $data['subject'] : '');
                } else {
                    /**
                     * new user will be autocreated
                     *
                     */
                    $from = [$data['user']['email'] => $data['user']['name']];
                    $name = $data['user']['name'];
                    $subject = __('New User {0}: {1}', $name, isset($data['subject']) ? $data['subject'] : '');

                    /**
                     * give the new user a password and role
                     *
                     */
                    $patched = array_merge($patched, [
                        'user' => [
                            'password' => randomString(),
                            'group_id' => $this->_getRoleIdFromName(),
                        ],
                    ]);
                }
                $to = $admins;
                $template = 'from-user';
            } else if (isset($authUser)) {
                /**
                 * check if recipient exists
                 */
                $user = $this->Sents->Users->find()
                    ->where(['Users.email' => $data['email']])
                    ->toArray();

                if (!empty($user)) {
                    $username = $user[0]->name;
                } else {
                    throw new NotFoundException();
                }

                /**
                 * mail created from authenticated user
                 */
                $from = [$authUser['email'] => $authUser['name']];
                $to = [$data['email'] => $username];
                $name = $username;
                $subject = isset($data['subject']) ? $data['subject'] : __('General Information');

                $patched = array_merge($patched, [
                    'user_id' => $authUser['id'],
                ]);
            }

            if (!isset($to, $from)) {
                $event->stopPropagation();
                throw new ForbiddenException();
            }

            /**
             *  template to be used
             */
            if (!isset($template)) {
                if (isset($data['template']['slug'])) {
                    $template = $data['template']['slug'] === 'magic-link' ? 'magic-link' : 'general';
                }

                if (!isset($template)) {
                    $template = 'general';
                }

                $templateData = isset($data['template']['data']) ? $data['template']['data'] : '';
            }
            if (!isset($templateData)) {
                $templateData = '';
            }

            $mail = new Email();
            $mail->viewBuilder()->setTemplate($template);
            $mail->viewBuilder()->setLayout('physio-layout');

            /**
             *  View Vars
             */
            $logo = Configure::read('Site.logo');
            $subject = isset($subject) ? $subject : $defaultSubject;
            $beforeContent = isset($data['before-content']) ? $data['before-content'] : '';
            $content = isset($data['content']) ? $data['content'] : __('No message');
            $afterContent = isset($data['after-content']) ? $data['after-content'] : '';
            $beforeSitename = isset($data['before-sitename']) ? $data['before-sitename'] : '';
            $afterSitename = isset($data['after-sitename']) ? $data['after-sitename'] : '';
            $beforeFooter = isset($data['before-footer']) ? $data['before-footer'] : '';
            $footer = isset($data['footer']) ? $data['footer'] : '';
            $afterFooter = isset($data['after-footer']) ? $data['after-footer'] : '';

            $viewVars = compact([
                'logo',
                'name',
                'subject',
                'beforeContent',
                'content',
                'afterContent',
                'beforeSitename',
                'sitename',
                'afterSitename',
                'beforeFooter',
                'footer',
                'afterFooter',
                'templateData',
            ]);

            $message = $mail
                ->setFrom($from)
                ->setTo($to)
                ->setSubject($subject)
                ->setEmailFormat('html')
                ->setViewVars($viewVars)
                ->send();

            /**
             * Cake creates mail with header and message properties
             * extend it by a subject property and
             * save mail to database
             */
            $message['subject'] = $subject;
            $this->Sents->patchEntity($entity, array_merge($patched, [
                '_to' => implode(';', array_keys($to)),
                '_from' => implode(';', array_keys($from)),
                '_read' => 0,
                'message' => json_encode($message),
            ]));

        });

        $this->Crud->on('afterSave', function (Event $event) {

            if (!$event->getSubject()->success) {
                return;
            }

            $entity = $event->getSubject()->entity;
            $_to = explode(';', $entity->get('_to'));

            foreach ($_to as $key => $value) {
                $user = $this->Sents->Users
                    ->find()
                    ->select(['id'])
                    ->where(['Users.email' => $value])
                    ->first();

                if ($user) {
                    $inboxTable = TableRegistry::getTableLocator()->get('Inboxes');
                    $newInbox = $inboxTable->newEntity([
                        'user_id' => $user->id,
                        '_from' => $entity->get('_from'),
                        '_to' => $value,
                        '_read' => 0,
                        'message' => $entity->get('message'),
                    ]);
                    $saved = $inboxTable->save($newInbox);
                }
            }
        });

        return $this->Crud->execute();
    }

    public function get($id)
    {

        $received = $this->Sents->find('byIdOrEmail', ['field' => $id]);

        $this->set([
            'success' => true,
            'data' => $received,
            '_serialize' => ['success', 'data'],
        ]);

    }

}
