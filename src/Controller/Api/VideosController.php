<?php

namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\App;
use Cake\Database\Expression\QueryExpression;
use Cake\Database\Query as DatabaseQuery;
use Cake\Event\Event;
use Cake\Log\Log;
use Cake\ORM\Query;
use Cake\I18n\Time;
use DateTime;

/**
 * Videos Controller
 *
 *
 * @method \App\Model\Entity\Video[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class VideosController extends AppController
{

    public function initialize()
    {
        parent::initialize();

        $this->Auth->allow([]);
        $this->loadComponent('File');
        $this->loadComponent('Director');
        $this->loadComponent('Upload');

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                // 'Crud.Index',
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
                'Crud.ApiPagination',
                'CrudJsonApi.JsonApi',
                'CrudJsonApi.Pagination', // Pagination != ApiPagination
                'Crud.ApiQueryLog',
            ],
        ]);

        $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');


    }

    public function index() {

        $authUser = $this->getAuthUser();
        
        $role = $this->getUserRoleName($authUser);

        switch($role) {

            case 'Administrator':
                $data = $this->Videos->find()
                    ->toArray();
                break;
            
            case 'Manager':
            case 'User':
            case 'Guest':
                $data = $this->Videos->find()
                    // see https://book.cakephp.org/3/en/orm/retrieving-data-and-resultsets.html#filtering-by-associated-data
                    // see https: //stackoverflow.com/questions/26799094/how-to-filter-by-conditions-for-associated-models
                    // see https: //stackoverflow.com/questions/10154717/php-cakephp-datetime-compare
                    ->matching('Users', function(Query $q) use($authUser) {
                        
                        $now = date('Y-m-d H:i:s');

                        $condition = [
                            'Users.id' => $authUser['id'],
                            'UsersVideos.start <=' => $now,
                            'UsersVideos.end >=' => $now,
                        ];
                        
                        return $q
                            ->where($condition);
                    })
                    ->toArray();
    
        }

        $this->set([
            'success' => true,
            'data' => $data,
            '_serialize' => ['success', 'data'],
        ]);

    }

    public function add()
    {
        
        if (!empty($files = $this->request->getData('Files'))) {
            
            // make shure single uploads are handled correctly
            if (!empty($files['tmp_name'])) {
                $files = [$files];
            }
            
            if (!empty($videos = $this->Upload->saveAs(VIDEOS, $files))) {

                $videos = $this->Videos->newEntities($videos);

                if ($data = $this->Videos->saveMany($videos)) {

                    $this->set([
                        'success' => true,
                        'data' => $data,
                        'message' => __('Video saved'),
                        '_serialize' => ['success', 'data', 'message'],
                    ]);
                } else {

                    $this->set([
                        'success' => false,
                        'data' => [],
                        'message' => __('Video could not be saved'),
                        '_serialize' => ['success', 'data', 'message'],
                    ]);
                }
            } else {

                $this->set([
                    'success' => false,
                    'data' => [],
                    'message' => __('An Error occurred while uploading your files'),
                    '_serialize' => ['success', 'data', 'message'],
                ]);
            }
        }
    }

    public function edit($id) {
        $this->Crud->on('afterSave', function (Event $event) {
            if($event->getSubject()->success) {
                $this->set([
                    'success' => true,
                    'message' => __('Video saved'),
                    '_serialize' => ['success', 'message'],
                ]);
            } else {
                $this->set([
                    'success' => false,
                    'message' => __('Video could not be saved'),
                    '_serialize' => ['success', 'data', 'message'],
                ]);

            }
        });

        return $this->Crud->execute();

    }

    public function delete()
    {
        $this->Crud->on('beforeDelete', function (Event $event) {

            if ($this->Auth->user()) {

                $id = $event->getSubject()->entity->id;
                $fn = $event->getSubject()->entity->src;

                $path = VIDEOS . DS . $id;
                $lg_path = $path . DS . 'lg';

                $oldies = glob($lg_path . DS . $fn);

                if (!empty($oldies) && $oldies && !unlink($oldies[0])) {
                    $event->stopPropagation();
                } else {
                    $this->File->rmdirr($path);
                }
            }
        });

        $this->Crud->on('afterDelete', function (Event $event) {

            if ($event->getSubject()->success) {
                $this->set([
                    'success' => true,
                    'message' => __('Video deleted'),
                    '_serialize' => ['success', 'message'],
                ]);
            } else {
                $this->set([
                    'success' => false,
                    'message' => __('Video could not be deleted'),
                    '_serialize' => ['success', 'message'],
                ]);

            }

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
                    '_serialize' => ['success', 'data'],
                ]
            );
        } else {
            $this->set(
                [
                    'success' => false,
                    'data' => $data,
                    '_serialize' => ['success', 'data'],
                ]
            );
            // die;
        }
        

    }

}
