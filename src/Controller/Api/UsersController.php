<?php
namespace App\Controller\Api;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Utility\Security;
use Firebase\JWT\JWT;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

class UsersController extends AppController
{
    
    public function initialize() {
        parent::initialize();
        $this->Auth->allow( ['add', 'token', 'logout', 'login'] );

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
        $authUser = $this->getUser($this->Auth->user());

        $this->Crud->on('beforePaginate', function(\Cake\Event\Event $event) use($authUser) {

            $query = $event->getSubject()->query;
            if (!$this->isAdmin($authUser)) {
                $query->where(['Users.id' => $authUser['id']]);
                $this->paginate($query);
            } else {
                $query->order(['Users.name' => 'ASC']);
            }
            
        });

        $this->Crud->on('afterPaginate', function(\Cake\Event\Event $event) use($authUser) {

            $notAllowed = FIXTURE;

            foreach ($event->getSubject()->entities as $entity) {
                $id = $entity->id;
                $index = array_search($id, $notAllowed);
                if (is_int($index)) {
                    $entity['protected'] = true;
                }
            }
        });

        return $this->Crud->execute();
    }

    public function view($id)
    {
        $authUser = $this->getUser($this->Auth->user());
        $user = $this->getUser($id);

        $this->Crud->on('beforeFind', function(\Cake\Event\Event $event) use($authUser, $user, $id) {
            if ($authUser['id'] != $id && !$this->isAdmin($authUser)) {
                $event->stopPropagation();
                throw new NotFoundException();
            }
            $this->set('data', $user);

        });

        $this->Crud->action()->config('serialize.data', 'data');

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
                    'message' => __('User created'),
                    'token' => JWT::encode([
                        'sub' => $id,
                        'exp' =>  time() + Configure::read('Token.lifetime')
                    ],
                    Security::getSalt())
                ]);
                $this->Crud->action()->config('serialize.data', 'data');
            }
        });
        return $this->Crud->execute();
    }

    public function edit($id) {

        $this->Crud->on('afterSave', function(Event $event) use($id) {
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

            $user = $this->getUser($id);

            $this->set([
                'data' => $user,
                'message' => $message,
                '_serialize' => ['success', 'data', 'message'],
            ]);

            $this->Crud->action()->config('serialize.data', 'data');
        });
        return $this->Crud->execute();
    }

    public function delete($id) {
        $this->Crud->on('beforeDelete', function(Event $event) {
            $notAllowed = FIXTURE;
            $id = $event->getSubject()->entity->id;
            $index = array_search($id, $notAllowed);
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
        $user = $this->Auth->identify();
        if (!$user) {
            throw new UnauthorizedException(__('Invalid username or password'));
        }

        $request = $this->getRequest()->getData();

        $token = JWT::encode([
            'sub' => $request['id'],
            'exp' =>  time() + 604800
        ],
        Security::getSalt());

        $tokenTable = TableRegistry::getTableLocator()->get('Tokens');
        
        $this->set([
            'success' => true,
            'data' => [
                'token' => $token
            ],
            '_serialize' => ['success', 'data']
        ]);
    }

    public function login() {
        
        $user = $this->Auth->identify();
        if (!$user) {
            throw new UnauthorizedException(__('Invalid username or password'));
        }

        $this->Auth->setUser($user);
       
        if(isset($user['id'])) {
            // if query database
            $id = $user['id'];
        } else {
            // using raw auth information (no database query)
            $id = $user['sub'];
        }

        // hydrate the user with associated data
        $user = $this->getUser($id);
        $token = JWT::encode([
            'sub' => $id,
            'exp' => time() + Configure::read('Token.lifetime'),
        ],
        Security::getSalt());

        $user[ 'token' ] = $token;
        $this->set([
            'success' => true,
            'message' => __('Login successful'),
            'data' => [
                'user' => $user,
                'groups' => $this->getGroups(),
            ],
            '_serialize' => ['success', 'data', 'message']
        ]);
    }

    public function logout() {
        $this->Auth->logout();
        $this->set([
            'success' => true,
            'message' => __('You\'re logged out'),
            '_serialize' => ['success', 'message']
        ]);
    }

}