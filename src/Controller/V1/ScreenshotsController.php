<?php

namespace App\Controller\V1;

use App\Controller\V1\AppController;
use Cake\Core\App;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * Screenshots Controller
 *
 *
 * @method \App\Model\Entity\Video[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ScreenshotsController extends AppController
{

  public function initialize(): void
  {
    parent::initialize();
    $this->Authentication->addUnauthenticatedActions([]);

    $this->loadComponent('File');
    $this->loadComponent('Director');
    $this->loadComponent('Upload', ['type' => 'screenshots']);
    $this->loadComponent('Uri');

    $this->loadComponent('Crud.Crud', [
      'actions' => [
        'Crud.Index',
        'Crud.View',
        'Crud.Add',
        'Crud.Edit',
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

  public function add()
  {
    /**
     * we can not use PUT (alias edit method) for altering data
     * because $_FILES is only available in POST (alias add method),
     * so we have to first add the new and then remove the old entity
     */
    $files = $this->getRequest()->getData('Files');
    $uid = $this->getRequest()->getData('user_id');

    $this->Crud->on('beforeSave', function (Event $event) use ($files, $uid) {

      $entity = $event->getSubject()->entity;
      $newEntities = $this->addUpload($files);

      if (!empty($newEntities)) {

        // remove the former avatar (that with the same user_id) manually
        $oldEntities = $this->Screenshots->find()
          ->where(['user_id' => $uid])
          ->all()
          ->toList();

        foreach ($oldEntities as $oldie) {
          // this triggers necessary events
          $this->Screenshots->delete($oldie);
        }

        // overwrite request data with data returned from $this->addUpload
        $this->Screenshots->patchEntity($entity, $newEntities[0]);
      } else {
        $event->stopPropagation();
      }
    });

    $this->Crud->on('afterSave', function (Event $event) use ($uid) {

      $usersTable = TableRegistry::getTableLocator()->get('Users');
      $user = $usersTable->get($uid, contain: ['Screenshots', 'Tokens']);

      // normally we would send the new avatar
      // but in this case we need the updated user sent back to the client
      $this->set([
        'data' => $user,
        'message' => __('Screenshot saved'),
      ]);
      $this->Crud->action()->serialize(['data', 'message']);
    });

    return $this->Crud->execute();
  }

  public function delete($id)
  {
    $this->getRequest()->getSession()->destroy();
    $this->Crud->on('afterDelete', function (Event $event) {

      $uid = $event->getSubject()->entity["user_id"];
      $usersTable = TableRegistry::getTableLocator()->get('Users');
      $user = $usersTable->get($uid, contain: ['Screenshots']);

      $this->set([
        'data' => $user,
        'message' => __('Screenshot deleted'),
      ]);
      $this->Crud->action()->serialize(['data', 'message']);
    });
    return $this->Crud->execute();
  }

  protected function addUpload($files)
  {

    // make shure single uploads are handled correctly
    if (!is_array($files)) {
      $files = [$files];
    }

    if (!empty($screenshots = $this->Upload->save($files))) {
      return $screenshots;
    } else {
      return [];
    }
  }

  public function uri($id)
  {
    $data = $this->Uri->getUrl($id);

    if ($data) {
      $this->set(
        [
          'success' => true,
          'data' => $data,
        ]
      );
    } else {
      $this->set(
        [
          'success' => false,
          'data' => [],
        ]
      );
    }
    $this->viewBuilder()->setOption('serialize', ['success', 'data']);
  }
}
