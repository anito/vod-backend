<?php
namespace App\Controller\Api;

use Cake\Event\Event;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Utility\Security;
use Firebase\JWT\JWT;
use Cake\Log\Log;

class UsersController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->Auth->allow( ['token', 'logout', 'login'] );

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

    public function add() {
        $this->Crud->on('afterSave', function(Event $event) {
            if ($event->subject->created) {
                $this->set('data', [
                    'id' => $event->subject->entity->id,
                    'token' => JWT::encode([
                        'sub' => $event->subject->entity->id,
                        'exp' =>  time() + 604800 // (sec) 604800/60/60/24 = 7 days
                    ],
                    Security::getSalt())
                ]);
                $this->Crud->action()->config('serialize.data', 'data');
            }
        });
        return $this->Crud->execute();
    }

    public function token() {
        $this->login();
    }

    public function login() {
        $user = $this->Auth->identify();
        if (!$user) {
            throw new UnauthorizedException(__('Invalid username or password'));
        }
        $user[ 'token' ] = JWT::encode([
            'sub' => $user,
            'exp' =>  time() + 604800 // 1 week [ 604800/60/60/24 = 7 days || 86400/60/60/24 = 1 day ]
        ],
        Security::getSalt());

        $this->set([
            'success' => true,
            'data' => [
                'message' => __('Login successful'),
                'user' => $user,
                'role' => $this->_getUserRoleName($user),
            ],
            '_serialize' => ['success', 'data']
        ]);
    }

    public function logout() {
        $this->Auth->logout();
        $this->set([
            'success' => true,
            'data' => [
                'message' => ' You\'re logged out'
            ],
            '_serialize' => ['success', 'data']
        ]);
    }

    protected function _getUserRoleName($user) {
        return $this->Users->Groups->find()
            ->where(['id' => $user['group_id']])
            ->select(['name'])
            ->first()
            ->name;
    }

    protected function _getGroups() {
        $groups = [];
        $query = $this->Users->Groups->find('all');
        $_groups = $query->toArray();
        
        foreach( $_groups as $group ) {
            $groups[] = array( 'name' => $group->name, 'id' => $group->id );
        }
        return $groups;
    }

}