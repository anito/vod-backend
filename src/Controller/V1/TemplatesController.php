<?php

namespace App\Controller\V1;

use Cake\Core\App;
use Cake\Event\Event;

/**
 * Templates Controller
 *
 *
 * @method \App\Model\Entity\Template[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TemplatesController extends AppController
{

  public function initialize(): void
  {
    parent::initialize();
    $this->Authentication->allowUnauthenticated([]);

    $this->loadComponent('Crud.Crud', [
      'actions' => [
        'index' => [
          'className' => 'Crud.Index',
          'relatedModels' => true,
        ],
        'Crud.View',
        'Crud.Add',
        'edit' => [
          'className' => 'Crud.Edit',
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
    $this->Crud->on('beforePaginate', function (Event $event) {
      $query = $event->getSubject()->query;

      $query
        ->select(['id', 'slug', 'name', 'protected'])
        ->contain('Items', function ($q) {
          return $q->select(['id', 'content', 'template_id', 'field_id']);
        })
        ->contain('Items.Fields', function ($q) {
          return $q->select(['id', 'name']);
        });
    });

    return $this->Crud->execute();
  }

  public function edit($id)
  {

    $this->Crud->on('afterSave', function (Event $event) {
      $entity = $event->getSubject()->entity;

      if ($event->getSubject()->success) {
        $data = [
          'id' => $entity->id,
          'items' => $entity->items,
        ];
        $message = __('Template saved');
      } else {
        $data = [];
        $message = __('Template could not be saved');
      }

      $this->set(compact('data', 'message'));

      $this->Crud->action()->serialize(['data', 'message']);
    });

    return $this->Crud->execute();
  }

  public function add()
  {

    $this->Crud->on('afterSave', function (Event $event) {
      $entity = $event->getSubject()->entity;

      if ($event->getSubject()->success) {
        $data = [
          'id' => $entity->id,
          'items' => $entity->items,
        ];
        $message = __('Template created');
      } else {
        $data = [];
        $message = __('Template could not be created');
      }

      $this->set(compact('data', 'message'));

      $this->Crud->action()->serialize(['data', 'message']);
    });

    return $this->Crud->execute();
  }

  public function delete()
  {
    $this->Crud->on('beforeDelete', function (Event $event) {
      $result = $this->Authentication->getResult();
      if ($result->isValid()) {
        $entity = $event->getSubject()->entity;
      }
    });

    $this->Crud->on('afterDelete', function (Event $event) {

      if ($event->getSubject()->success) {
        $this->set([
          'message' => __('Template deleted'),
        ]);
      } else {
        $this->set([
          'message' => __('Template could not be deleted'),
        ]);
      }

      $this->Crud->action()->serialize(['message']);
    });

    return $this->Crud->execute();
  }
}
