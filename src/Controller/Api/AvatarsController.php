<?php

namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\App;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

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
                // 'Crud.ApiQueryLog'
            ]
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

            if(!empty($newEntities)) {

                // remove the former avatar (that with the same user_id) manually
                $oldEntities = $this->Avatars->find()
                    ->where(['user_id' => $uid])
                    ->toList();

                foreach($oldEntities as $oldie) {
                    // this triggers necessary events
                    $this->Avatars->delete($oldie);
                }

                // overwrite request data with data returned from $this->addUpload
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
                'contain' => ['Groups', 'Videos', 'Avatars', 'Tokens'],
            ]);
    
            // normally we would send the new avatar
            // but in this case we need the (updated) user sent back to the client
            $this->set([
                'success' => true,
                'data' => $user,
                '_serialize' => ['success', 'data'],
            ]);
        });

        return $this->Crud->execute();

    }

    public function delete($id)
    {
        $this->getRequest()->getSession()->destroy();
        $this->Crud->on('afterDelete', function (Event $event) {

            $uid = $event->getSubject()->entity["user_id"];
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

    public function uri($id)
    {
        $data = [];
        
        $params = $this->getRequest()->getQuery();
        $lg_path = AVATARS . DS . $id . DS . 'lg';
        $files = glob($lg_path . DS . '*.*');
        if (!empty($files)) {
            $fn = basename($files[0]);
            $type = "avatars";
        
            $options = array_merge(compact(array('fn', 'id', 'type')), $params);
            $p = $this->Director->p($options);
            $json = json_encode($params);
            $stringified = preg_replace('/["\'\s]/', '', $json);
            $data = array(
                'id' => $id,
                'url' => $p,
                'params' => $stringified
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
