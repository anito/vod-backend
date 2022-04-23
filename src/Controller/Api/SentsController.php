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
use Cake\Mailer\Mailer;
use Cake\ORM\TableRegistry;

class SentsController extends AppController
{

  public function initialize(): void
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
    ]);
    $this->viewBuilder()->setOption('serialize', ['success', 'data']);
  }

  public function add()
  {

    $data = $this->getRequest()->getData();

    $this->Crud->on('beforeSave', function (Event $event) use ($data) {

      $entity = $event->getSubject()->entity;
      $patched = [];
      $authUser = $this->_getAuthUser();
      $sitename = Configure::check('Site.name') ? Configure::read('Site.name') : __('My website');
      $defaultSubject = __('General Information');
      $newUser = false;

      /**
       * distinguish between different types of mail/users sending the mail:
       * only admins sending a token, if authenticated we can assume they are of role admin
       * non-admin: not sending a token
       * although a non-admin (role user) could be logged in and send the form,
       * he won't be authenticated (since the missing token)
       * - non-admins should have a $data['user'] object in their payload
       */
      if (!isset($authUser) && isset($data['user'])) {
        /**
         * mail sent from landing page form AND unauthenticated user
         * will auto create a new user from $data['user'] information
         */
        $admins = [];
        foreach ($this->_getAdmins() as $admin) {
          $admins[$admin['email']] = $admin['name'];
        }

        /**
         * check if the user already exists to prevent user autocreation
         */
        $user = $this->Sents->Users->find()
          ->where(['Users.email' => $data['user']['email']])
          ->first();

        if (isset($user)) {
          /**
           * user exists
           * modify entity in order to stop autocreation
           * 
           */
          $entity->get('user')->isNew(false);
          $entity->set('user', $user);

          $from = [$user['email'] => $user['name']];
          $name = $user['name'];
          $subject = __('Message from: {0}', $name);
          $data['before-content'] = isset($data['subject']) ? $data['subject'] : '';
        } else {
          $newUser = true;

          $from = [$data['user']['email'] => $data['user']['name']];
          $name = $data['user']['name'];
          $subject = __('New User: {0}', $name);
          $data['before-content'] = isset($data['subject']) ? $data['subject'] : '';

          /**
           * force autogeneration of a new user by providing user property
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
        $template = isset($data['template']) ? $data['template'] : 'from-user';
      } else if (isset($authUser)) {
        /**
         * Recipient
         */
        $user = $this->Sents->Users->find()
          ->where(['Users.email' => $data['email']])
          ->first();

        if (!isset($user)) {
          throw new NotFoundException();
        }

        /**
         * mail created from authenticated user
         */
        $from = [$authUser['email'] => $authUser['name']];
        $to = [$data['email'] => $user->name];
        $name = $user->name;
        $subject = isset($data['subject']) ? $data['subject'] : $defaultSubject;

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
        $template = isset($data['template']['slug']) ? $data['template']['slug'] : 'general';
      }
      $templateData = isset($data['template']['data']) ? $data['template']['data'] : '';

      $mail = new Mailer();
      $mail->viewBuilder()->setTemplate($template);
      $mail->viewBuilder()->setLayout('physio-layout');

      /**
       *  View Vars
       */
      $logo = Configure::read('Site.logo');
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
        ->setSender($from)
        ->setFrom($from)
        ->setTo($to)
        ->setSubject('[' . $sitename . '] ' . $subject)
        ->setEmailFormat('html')
        ->setViewVars($viewVars)
        ->deliver();


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
    ]);
    $this->viewBuilder()->setOption('serialize', ['success', 'data']);
  }
}
