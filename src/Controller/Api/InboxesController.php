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
use Cake\Log\Log;
use Cake\Mailer\Email;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

class InboxesController extends AppController {

    function initialize() {
        parent::initialize();

        $this->Auth->allow([]);

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

    public function index() {

        $mails = $this->Inboxes->find('all');

        $this->set([
            'success' => true,
            'data' => [
                'mails' => $mails
            ],
            '_serialize' => ['success', 'data']
        ]);
    }

    public function get($id) {

        $received = $this->Inboxes->find('byIdOrEmail', ['field' => $id]);

        $this->set([
            'success' => true,
            'data' => $received,
            '_serialize' => ['success', 'data'],
        ]);

    }
}
