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

        // we cant use PUT (alias edit method) for altering data
        // because $_FILES is only available in POST (alias add method)
        $files = $this->request->getData('Files');
        $uid = $this->request->getData('user_id');


        $this->Crud->on('beforeSave', function (Event $event) use ($files, $uid) {
            
            $entity = $event->getSubject()->entity;
            $newEntities = $this->addUpload($files);

            if(!empty($newEntities)) {

                // remove the former avatar (that with the same user_id) manually
                $oldEntities = $this->Avatars->find()
                    ->where(['user_id' => $uid])
                    ->toList();

                foreach($oldEntities as $oldie) {
                    $this->Avatars->delete($oldie);
                }

                // we depend on events (here beforeDelete) to get rid of old uploads
                // executing queries on query objects don't trigger events, so we don't can use this

                // $query = $this->Avatars->query();
                // $oldEntity = $query->delete()
                //     ->where(['user_id' => $uid])
                //     ->execute();

                // overwrite request data with updated data from the $this->addUpload method
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
    
            // normally we would send the added avatar
            // but here we send the (updated) user back to the client
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
        $this->Crud->on('beforeDelete', function (Event $event) {

            $this->deleteUpload($event);

        });
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
