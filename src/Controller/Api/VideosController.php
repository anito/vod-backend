<?php

namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\App;
use Cake\Event\Event;
use Cake\Log\Log;


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

    public function index_() {

        $this->Crud->action()->findMethod('widthImages');
        $this->Crud->execute();

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
            $data = array($id => $this->Director->p($options));
            
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
