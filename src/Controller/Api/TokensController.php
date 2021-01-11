<?php

namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\App;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Session;
use Cake\I18n\Time;
use Cake\Utility\Text;
use Cake\Log\Log;
use Cake\ORM\Locator\LocatorInterface;
use DateTime;

/**
 * Tokens Controller
 *
 *
 * @method \App\Model\Entity\Token[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TokensController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow([]);

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

        // we can not use PUT (alias edit method) for altering data
        $uid = $this->getRequest()->getData('user_id');

        $this->Crud->on('beforeSave', function (Event $event) use ($uid) {

            $entity = $event->getSubject()->entity;
            $timestamp = null;

            // get the latest video subscription to adjust the tokens expiration time
            $videosTable = TableRegistry::getTableLocator()->get('Videos');
            $latestVideo = $videosTable->find('latestVideo', ['uid' => $uid]);

            if(!empty($latestVideo)) {
                $end = $latestVideo->_matchingData['UsersVideos']->end;
                $end = $end->i18nFormat('yyyy-MM-dd HH:mm:ss');
                $timestamp = strtotime($end);
            }

            $jwt = $this->createToken($uid, $timestamp);

            $entity['token'] = $jwt;
            $this->Tokens->patchEntity($entity, [$entity]);

            // remove all former tokens belonging to that user, except the newly created one
            $oldEntities = $this->Tokens->find()
                ->where(['user_id' => $uid, 'token !=' => $jwt])
                ->toList();

            foreach ($oldEntities as $oldie) {
                // triggers necessary events
                $this->Tokens->delete($oldie);
            }
            

        });

        // normally we would return the newly created token
        // but in this case we want the (updated) user being sent back to the client
        $this->Crud->on('afterSave', function (Event $event) use ($uid) {

            $usersTable = TableRegistry::getTableLocator()->get('Users');

            $user = $usersTable->get($uid, [
                'contain' => ['Groups', 'Videos', 'Avatars', 'Tokens'],
            ]);

            $this->set([
                'success' => true,
                'data' => $user,
                'message' => __('Token created'),
                '_serialize' => ['success', 'message', 'data'],
            ]);
        });

        return $this->Crud->execute();

    }

    public function delete($id) {

        $this->Crud->on('afterDelete', function(Event $event) {
            
            // return the full stacked user
            $uid = $event->getSubject()->entity->user_id;

            $usersTable = TableRegistry::getTableLocator()->get('Users');
            $user = $usersTable->get($uid, [
                'contain' => ['Groups', 'Videos', 'Avatars', 'Tokens'],
            ]);

            $this->set([
                'success' => true,
                'data' => $user,
                'message' => __('Token removed'),
                '_serialize' => ['success', 'message', 'data'],
            ]);

        });

        return $this->Crud->execute();
    }

}
