<?php
namespace App\Controller\Api;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\I18n\Date;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

class UsersController extends AppController
{
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['token', 'logout', 'login']);

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'index' => [
                    'className' => 'Crud.Index',
                    'relatedModels' => true, // available only for index
                ],
                'Crud.View',
                'Crud.Add',
                'Crud.Edit',
                'Crud.Delete',
            ],
            'listeners' => [
                'Crud.Api',
                'Crud.ApiPagination',
            ],
        ]);

        $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');
    }

    public function index()
    {
        $authUser = $this->_getAuthUser();

        $this->Crud->on('beforePaginate', function (Event $event) use ($authUser) {

            $query = $event->getSubject()->query;
            if (!$this->_isAdmin($authUser)) {
                $query
                // limit user query to only contain itself
                ->where(['Users.id' => $authUser["id"]]);
                $this->paginate($query);
            } else {
                $query
                // we must force "hasMany" relations manually, although Crud.relatedModels Listener is set
                // belongsTo, hasOne and belongsToMany work just fine
                // this seems to be a bug in crud plugin
                ->order(['Users.name' => 'ASC']);
                $this->paginate($query);
            }
        });

        return $this->Crud->execute();
    }

    public function view()
    {
        $this->Crud->on('beforeFind', function (Event $event) {

            $authUser = $this->_getAuthUser();
            if (!$this->_isAdmin($authUser)) {
                $event->stopPropagation();
                throw new UnauthorizedException();
            }

            $user = $event->getSubject()->query
                ->contain(
                    'Mails', function (Query $q) {
                        return $q
                            ->select([
                                'Mails.user_id',
                                'total' => $q->func()->count('*'),
                            ]);
                    })
                ->contain(
                    'Inboxes', function (Query $q) {
                        return $q
                            ->select([
                                'Inboxes.user_id',
                                'total' => $q->func()->count('*'),
                                'read' => $q->func()->sum('Inboxes._read'),
                            ]);
                    })
                ->contain(['Groups', 'Avatars', 'Videos', 'Tokens'])
                ->first();

            $this->set('data', $user);

        });

        // $this->Crud->action()->config('serialize.data', 'data');

        return $this->Crud->execute();

    }

    public function add()
    {
        $this->Crud->on('afterSave', function (Event $event) {
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
                        'exp' => time() + Configure::read('Token.lifetime'),
                    ],
                        Security::getSalt()),
                ]);
            }
        });
        return $this->Crud->execute();
    }

    public function edit($id)
    {

        $this->Crud->on('afterSave', function (Event $event) use ($id) {

            $message = $this->_getCustomValidationErrorMessage($event, 'checkProtected');
            if ($message) {
                throw new ForbiddenException($message);
            }
            if ($event->getSubject()->success) {
                $message = __('User updated');
            } else {
                $message = __('User could not be updated');
            }

            $user = $this->_getUser($id);

            $this->set([
                'data' => $user,
                'message' => $message,
                '_serialize' => ['success', 'data', 'message'],
            ]);

        });
        return $this->Crud->execute();
    }

    public function delete($id)
    {
        $this->Crud->on('beforeDelete', function (Event $event) {
            $notAllowed = FIXTURE;
            $id = $event->getSubject()->entity->id;
            $index = array_search($id, $notAllowed);
            if (is_int($index)) {
                $message = __('Protected users can not be deleted');
                $event->stopPropagation();
                throw new ForbiddenException($message);
            }
        });

        $this->Crud->on('afterDelete', function (Event $event) {
            if ($event->getSubject()->entity->hasErrors()) {
                $errors = $event->getSubject()->entity->getErrors();
                $field = array_key_first($errors);
                $type = array_key_first($errors[$field]);

                $message = $errors[$field][$type];
                throw new ForbiddenException($message);
            }
            $message = __('User deleted');
            $this->set('data', [
                'message' => $message,
            ]);

        });
        return $this->Crud->execute();
    }

    public function token()
    {
        $authUser = $this->Auth->identify();
        if (!$authUser) {
            throw new UnauthorizedException(__('Invalid username or password'));
        }

        $id = $authUser["id"];

        $token = JWT::encode([
            'sub' => $id,
            'exp' => time() + 604800,
        ],
            Security::getSalt());

        $this->set([
            'success' => true,
            'data' => [
                'token' => $token,
            ],
            '_serialize' => ['success', 'data'],
        ]);

    }

    public function login()
    {
        $renewed = false;
        /**
         * Form login:
         * using findWithEmail finder
         * - JWT verification
         * - token check against database
         *
         * Token login:
         * - JWT verification
         */
        $loggedinUser = $this->Auth->identify();

        if (!$loggedinUser) {
            // invalid form login or
            // invalid token
            throw new UnauthorizedException(__('Invalid username or password'));
        } elseif (!$this->_isValidToken($loggedinUser)) {
            // Token valid but didn't pass database check
            throw new UnauthorizedException(__('Invalid Token'));
        }

        // user logged in by form or token, so checking for id or sub
        if (isset($loggedinUser['sub'])) {
            $id = $loggedinUser['sub'];
        } else {
            $id = $loggedinUser["id"];
        }

        // save login time
        $_user = $this->Users->get($id);
        $_user->last_login = date("Y-m-d H:i:s");
        $this->Users->save($_user);

        if ($this->_isAdmin($loggedinUser)) {
            $expires = TableRegistry::getTableLocator()->get('Users')
                ->find()
                ->contain(['Tokens'])
                ->where(['Users.id' => $id])
                ->first()
                ->expires;

            if (time() > $expires) {
                $jwt = $this->createToken($id, time() + 30 * 24 * 3600); // 30*24 hours (30 days)

                $tokenTable = TableRegistry::getTableLocator()->get('Tokens');
                $token = $tokenTable
                    ->find()
                    ->where(['Tokens.user_id' => $id])
                    ->first();

                if (!$token) {
                    $token = $tokenTable->newEntity([
                        'user_id' => $id,
                    ]);
                }
                $token->token = $jwt;
                $tokenTable->save($token);
                $renewed = $id;
            }
        }

        $user = $this->_getUser($id);
        $user["token"] = $user["token"]["token"];

        $this->Auth->setUser($user);

        $this->set([
            'success' => true,
            'data' => [
                'message' => __('Login successful'),
                'user' => $user,
                'groups' => $this->_getGroups(),
                'renewed' => $renewed,
                'wait' => 2000,
            ],
            '_serialize' => ['success', 'data'],
        ]);
    }

    public function logout()
    {
        $this->Auth->logout();
        $this->set([
            'success' => true,
            'message' => __('You\'re logged out'),
            '_serialize' => ['success', 'message'],
        ]);
    }

    protected function _isValidToken($identifiedUser = null)
    {
        if (!isset($identifiedUser)) {
            $identifiedUser = $this->Auth->identify();
        }

        $currentToken = null;

        if (isset($identifiedUser['id'])) {
            // if query database
            $id = $identifiedUser['id'];
        } else {
            // using raw auth information (no database query)
            $id = $identifiedUser['sub'];
        }

        // hydrate the user with associated data
        $user = $this->_getUser($id);
        if (isset($user["token"])) {
            $currentToken = $user["token"]["token"];
        }

        $queryToken = $this->getRequest()->getQuery("token");

        if (isset($queryToken) && $currentToken !== $queryToken) {
            return false;
        } else {
            return true;
        }

    }
}
