<?php

namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Firebase\JWT\JWT;
use Cake\Utility\Security;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Http\Session;
use Cake\Utility\Text;
use Cake\Log\Log;
use Cake\ORM\Locator\LocatorInterface;

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

        // we cant use PUT (alias edit method) for altering data
        $uid = $this->getRequest()->getData('user_id');

        $this->Crud->on('beforeSave', function (Event $event) use ($uid) {

            $entity = $event->getSubject()->entity;
            
            $entity['token'] = $this->createToken($uid);
            $this->Tokens->patchEntity($entity, [$entity]);

            // remove the former token (that with the same user_id) manually
            $oldEntities = $this->Tokens->find()
                ->where(['user_id' => $uid])
                ->toList();

            foreach ($oldEntities as $oldie) {
                // this triggers necessary events
                $this->Tokens->delete($oldie);
            }

        });

        // normally we would return the newly created token
        // but in this case we need the (updated) user sent back to the client
        $this->Crud->on('afterSave', function (Event $event) use ($uid) {

            $usersTable = TableRegistry::getTableLocator()->get('Users');
            $user = $usersTable->get($uid, [
                'contain' => ['Groups', 'Videos', 'Avatars', 'Tokens'],
            ]);
            
            $user['token_id'] = $event->getSubject()->entity->id;
            $usersTable->patchEntity($user, [$user]);
            $usersTable->save($user);

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

    protected function createToken($id) {
        return JWT::encode([
            'sub' => $id,
            'exp' => time() + Configure::read('Token.lifetime'),
        ],
        Security::getSalt());
    }

}
