<?php

namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\App;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Utility\Text;
use Cake\Log\Log;
use Cake\ORM\Locator\LocatorInterface;

/**
 * Avatars Controller
 *
 *
 * @method \App\Model\Entity\Video[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AvatarsController extends AppController
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
                'Crud.Index',
                // 'index' => [
                //     'className' => 'Crud.Index',
                //     'relatedModels' => true,
                // ],
                'Crud.View',
                'Crud.Add',
                'Crud.Edit',
                'Crud.Delete'
            ],
            'listeners' => [
                'Crud.Api',
                'Crud.ApiPagination',
                'CrudJsonApi.JsonApi',
                'CrudJsonApi.Pagination', // Pagination != ApiPagination
                'Crud.ApiQueryLog'
            ]
        ]);

        $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');


    }

    public function add()
    {

        $files = $this->request->getData('Files');
        $uid = $this->request->getData('user_id');


        $this->Crud->on('beforeSave', function (Event $event) use ($files, $uid) {
            
            $entity = $event->getSubject()->entity;
            $newEntities = $this->addUpload($files);

            if(!empty($newEntities)) {

                // delete existing entries since we cant use PUT for altering data ($_FILES is only available in POST method (not PUT))
                $query = $this->Avatars->query();
                $query->delete()
                    ->where(['user_id' => $uid])
                    ->execute();

                $this->Avatars->patchEntity($entity, $newEntities[0]);
            } else {
                $this->set([
                    'success' => false,
                    'data' => [],
                    'message' => 'An Error occurred while uploading your files',
                    '_serialize' => ['success', 'data', 'message'],
                ]);
            }

        });

        $this->Crud->on('afterSave', function (Event $event) use ($uid) {

            $usersTable = TableRegistry::getTableLocator()->get('Users');
            $user = $usersTable->get($uid, [
                'contain' => ['Groups', 'Videos', 'Avatars'],
            ]);
    
            $this->set([
                'success' => true,
                'data' => $user,
                '_serialize' => ['success', 'data'],
            ]);
        });

        return $this->Crud->execute();

    }
    
    public function delete()
    {
        $this->Crud->on('beforeDelete', function (Event $event) {

            $this->deleteUpload($event);

        });
        return $this->Crud->execute();
    }

    protected function addUpload($files) {
       
        // make shure single uploads are handled correctly
        if (!empty($files['tmp_name'])) {
            $files = [$files];
        }

        if (!empty($avatars = $this->Upload->saveAs(AVATARS, $files))) {
            return $avatars;
        } else {
            return [];
        }
    }

    protected function deleteUpload($event) {

        $id = $event->getSubject()->entity->id;
        $fn = $event->getSubject()->entity->src;

        $path = AVATARS . DS . $id;
        $lg_path = $path . DS . 'lg';

        $oldies = glob($lg_path . DS . $fn);

        if (!empty($oldies) && $oldies && !unlink($oldies[0])) {
            $event->stopPropagation();
        } else {
            $this->File->rmdirr($path);
        }

    }

    public function uri($id)
    {
        $data = [];
        
        $params = $this->getRequest()->getQuery();
        $lg_path = AVATARS . DS . $id . DS . 'lg';
        $files = glob($lg_path . DS . '*.*');
        $fn = basename($files[0]);
        $type = "avatars";
        
        if (!empty($files[0])) {
            $options = array_merge(compact(array('fn', 'id', 'type')), $params);
            // Log::debug($options);
            $p = $this->Director->p($options);
            $json = json_encode($params);
            $stringified = preg_replace('/["\'\s]/', '', $json);
            $data = array(
                'id' => $id,
                'url' => $p,
                'params' => $stringified
                // preg_replace('/[\"\'\s]/i', '', json_encode($params)) => $this->Director->p($options)
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
        }
    }

}
