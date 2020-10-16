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
                'index' => [
                    'className' => 'Crud.Index',
                    'relatedModels' => true,
                ],
                'view' => [
                    'className' => 'Crud.View',
                    'relatedModels' => true // not supported
                ],
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

    public function edit($id) {
        // $request = $this->request->getData();
        // Log::debug($request);
        
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
            'exp' =>  time() + 604800 // 1 week => 7 days = 7*60*60*24 = 604800 || 1 day = 60*60*24 =  86400
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

}