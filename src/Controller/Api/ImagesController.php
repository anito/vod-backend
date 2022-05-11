<?php

namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\App;
use Cake\Event\Event;

/**
 * Images Controller
 *
 *
 * @method \App\Model\Entity\Video[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ImagesController extends AppController
{
  public function initialize(): void
  {
    parent::initialize();
    $this->Authentication->addUnauthenticatedActions([]);

    $this->loadComponent('File');
    $this->loadComponent('Director');
    $this->loadComponent('Upload');

    $this->loadComponent('Crud.Crud', [
      'actions' => [
        'index' => [
          'className' => 'Crud.Index',
          'relatedModels' => true,
        ],
        'Crud.View',
        'Crud.Add',
        'Crud.Edit',
        'Crud.Delete',
      ],
      'listeners' => [
        'Crud.Api',
      ],
    ]);

    $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');
  }

  public function index()
  {
    $this->Crud->on('beforePaginate', function (Event $event) {

      $query = $event->getSubject()->query;

      $settings = [
        'limit' => 100,
      ];
      $data = $this->paginate($query, $settings);
      $this->set('data', $data);
    });

    $this->Crud->action()->serialize(['data']);
    return $this->Crud->execute();
  }

  public function add()
  {

    if (!empty($files = $this->request->getData('Files'))) {

      // make shure single uploads are handled correctly
      if (!is_array($files)) {
        $files = [$files];
      }

      if (!empty($images = $this->Upload->saveAs(IMAGES, $files))) {

        $images = $this->Images->newEntities($images);

        if ($data = $this->Images->saveMany($images)) {
          $this->set([
            'success' => true,
            'data' => $data,
            'message' => __('Image saved'),
          ]);
        } else {
          $this->set([
            'success' => false,
            'data' => [],
            'message' => __('An error occurred saving your image data'),
          ]);
        }
      } else {
        $this->set([
          'success' => false,
          'data' => [],
          'message' => __('An Error occurred while uploading your files'),
        ]);
      }
      $this->viewBuilder()->setOption('serialize', ['success', 'data', 'message']);
    }
  }

  public function delete()
  {
    $this->Crud->on('beforeDelete', function (Event $event) {

      $result = $this->Authorization->getResult();
      if ($result->isValid()) {

        $id = $event->getSubject()->entity->id;
        $fn = $event->getSubject()->entity->src;

        $path = IMAGES . DS . $id;
        $lg_path = $path . DS . 'lg';

        $oldies = glob($lg_path . DS . $fn);

        if (!empty($oldies) && $oldies && !unlink($oldies[0])) {
          $event->stopPropagation();

          $this->set([
            'message' => __('Image could not be deleted'),
          ]);
        } else {
          $this->File->rmdirr($path);
        }
        $this->Crud->action()->serialize(['message']);
      }
    });

    $this->Crud->on('afterDelete', function (Event $event) {

      if ($event->getSubject()->success) {
        $this->set([
          'message' => __('Image deleted'),
        ]);
      }
      $this->Crud->action()->serialize(['message']);
    });

    return $this->Crud->execute();
  }

  public function uri($id)
  {
    $data = [];

    $params = $this->getRequest()->getQuery();
    $lg_path = IMAGES . DS . $id . DS . 'lg';
    $files = glob($lg_path . DS . '*.*');

    if (!empty($files)) {
      $fn = basename($files[0]);
      $type = "images";

      $options = array_merge(compact(array('fn', 'id', 'type')), $params);
      $p = $this->Director->p($options);
      $json = json_encode($params);
      $stringified = preg_replace('/["\'\s]/', '', $json);
      $data = array(
        'id' => $id,
        'url' => $p,
        'params' => $stringified,
      );

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
          'data' => $data,
        ]
      );
      // die;
    }
    $this->viewBuilder()->setOption('serialize', ['success', 'data']);
  }
}
