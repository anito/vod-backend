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
use Cake\Http\Exception\UnauthorizedException;

class InboxesController extends AppController
{

  public function initialize(): void
  {
    parent::initialize();
    $this->Authentication->addUnauthenticatedActions([]);

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

    $authUser = $this->_getAuthUser();
    if (!$this->_isSuperuser($authUser)) {
      throw new UnauthorizedException(__('Unauthorized'));
    }

    $mails = $this->Inboxes->find('all');

    $this->set([
      'success' => true,
      'data' => $mails,
    ]);
    $this->viewBuilder()->setOption('serialize', ['success', 'data']);
  }

  public function get($id)
  {
    // protect Superusers Mail
    $user = $this->_getUser($id, ['contain' => 'Groups']);
    $authID = $this->_getAuthUser()->id;
    $userId = $user->id;
    $role = $user->role;
    if ($role === SUPERUSER && $authID !== $userId) {
      $mails = [];
    } else {
      $mails = $this->Inboxes->find('byIdOrEmail', ['field' => $id]);
    }

    $this->set([
      'success' => true,
      'data' => $mails,
    ]);
    $this->viewBuilder()->setOption('serialize', ['success', 'data']);
  }
}
