<?php

namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\App;
use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\ORM\Query;

/**
 * Templates Controller
 *
 *
 * @method \App\Model\Entity\Template[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TemplatesController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow([]);

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'index' => [
                    'className' => 'Crud.Index',
                    'relatedModels' => true,
                ],
                'Crud.View',
                'Crud.Add',
                'edit' => [
                    'className' => 'Crud.edit',
                    'relatedModels' => true,
                ],
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
        $templates = $this->Templates->find()
            ->select(['id', 'slug', 'name', 'protected'])
            ->contain('Items', function (Query $query) {
                return $query->select(['id', 'content', 'template_id', 'field_id']);
            })
            ->contain('Items.Fields', function (Query $query) {
                return $query->select(['id', 'name']);
            });

        $this->set([
            'success' => true,
            'data' => $templates,
            '_serialize' => ['success', 'data'],
        ]);

        $this->Crud->on('beforePaginate', function (Event $event) {

        });

        return $this->Crud->execute();

    }

    public function edit($id)
    {

        $this->Crud->on('beforeSave', function (Event $event) {
            $data = $this->getRequest()->getData();
        });

        return $this->Crud->execute();

    }

    public function delete()
    {
        $this->Crud->on('beforeDelete', function (Event $event) {

            if ($this->Auth->identify()) {

                $entity = $event->getSubject()->entity;
            }
        });

        $this->Crud->on('afterDelete', function (Event $event) {

            if ($event->getSubject()->success) {
                $this->set([
                    'success' => true,
                    'message' => __('Template deleted'),
                    '_serialize' => ['success', 'message'],
                ]);
            } else {
                $this->set([
                    'success' => false,
                    'message' => __('Template could not be deleted'),
                    '_serialize' => ['success', 'message'],
                ]);

            }
        });

        return $this->Crud->execute();
    }

}
