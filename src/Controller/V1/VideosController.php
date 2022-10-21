<?php

namespace App\Controller\V1;

use App\Controller\V1\AppController;
use Cake\Core\App;
use Cake\Event\Event;
use Cake\ORM\Query;
use Exception;

/**
 * Videos Controller
 *
 *
 * @method \App\Model\Entity\Video[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class VideosController extends AppController
{

  public function initialize(): void
  {
    parent::initialize();

    $this->Authentication->addUnauthenticatedActions(['all']);

    $this->loadComponent('File');
    $this->loadComponent('Director');
    $this->loadComponent('Upload');

    $this->loadComponent('Crud.Crud', [
      'actions' => [
        // 'Crud.Index',
        'index' => [
          'className' => 'Crud.Index',
          'relatedModels' => true, // only for index
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
    $this->loadComponent('Paginator');

    $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');
  }

  public function all()
  {
    $data = $this->Videos->find()
      ->select(['id', 'image_id', 'title', 'description'])
      ->toArray();

    $this->set([
      'success' => true,
      'data' => $data,
    ]);
    $this->viewBuilder()->setOption('serialize', ['success', 'data']);
  }

  public function index()
  {

    $user = $this->_getAuthUser();
    $role = $user->role;

    $this->Crud->on('beforePaginate', function (Event $event) use ($user, $role) {

      // limit defaults to 20
      // maxLimit defaults to 100
      // augmented by the sort, direction, limit, and page parameters when passed in from the URL
      $settings = [
        'limit' => 100,
      ];
      $query = $event->getSubject()->query;

      switch ($role) {

        case ADMIN:
        case SUPERUSER:
          $query
            ->order(['Videos.title' => 'ASC']);
          break;

        case MANAGER:
        case USER:
        case GUEST:
          $query
            // see https://book.cakephp.org/3/en/orm/retrieving-data-and-resultsets.html#filtering-by-associated-data
            // see https: //stackoverflow.com/questions/26799094/how-to-filter-by-conditions-for-associated-models
            // see https: //stackoverflow.com/questions/10154717/php-cakephp-datetime-compare
            ->matching('Users', function (Query $q) use ($user) {

              $now = date('Y-m-d H:i:s');

              $condition = [
                'Users.id' => $user['id'],
                'UsersVideos.start <=' => $now,
                'UsersVideos.end >=' => $now,
              ];

              return $q
                ->where($condition)
                ->order(['Videos.title' => 'ASC']);
            });
      }
      $videos = $this->paginate($query, $settings);

      $this->set([
        'data' => $videos,
      ]);
    });
    $this->Crud->action()->serialize(['data']);
    return $this->Crud->execute();
  }

  public function add()
  {
    if ($this->request->is('post') && empty($_POST)) {
      throw new Exception(__('Videos exceeding maximum post size {max}', ['max' => ini_get('post_max_size')]));
    }

    $files = $this->request->getData('Files');
    if (!empty($files)) {

      // make sure single uploads are handled correctly
      if (!is_array($files)) {
        $files = [$files];
      }

      if (!empty($videos = $this->Upload->saveAs(VIDEOS, $files))) {

        $videos = $this->Videos->newEntities($videos);

        // Log::debug('{videos}', ['videos' => $videos]);

        if ($data = $this->Videos->saveMany($videos)) {

          $this->set([
            'success' => true,
            'data' => $data,
            'message' => __('Video saved'),
          ]);
        } else {

          $this->set([
            'success' => false,
            'data' => [],
            'message' => __('Video could not be saved'),
          ]);
        }
      } else {

        $this->set([
          'success' => false,
          'data' => [],
          'message' => __('An Error occurred while uploading your files'),
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

  public function edit($id)
  {
    $this->Crud->on('afterSave', function (Event $event) {
      if ($event->getSubject()->success) {
        $this->set([
          'message' => __('Video saved'),
        ]);
      } else {
        $this->set([
          'message' => __('Video could not be saved'),
        ]);
      }
      $this->Crud->action()->serialize(['message']);
    });

    return $this->Crud->execute();
  }

  public function delete()
  {
    $this->Crud->on('beforeDelete', function (Event $event) {

      $result =  $this->Authentication->getResult();
      if ($result->isValid()) {

        $id = $event->getSubject()->entity->id;
        $fn = $event->getSubject()->entity->src;

        $path = VIDEOS . DS . $id;
        $lg_path = $path . DS . 'lg';

        $oldies = glob($lg_path . DS . $fn);

        if (!empty($oldies) && $oldies && !unlink($oldies[0])) {
          $event->stopPropagation();

          $this->set([
            'message' => __('Video could not be deleted'),
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
          'message' => __('Video deleted'),
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
    $lg_path = VIDEOS . DS . $id . DS . 'lg';
    $files = glob($lg_path . DS . '*.*');
    if (!empty($files)) {
      $fn = basename($files[0]);
      $type = "videos";

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