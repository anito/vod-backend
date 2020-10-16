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

        $user = $this->Auth->user()['sub'];
        $role = $this->_getUserRoleName($user);

        switch($role) {

            case 'Administrator':
                $data = $this->Videos->find()
                    ->toArray();
                break;
            
            case 'Manager':
            case 'Guest':
            case 'User':
                $data = $this->Videos->find()
                    // see https://book.cakephp.org/3/en/orm/retrieving-data-and-resultsets.html#filtering-by-associated-data
                    // see https: //stackoverflow.com/questions/26799094/how-to-filter-by-conditions-for-associated-models
                    // see https: //stackoverflow.com/questions/10154717/php-cakephp-datetime-compare
                    ->matching('Users', function(Query $q) {
                        
                        $now = date('Y-m-d H:i:s');
                        Log::debug('Current time ' . $now);

                        $user = $this->Auth->user()['sub'];

                        $condition = [
                            'Users.id' => $user['id'],
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
        
        if (!empty($files = $this->request->getData('Video'))) {
            
            // make shure single uploads are handled correctly
            if (!empty($files['tmp_name'])) {
                $files = [$files];
            }
            
            if (!empty($videos = $this->Upload->saveUploadedFiles($files))) {

                $videos = $this->Videos->newEntities($videos);

                if ($data = $this->Videos->saveMany($videos)) {

                    $this->set([
                        'success' => true,
                        'data' => $data,
                        '_serialize' => ['success', 'data'],
                    ]);
                } else {

                    $this->set([
                        'success' => false,
                        'data' => [],
                        'message' => 'An error during save has occurred',
                        '_serialize' => ['success', 'data', 'message'],
                    ]);
                }
            } else {

                $this->set([
                    'success' => false,
                    'data' => [],
                    'message' => 'No videos for upload',
                    '_serialize' => ['success', 'data', 'message'],
                ]);
            }
        }
    }

    public function delete()
    {
        $this->Crud->on('beforeDelete', function (Event $event) {

            if ($this->Auth->identify()) {

                $id = $event->getSubject()->entity->id;
                $fn = $event->getSubject()->entity->src;

                define('PATH', $this->Director->getPathConstant($fn));
                if (!defined('PATH')) {
                    $event->stopPropagation();
                }

                $path = PATH . DS . $id;
                $lg_path = $path . DS . 'lg';

                $oldies = glob($lg_path . DS . $fn);

                if (!empty($oldies) && $oldies && !unlink($oldies[0])) {
                    $event->stopPropagation();
                } else {
                    $this->File->rmdirr($path);
                }
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
        $fn = basename($files[0]);
        
        if (!empty($files[0])) {
            $options = array_merge(compact(array('fn', 'id')), $params);
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
