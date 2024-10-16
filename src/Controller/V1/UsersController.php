<?php

namespace App\Controller\V1;

use Cake\Collection\CollectionInterface;
use Cake\Event\Event;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\UnauthorizedException;
use Cake\ORM\TableRegistry;

class UsersController extends AppController
{
  public function initialize(): void
  {
    parent::initialize();
    $this->Authentication->allowUnauthenticated(['logout', 'login', 'google', 'facebook', 'lookup']);

    $this->loadComponent('Crud.Crud', [
      'actions' => [
        'simpleindex' => [
          'className' => 'Crud.Index',
        ],
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
        // 'Crud.ApiQueryLog'
      ],
    ]);

    $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');

    $this->Users->getEventManager()->on('User.registration', function ($event, $entity) {
      /**
       * Todo
       */
    });
  }

  public function beforeFilter(EventInterface $event)
  {
    $searchParams = $this->request->getQuery();
    if (!isset($searchParams['keys'])) {
      $this->Crud->addListener('Crud.ApiPagination');
    }
  }

  public function index()
  {
    $authUser = $this->_getAuthUser();

    $this->Crud->on('afterPaginate', function (Event $event) use ($authUser) {

      // limit defaults to 20
      // maxLimit defaults to 100
      // augmented by the sort, direction, limit, and page parameters when passed in from the URL
      $settings = [
        'limit' => 10,
      ];
      $query = $event->getSubject()->query;
      if (!$this->_isPrivileged($authUser)) {
        // query the authenticated user only
        $query->where(['Users.id' => $authUser->id]);
      } else {
        $condition = [];
        $queryParams = $this->request->getQueryParams();
        if (isset($queryParams['keys']) && isset($queryParams['search'])) {
          $keys = $queryParams['keys'];
          $keys = explode(",", $keys);
          $keys = preg_replace('/\s+/', '', $keys);
          $search = $queryParams['search'];
          $table = TableRegistry::getTableLocator()->get('Users');
          foreach ($keys as $key) {
            if ($table->hasField($key)) {
              $condition['Users.' . $key . ' LIKE'] = '%' . $search . '%';
            }
          }
          $condition = ['OR' => $condition];
        }
        $query
          ->where($condition)
          // remove jwt from Superusers
          ->formatResults(function (CollectionInterface $results) {
            $superUserGroupId = $this->_getRoleIdFromName(SUPERUSER);
            return $results->map(function ($row) use ($superUserGroupId) {
              if ($row['group_id'] === $superUserGroupId) {
                $row['token'] = null;
              }
              return $row;
            });
          });
      }
      $query->order(['Users.name' => 'ASC']);

      $users = $this->paginate($query, $settings);
      $this->set('data', $users);
    });

    $this->Crud->action()->serialize(['data']);
    return $this->Crud->execute();
  }

  /**
   * Queries a list with only  limited information about email and avatar,
   * in order to lookup an Avatar that belongs to an specific email address
   * - used for an Email Client on https://vod-app.doojoo.de
   */
  public function simpleindex()
  {
    $users = $this->Users->find('all', fields: [
        'Users.id',
        'Users.email',
        'Users.name',
        'Avatars.src',
        'Avatars.id'
      ])
      ->contain('Avatars')
      ->all();

    $this->set('data', $users);

    $this->Crud->action()->serialize(['data']);
    return $this->Crud->execute();
  }

  public function view()
  {
    $this->Crud->on('beforeFind', function (Event $event) {

      $user = $event->getSubject()->query
        ->contain(
          'Sents',
          function ($q) {
            return $q
              ->select([
                'Sents.user_id',
                'total' => $q->func()->count('*'),
              ]);
          }
        )
        ->contain(
          'Inboxes',
          function ($q) {
            return $q
              ->select([
                'Inboxes.user_id',
                'total' => $q->func()->count('*'),
                'readings' => $q->func()->sum('Inboxes._read'),
              ]);
          }
        )
        ->contain(['Groups', 'Avatars', 'Videos', 'Tokens'])
        ->first();

      $this->set('data', $user);
    });

    $this->Crud->action()->serialize(['data']);

    return $this->Crud->execute();
  }

  public function add()
  {
    $this->Crud->on('afterSave', function (Event $event) {
      if ($event->getSubject()->entity->hasErrors()) {
        $errors = $event->getSubject()->entity->getErrors();
        $field = array_key_first($errors);
        $type = array_key_first($errors[$field]);

        $message = $errors[$field][$type];
        throw new ForbiddenException($message);
      }

      if ($event->getSubject()->created) {
        $id = $event->getSubject()->entity->id;
        $user = $event->getSubject()->entity->toArray();
        $this->set(
          [
            'data' => $user,
            // [
            // 'id' => $id,
            // 'token' => JWT::encode(
            //   [
            //     'sub' => $id,
            //     'exp' => time() + Configure::read('Token.lifetime'),
            //   ],
            //   Security::getSalt()
            // ),
            // ],
            'message' => __('User created'),
          ]
        );
        $this->Crud->action()->serialize(['data', 'message']);
      }
    });
    return $this->Crud->execute();
  }

  public function edit($id)
  {
    $this->Crud->on('afterSave', function (Event $event) use ($id) {

      if ($event->getSubject()->success) {
        $message = __('User updated');
      } else {
        $message = __('User could not be updated');
      }

      $user = $this->_getUser($id);

      $this->set([
        'data' => $user->toArray(),
        'message' => $message,
      ]);

      $this->Crud->action()->serialize(['data', 'message']);
    });
    return $this->Crud->execute();
  }

  public function delete($id)
  {
    $this->Crud->on('afterDelete', function (Event $event) {
      if ($event->getSubject()->entity->hasErrors()) {
        $errors = $event->getSubject()->entity->getErrors();
        $field = array_key_first($errors);
        $type = array_key_first($errors[$field]);

        $message = $errors[$field][$type];
        throw new ForbiddenException($message);
      }
      $message = __('User deleted');
      $this->set([
        'data' => [],
        'message' => $message,
      ]);

      $this->Crud->action()->serialize(['data', 'message']);
    });
    return $this->Crud->execute();
  }

  public function token()
  {
    $user = $this->_getAuthUser();
    $isAdmin = $this->_isPrivileged($user);

    if (!$isAdmin) {
      throw new UnauthorizedException(__('Unauthorized'));
    };

    $uid = $this->Authentication->getIdentity()->getIdentifier();

    $token = createJWT($uid);

    $this->set([
      'success' => true,
      'data' => [
        'token' => $token,
      ],
    ]);
    $this->viewBuilder()->setOption('serialize', ['success', 'data']);
  }

  public function facebook()
  {
    $payload = $this->request->getData();
    $payload = json_decode(json_encode($payload), FALSE);
    $id = $payload->id;
    $user = $this->Users->find()
      ->contain(['Groups', 'Avatars', 'Videos', 'Tokens'])
      ->where(['Users.id' => $id])
      ->first();

    $tokenTable = TableRegistry::getTableLocator()->get('Tokens');
    $avatarTable = TableRegistry::getTableLocator()->get('Avatars');

    if (empty($user)) {
      $jwt = $this->_createToken($id);

      $token = $tokenTable->newEntity([
        'token' => $jwt,
        'user_id' => $id,
      ]);
      $avatar = $avatarTable->newEntity([
        'src' => $payload->picture->url,
        'user_id' => $id
      ]);
      $user = $this->Users->newEntity([
        'id' => $id,
        'email' => $payload->email,
        'password' => randomString(),
        'name' => $payload->name,
        'active' => 1,
        'group_id' => $this->_getRoleIdFromName(USER),
      ]);

      $user->token = $token;
      $user->avatar = $avatar;
    } else {
      $jwt = $user->jwt;
      if (!isset($jwt)) {
        $jwt = $this->_createToken($payload->sub);
        $token = $tokenTable->newEntity([
          'token' => $jwt,
          'user_id' => $id,
        ]);
        $user->token = $token;
      }
      if (isset($user->avatar)) {
        $avatar = $user->avatar;
        if ($avatar->src !== $payload->picture->url) {
          $this->Users->Avatars->patchEntity($avatar, [
            'src' => $payload->picture->url,
            'user_id' => $user->id
          ]);
          $user->avatar = $avatar;
        }
      } else {
        $avatar = $avatarTable->newEntity([
          'user_id' => $user->id,
          'src' => $payload->picture->url,
        ]);
        $user->avatar = $avatar;
      }
    }

    $saved = $this->Users->save($user);
    $success = $saved && !!$saved->jwt && !!$saved->active;

    if ($success) {
      $user = $this->_getUser($user->id);
      $groups = $this->_getGroups();
      $token = $jwt;
      $message = __('Facebook Login successful');
    } else {
      $user = [];
      $groups = [];
      $token = '';
      $message = __('Facebook Login failed');
    }

    $this->set([
      'success' => $success,
      'data' => compact(['user', 'groups', 'token', 'message']),
    ]);
    $this->viewBuilder()->setOption('serialize', ['success', 'data']);
  }

  public function google()
  {
    $header = $this->request->getHeaderLine(AUTH_HEADER);
    if ($header && stripos($header, AUTH_PREFIX) === 0) {
      $token = str_ireplace(AUTH_PREFIX . ' ', '', $header);
    } else {
      $token = $this->request->getQuery('token');
    }

    if (!isset($token)) {
      throw new ForbiddenException(__('You must provide a token to sign into your Google account'));
    }

    $payload = $this->_getJWTPayload($token);

    $user = $this->Users->find()
      ->contain(['Groups', 'Avatars', 'Videos', 'Tokens'])
      ->where(['Users.id' => $payload->sub])
      ->first();

    $tokenTable = TableRegistry::getTableLocator()->get('Tokens');
    $avatarTable = TableRegistry::getTableLocator()->get('Avatars');

    if (empty($user)) {
      $jwt = $this->_createToken($payload->sub);
      $token = $tokenTable->newEntity([
        'token' => $jwt,
        'user_id' => $payload->sub,
      ]);
      $avatar = $avatarTable->newEntity([
        'src' => $payload->picture,
        'user_id' => $payload->sub
      ]);
      $user = $this->Users->newEntity([
        'id' => $payload->sub,
        'email' => $payload->email,
        'password' => randomString(),
        'name' => $payload->name,
        'active' => 1,
        'group_id' => $this->_getRoleIdFromName(USER),
      ]);

      $user->token = $token;
      $user->avatar = $avatar;
    } else {
      $jwt = $user->jwt;
      if (!isset($jwt)) {
        $jwt = $this->_createToken($payload->sub);
        $token = $tokenTable->newEntity([
          'token' => $jwt,
          'user_id' => $payload->sub,
        ]);
        $user->token = $token;
      }
      if (isset($user->avatar)) {
        $avatar = $user->avatar;
        if ($avatar->src !== $payload->picture) {
          $this->Users->Avatars->patchEntity($avatar, [
            'src' => $payload->picture,
            'user_id' => $user->id
          ]);
          $user->avatar = $avatar;
        }
      } else {
        $avatar = $avatarTable->newEntity([
          'user_id' => $user->id,
          'src' => $payload->picture,
        ]);
        $user->avatar = $avatar;
      }
    }
    $saved = $this->Users->save($user);
    $success = $saved && !!$saved->jwt && !!$saved->active;

    if ($success) {
      $user = $this->_getUser($user->id);
      $groups = $this->_getGroups();
      $token = $jwt;
      $message = __('Google Login successful');
    } else {
      $user = [];
      $groups = [];
      $token = '';
      $message = __('Google Login failed');
    }

    $this->set([
      'success' => $success,
      'data' => compact(['user', 'groups', 'token', 'message']),
    ]);
    $this->viewBuilder()->setOption('serialize', ['success', 'data']);
  }

  public function getController() {
    return $this->request->getParam('controller');
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
    $loggedinUser = $this->_getAuthUser();

    if (!$loggedinUser) {
      // invalid form login or invalid token
      throw new UnauthorizedException(__('Invalid username or password'));
    }

    $isPrivileged = $this->_isPrivileged($loggedinUser);

    // Check token against db on none privileged users
    if (!$this->_isValidToken($loggedinUser) && !$isPrivileged) {
      // Token valid but didn't pass database check
      throw new UnauthorizedException(__('Invalid Token'));
    }

    $id = $loggedinUser->id;

    // Handle privileged users jwt if expired or empty
    if ($isPrivileged) {
      $expires = $this->Users
        ->find()
        ->contain(['Tokens'])
        ->where(['Users.id' => $id])
        ->first()
        ->expires;

      if (time() > $expires) {
        $jwt = $this->_createToken($id, time() + 30 * 24 * 3600);

        $token = $this->Users->Tokens
          ->find()
          ->where(['Tokens.user_id' => $id])
          ->first();

        if (!$token) {
          $token = $this->Users->Tokens->newEntity([
            'user_id' => $id,
          ]);
        }
        $token->token = $jwt;
        $this->Users->Tokens->save($token);
        $renewed = $id;
      }
    }

    // save login time
    $_user = $this->Users->get($id);
    $_user->last_login = date("Y-m-d H:i:s");
    $this->Users->save($_user);

    $user = $this->_getUser($id);

    $this->set([
      'success' => true,
      'data' => [
        'user' => $user->toArray(),
        'groups' => $this->_getGroups(),
        'renewed' => $renewed,
        'message' => __('Login successful')
      ],
    ]);
    $this->viewBuilder()->setOption('serialize', ['success', 'data', 'message']);
  }

  public function logout()
  {
    $this->Authentication->logout();
    $this->set([
      'success' => true,
      'message' => __('You have been logged out'),
    ]);
    $this->viewBuilder()->setOption('serialize', ['success', 'message']);
  }

  protected function _isValidToken($identifiedUser = null)
  {
    if (!isset($identifiedUser)) {
      $identifiedUser = $this->_getAuthUser();
    }

    $currentToken = null;

    if (isset($identifiedUser->id)) {
      // if query database
      $id = $identifiedUser->id;
    } else {
      // using raw auth information (no database query)
      $id = $identifiedUser->sub;
    }

    // hydrate the user with associated data
    $user = $this->_getUser($id);
    if (isset($user->jwt)) {
      $currentToken = $user->jwt;
    }

    $queryToken = $this->getRequest()->getQuery("token");

    if (!isset($currentToken)) {
      return false;
    } else if (isset($queryToken) && $currentToken !== $queryToken) {
      return false;
    } else {
      return true;
    }
  }
}
