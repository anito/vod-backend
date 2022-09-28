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

namespace App\Controller\V1;

use App\Controller\V1\AppController;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\Query;
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
    $this->Authentication->addUnauthenticatedActions(['add']);


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
        // 'Crud.ApiQueryLog'
      ],
    ]);

    $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');
  }

  public function index()
  {

    $mails = $this->Sents->find('all');

    $this->set([
      'success' => true,
      'data' => $mails,
    ]);
    $this->viewBuilder()->setOption('serialize', ['success', 'data']);
  }

  public function add()
  {

    $data = $this->request->getData();

    $this->Crud->on('beforeSave', function (Event $event) use ($data) {

      $type = 'html';
      $layout = 'physio-layout';
      $isPrivileged = false;
      // marshalled entity - modified by request data [user => [email, name]]
      $entity = $event->getSubject()->entity;

      $patched = [];
      $_to = [];
      $authUser = $this->_getAuthUser();
      if ($authUser) {
        $role = $authUser->get('role');
        $isPrivileged = in_array($role, [ADMIN, SUPERUSER]);
      }
      $sitename = Configure::check('Site.name') ? Configure::read('Site.name') : __('My website');
      $defaultSubject = __('General Information');

      /**
       * Distinguish between different types of users sending the mail:
       * users sending a token, considered to be of role admin or higher
       * users not sending a token are considered of role user
       */


      /**
       * Mainly used for homepage form
       */
      if (!$isPrivileged) {

        /**
         * Recipients (Admins)
         */
        foreach ($this->_getAdmins() as $admin) {
          $_to[$admin['email']] = $admin['name'];
        }

        /**
         * Check if the user exists in order to prevent auto-creation
         */
        $user = $this->Sents->Users->find()
          ->where(['Users.email' => $data['user']['email']])
          ->first();

        if (isset($user)) {
          /**
           * Don't (auto) create user from payload
           * if data structure has been received like
           * [user => [name, email]]
           */
          $entity->set('user', $user);

          $_from = [$user['email'] => $user['name']];
          $name = $user['name'];
          $subject = __('Message from {0}', $name);
        } else {
          $_from = [$data['user']['email'] => $data['user']['name']];
          $name = $data['user']['name'];
          $subject = __('Message from {0} (New user)', $name);

          /**
           * patch user
           * for auto-generation
           */
          $patched = array_merge($patched, [
            'user' => [
              'password' => randomString(),
              'group_id' => $this->_getRoleIdFromName(USER),
            ],
          ]);
        }
        $data['before-content'] = isset($data['subject']) ? $data['subject'] : '';
        $template = $data['template'] ?: 'from-user';
      } else {
        /**
         * Message sent from an authenticated User (Superuser or Admin)
         * Mainly used for internal communication within the apps MailManager component
         */

        /**
         * Recipient
         */
        $user = $this->Sents->Users->find()
          ->where(['Users.email' => $data['user']['email']])
          ->first();

        // $user = $entity->get('user');

        if (!isset($user)) {
          throw new NotFoundException();
        }

        /**
         * Don't (auto) create user from payload
         * if data structure has been received like
         * [user => [name, email]]
         */
        if ($entity->get('user')) {
          $entity->set('user', $user);
        };

        $_from = [$authUser['email'] => $authUser['name']];
        $_to[$user->email] = $user->name;
        $name = $user->name;
        $subject = isset($data['subject']) ? $data['subject'] : $defaultSubject;

        $patched = array_merge($patched, [
          'user_id' => $authUser['id'],
        ]);
      }

      if (!isset($_to, $_from)) {
        $event->stopPropagation();
        throw new ForbiddenException();
      }

      /**
       *  The template to be used
       */
      $templateData = isset($data['template']['data']) ? $data['template']['data'] : false;
      if (!isset($template)) {
        $template = isset($data['template']['slug']) ? $data['template']['slug'] : (isset($data['template']) ? $data['template'] : 'general');
      }
      $path = EMAIL_TEMPLATES . DS . $type . DS . $template . '.php';
      $templ = preg_replace('/-+/', '_', $template);
      if (!file_exists(EMAIL_TEMPLATES . DS . $type . DS . $templ . '.php')) {
        $template = $templateData ? 'magic-link' : 'general';
      };

      $mail = new Mailer();
      $mail->viewBuilder()->setTemplate($template);
      $mail->viewBuilder()->setLayout($layout);

      /**
       *  View Vars
       */
      $salutation = Configure::read('Site.salutation');
      $logo = Configure::read('Site.logo');
      $beforeContent = isset($data['before-content']) ? $data['before-content'] : '';
      $content = isset($data['content']) ? $data['content'] : __('No message');
      $afterContent = isset($data['after-content']) ? $data['after-content'] : '';
      $beforeSitename = isset($data['before-sitename']) ? $data['before-sitename'] : '';
      $afterSitename = isset($data['after-sitename']) ? $data['after-sitename'] : '';
      $beforeFooter = isset($data['before-footer']) ? $data['before-footer'] : '';
      $footer = isset($data['footer']) ? $data['footer'] : '';
      $afterFooter = isset($data['after-footer']) ? $data['after-footer'] : '';
      $prime = '#ad1457';

      $viewVars = compact([
        'logo',
        'salutation',
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
        'prime'
      ]);

      $message = $mail
        ->setSender($_from)
        ->setFrom($_from)
        ->setTo($_to)
        ->setSubject('[' . $sitename . '] ' . $subject)
        ->setEmailFormat($type)
        ->setViewVars($viewVars)
        ->deliver();


      /**
       * Mail should have header, subject and message properties
       * Add a subject property and
       * Save mail to database
       */
      $message['subject'] = $subject;

      $this->Sents->patchEntity($entity, array_merge($patched, [
        '_to' => implode(';', array_keys($_to)),
        '_from' => implode(';', array_keys($_from)),
        '_read' => 0,
        'message' => json_encode($message),
      ]));
    });

    $this->Crud->on('afterSave', function (Event $event) {

      $success = $event->getSubject()->success;
      if (!$success) {
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
          $inboxTable->save($newInbox);
        }
      }
    });

    return $this->Crud->execute();
  }

  public function get($id)
  {
    $user = $this->_getUser($id, ['contain' => 'Groups']);
    $authID = $this->_getAuthUser('id');
    $userId = $user->id;
    $role = $user->role;
    if ($role === SUPERUSER && $authID !== $userId) {
      // hide all Superusers Mail
      $mails = [];
    } else {
      // exclude mails sent to superusers
      $usersTable = TableRegistry::getTableLocator()->get('Users');
      $superusers = $usersTable->find('superusersEmail')->toArray();
      $emailList = [];
      foreach ($superusers as $key => $user) {
        $emailList[] = $user->email;
      }
      $mails = $this->Sents->find('byIdOrEmail', ['field' => $id])
        ->where(function (QueryExpression $exp, Query $q) use ($emailList) {
          return $exp->notIn('_to', $emailList);
        });
    }

    $this->set([
      'success' => true,
      'data' => $mails,
    ]);
    $this->viewBuilder()->setOption('serialize', ['success', 'data']);
  }
}
