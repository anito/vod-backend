<?php
namespace App\Controller\Api;

use Cake\Event\Event;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Utility\Security;
use Firebase\JWT\JWT;
use Cake\Log\Log;

class UsersController extends AppController
{
    // 1 week => 7 days = 7*24*60*60 = 604800
    // 1 day = 24*60*60 =  86400
    static $max_token_time = 60*60;

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

    public function index()
    {
        $this->Crud->on('afterPaginate', function(\Cake\Event\Event $event) {
            $notAllowed = FIXTURE;

            foreach ($event->getSubject()->entities as $entity) {
                $id = $entity->id;
                $index = array_search($id, array_column($notAllowed, 'id'));
                if (is_int($index)) {
                    $entity['protected'] = true;
                }
            }
        });

        return $this->Crud->execute();
    }

    public function add() {
        $this->Crud->on('afterSave', function(Event $event) {
            if ($event->getSubject()->entity->hasErrors()) {
                $errors = $event->getSubject()->entity->errors();
                $field = array_key_first($errors);
                $type = array_key_first($errors[$field]);

                $message = $errors[$field][$type];
                throw new ForbiddenException($message);
            }

            if ($event->getSubject()->created) {
                $id = $event->getSubject()->entity->id;
                $this->set('data', [
                    'id' => $id,
                    'message' => 'User created',
                    'token' => JWT::encode([
                        'sub' => $id,
                        'exp' =>  time() + $this::$max_token_time
                    ],
                    Security::getSalt())
                ]);
                $this->Crud->action()->config('serialize.data', 'data');
            }
        });
        return $this->Crud->execute();
    }

    public function edit($id) {
        $this->Crud->on('afterSave', function(Event $event) {
            if ($event->getSubject()->entity->hasErrors()) {
                $errors = $event->getSubject()->entity->getErrors();
                $field = array_key_first($errors);
                $type = array_key_first($errors[$field]);

                $message = $errors[$field][$type];
                throw new ForbiddenException($message);
            }
            if ($event->getSubject()->success) {
                $message = __('User updated');
            } else {
                $message = __('User could not be updated');
            }
            $this->set('data', [
                'message' => $message
            ]);

            $this->Crud->action()->config('serialize.data', 'data');
        });
        return $this->Crud->execute();
    }

    public function delete($id) {
        $this->Crud->on('beforeDelete', function(Event $event) {
            $notAllowed = FIXTURE;
            $id = $event->getSubject()->entity->id;
            $index = array_search($id, array_column($notAllowed, 'id'));
            if (is_int($index)) {
                $message = __('Protected users can not be deleted');
                $event->stopPropagation();
                throw new ForbiddenException($message);
            }
        });

        $this->Crud->on('afterDelete', function(Event $event) {
            if ($event->getSubject()->entity->hasErrors()) {
                $errors = $event->getSubject()->entity->getErrors();
                $field = array_key_first($errors);
                $type = array_key_first($errors[$field]);

                $message = $errors[$field][$type];
                throw new ForbiddenException($message);
            }
            $message = __('User deleted');
            $this->set('data', [
                'message' => $message
            ]);

            $this->Crud->action()->config('serialize.data', 'data');
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
            'exp' =>  time() + $this::$max_token_time
        ],
        Security::getSalt());

        $this->set([
            'success' => true,
            'data' => [
                'message' => __('Login successful'),
                'user' => $user,
                'role' => $this->_getUserRoleName($user),
                'groups' => $this->_getGroups(),
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