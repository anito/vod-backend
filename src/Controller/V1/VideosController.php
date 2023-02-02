<?php

namespace App\Controller\V1;

use App\Controller\V1\AppController;
use Cake\Core\App;
use Cake\Event\Event;
use Cake\Event\EventInterface;
use Cake\ORM\Locator\TableLocator;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
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

    $this->Authentication->addUnauthenticatedActions([]);

    $user = $this->_getAuthUser();
    $isRelatedModelActive = (isset($user) && $this->_isPrivileged($user)) ? true : false;

    $this->loadComponent('File');
    $this->loadComponent('Director');
    $this->loadComponent('Upload');

    $this->loadComponent('Crud.Crud', [
      'actions' => [
        // 'Crud.Index',
        'index' => [
          'className' => 'Crud.Index',
          'relatedModels' => $isRelatedModelActive, // -> for index action anly
        ],
        'Crud.View',
        'Crud.Add',
        'Crud.Edit',
        'Crud.Delete',
      ],
      'listeners' => [
        'Crud.Api',
        // 'Crud.ApiPagination' // set in beforeFilter
      ],
    ]);

    $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');
  }

  public function beforeFilter(EventInterface $event)
  {
    $this->searchParams = $this->request->getQuery();
    $this->isSearch = isset($this->searchParams['keys']);
    if (!$this->isSearch) {
      $this->Crud->addListener('Crud.ApiPagination');
    }
  }

  public function index($type = null)
  {

    $user = $this->_getAuthUser();
    $role = $user->role;
    $condition = [];

    if (isset($this->searchParams['keys']) && isset($this->searchParams['search'])) {
      $keys = $this->searchParams['keys'];
      $keys = explode(",", $keys);
      $keys = preg_replace('/\s+/', '', $keys);
      $search = $this->searchParams['search'];
      $table = TableRegistry::getTableLocator()->get('Videos');
      foreach ($keys as $key) {
        if ($table->hasField($key)) {
          $condition['Videos.' . $key . ' LIKE'] = '%' . $search . '%';
        }
      }
      $condition = ['OR' => $condition];
    }

    $this->Crud->on('beforePaginate', function (Event $event) use ($user, $role, $type, $condition) {

      // limit defaults to 20
      // maxLimit defaults to 100
      // augmented by the sort, direction, limit, and page parameters when passed in from the URL
      $settings = [
        'limit' => 10,
      ];

      $query = $event->getSubject()->query;

      switch ($role) {

        case ADMIN:
        case SUPERUSER:
          $query
            ->where($condition);
          break;

        case MANAGER:
        case USER:
        case GUEST:
          if($type === 'all') {
            $query
              ->where($condition)
              ->select(['id', 'image_id', 'title', 'description']);
          } else {
            $query
              // see https://book.cakephp.org/4/en/orm/retrieving-data-and-resultsets.html#filtering-by-associated-data
              // see https: //stackoverflow.com/questions/26799094/how-to-filter-by-conditions-for-associated-models
              // see https: //stackoverflow.com/questions/10154717/php-cakephp-datetime-compare
              ->matching('Users', function (Query $q) use ($user, $condition) {
  
                $now = date('Y-m-d H:i:s');
  
                $condition = array_merge($condition, [
                  'Users.id' => $user['id'],
                  'UsersVideos.start <=' => $now,
                  'UsersVideos.end >=' => $now,
                ]);
  
                return $q
                  ->where($condition);
              });
          }
      }
      $query->order(['Videos.title' => 'ASC']);
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

  public function edit()
  {
    $this->Crud->on('afterSave', function (Event $event) {
      if ($event->getSubject()->success) {
        $this->set(['message' => __('Video saved'),
        ]);
      } else {
        $this->set(['message' => __('Video could not be saved'),
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
      $url = $this->Director->p($options);
      $json = json_encode($params);
      $stringified = preg_replace('/["\'\s]/', '', $json);

      $data = array(
        'id' => $id,
        'url' => $url,
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
