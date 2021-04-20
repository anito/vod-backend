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
use Cake\Event\Event;

class EmailTemplatesController extends AppController
{

    public function initialize()
    {
        parent::initialize();

        $this->Auth->allow([]);

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'index' => [
                    'className' => 'Crud.Index',
                    'relatedModels' => true, // available only for index
                ],
                // 'Crud.Index',
                'Crud.View',
                'Crud.Edit',
                'Crud.Add',
                'Crud.Delete',
            ],
            'listeners' => [
                'Crud.Api',
            ],
        ]);

        $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');

    }

    public function add()
    {
        $this->Crud->on('beforeSave', function (Event $event) {
            $entity = $event->getSubject()->entity;

            $general_id = $this->EmailTemplates->Templates->find()
                ->where(['Templates.slug' => 'general'])
                ->first()->id;

            if (!isset($general_id)) {
                $event->stopPropagation();
            }
            $entity->template_id = $general_id;
        });

        return $this->Crud->execute();
    }
}
