<?php

namespace App\Controller\V1;

use App\Controller\V1\AppController;
use Cake\Core\App;
use Cake\Event\Event;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Cake\View\JsonView;
use Laminas\Diactoros\UploadedFile;

class NonExistentFileException extends \RuntimeException {}

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
    $this->Authentication->allowUnauthenticated(['add']);

    $this->loadComponent('File');
    $this->loadComponent('Director');
    $this->loadComponent('Upload', ['type' => 'screenshots']);
    $this->loadComponent('Uri');
    $this->loadComponent('Screenshot');

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

    $this->Crud->on('beforeSave', function (Event $event) {

      // Create a snapshot entity from scratch using `url` query param to emulate an upload
      $arr  = $this->Screenshot->snap();

      if (@filesize($arr['path'])) {

        $path = $arr['path'];
        $fn   = $arr['fn'];

        // Emulate an upload using UploadedFileInterface
        $file = new UploadedFile(
          $path,
          filesize($path),
          \UPLOAD_ERR_OK,
          $fn,
          'image/png'
        );

        $this->request = $this->request->withData('Files', [$file]);

        $files = $this->request->getData('Files');
        if (!empty($images = $this->Upload->save($files))) {

          $screenshot = $this->Screenshots->newEntity($images[0]);

          // Save file to seafile cloud (https://cloud.doojoo.de)
          $link = $this->Screenshot->saveToSeafile($files[0], $screenshot->id);

          // $folder = $screenshot->id;
          // $filename = $screenshot->src;
          // $link = $this->Screenshot->saveToCloud($folder, $filename);

          // Mutate entity
          $screenshot->link = $link;

          $event->getSubject()->entity = $screenshot;

          if ($data = $this->Screenshots->save($screenshot)) {

            $this->set([
              'data' => $data,
            ]);
          } else {
            $this->set([
              'success' => false,
              'data' => null,
              'message' => __('An error occurred saving your screenshot data'),
            ]);
          }
        } else {
          $this->set([
            'success' => false,
            'data' => null,
            'message' => __('An Error occurred while uploading your files'),
          ]);
        }
      } else {

        $event->stopPropagation();

        $this->set([
          'success' => false,
        ]);
      }

      $this->Crud->action()->serialize(['success', 'data', 'message']);
    });

    $this->Crud->execute();
  }

  public function delete()
  {
    $this->Crud->on('beforeDelete', function (Event $event) {

      $result = $this->Authentication->getResult();
      if ($result->isValid()) {

        $id = $event->getSubject()->entity->id;
        $fn = $event->getSubject()->entity->src;

        $path = SCREENSHOTS . DS . $id;
        $lg_path = $path . DS . 'lg';

        $oldies = glob($lg_path . DS . $fn);

        if (!empty($oldies) && $oldies && !unlink($oldies[0])) {
          $event->stopPropagation();

          $this->set([
            'message' => __('Screenshot could not be deleted'),
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
          'message' => __('Screenshot deleted'),
        ]);
      }
      $this->Crud->action()->serialize(['message']);
    });

    return $this->Crud->execute();
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
