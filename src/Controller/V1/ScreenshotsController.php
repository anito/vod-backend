<?php

namespace App\Controller\V1;

use Cake\Core\App;
use Cake\Event\Event;
use Cake\Http\Exception\ForbiddenException;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Cake\View\JsonView;
use Crud\Error\Exception\CrudException;
use Exception;
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
        // 'Crud.ApiPagination',
        // 'Crud.ApiQueryLog'
      ],
    ]);

    $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');
  }

  public function add()
  {
    $this->Crud->on('beforeSave', function (Event $event) {

      $path  = $this->Screenshot->snap();

      if ($path instanceof Exception) {
        $event->stopPropagation();
        $message = $path->getMessage();
        $code = $path->getCode();
        throw new Exception($message, $code);
      }

      if (!@filesize($path)) {
        $event->stopPropagation();
        throw new Exception('The snapshot seems to be empty', 402);
      }

      $entity = $this->Screenshots->newEmptyEntity();

      try {
        // Upload to cloud and receive download link
        $link = $this->Screenshot->saveToSeafile($path);

        $entity = $this->Screenshots->patchEntity($entity, [
          'src' => basename($path),
          'filesize' => filesize($path),
          'link' => $link
        ]);

        $success = true;
        $message = __('Screenshot saved');
      } catch (Exception $e) {
        $success = false;
        $message = $e->getMessage();
      }

      unlink($path);

      $event->getSubject()->entity = $entity;

      $this->set([
        'success' => $success,
        'data' => $entity,
        'message' => $message
      ]);

      $this->Crud->action()->serialize(['success', 'data', 'message']);
    });

    $this->Crud->on('afterSave', function (Event $event) {
      $entity = $event->getSubject()->entity;
      $this->Screenshots->delete($entity);
    });

    return $this->Crud->execute();
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
}
