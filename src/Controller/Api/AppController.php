<?php
namespace App\Controller\Api;

use Cake\Cache\Cache;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\I18n\I18n;
use Cake\ORM\TableRegistry;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

class AppController extends Controller
{
    use \Crud\Controller\ControllerTrait;

    public function initialize(): void
    {

        parent::initialize();
        Cache::disable();

        $this->loadComponent('RequestHandler');

        $this->loadComponent('Auth', [
            'storage' => 'Memory',
            'authError' => __('Not Authorized'),
            'authenticate' => [
                'Form' => [
                    'fields' => [
                        'username' => 'email',
                        'password' => 'password',
                    ],
                    /*
                     * 'withEmail' includes a token check against database and a JWT verification
                     */
                    'finder' => 'withEmail',
                ],
                'ADmad/JwtAuth.Jwt' => [
                    'parameter' => 'token',
                    'userModel' => 'Users',
                    'finder' => 'withId',
                    'fields' => [
                        'username' => 'id',
                    ],
                    'queryDatasource' => true,
                ],
            ],
            'unauthorizedRedirect' => false,
            'checkAuthIn' => 'Controller.initialize',
            'loginAction' => '',
        ]);

        if (array_key_exists('lang', $this->getRequest()->getQuery())) {
            $language = $this->getRequest()->getQuery('lang');
            I18n::setLocale($language);
        }

    }

    protected function getJWTPayload($jwt)
    {
        $tks = \explode('.', $jwt);
        list($headb64, $bodyb64, $cryptob64) = $tks;
        return JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
    }

    protected function createToken($id, $exp = null)
    {
        if (!isset($exp)) {
            $expires = time() + Configure::read('Token.lifetime');
        } else {
            $expires = $exp;
        }
        return JWT::encode([
            'sub' => $id,
            'exp' => $expires,
        ],
            Security::getSalt());
    }

    protected function _getAuthUser($key = "")
    {
        if (!$authUser = $this->Auth->user()) {
            $authUser = $this->Auth->identify();
        }

        if (!$authUser) {
            return;
        }

        $user = $this->_getUser($authUser);

        if (!empty($key)) {
            return $user[$key];
        } else {
            return $user;
        }

    }
    protected function _getUser($id, array $config = [])
    {
        $defaults = [
            'contain' => ['Groups', 'Avatars', 'Videos', 'Tokens'],
        ];
        $options = array_merge($defaults, $config);
        if (isset($id['sub'])) {
            $id = $id['sub'];
        }

        if (is_array($id)) {
            return $id;
        } else {
            return TableRegistry::getTableLocator()->get('Users')
                ->find()
                ->contain($options['contain'])
                ->where(['Users.id' => $id])
                ->first()
                ->toArray();
        }
    }

    protected function _isAdmin($user)
    {
        return $this->_getUserRoleName($user) === 'Administrator';
    }

    protected function _getAdmins()
    {
        $table = TableRegistry::getTableLocator()->get('Users');
        $res = $table->find()
            ->contain(['Groups'])
            ->where(['Users.group_id' => $this->_getRoleIdFromName('Administrator')])
            ->toList();
        return $res;
    }

    protected function _getUserRoleName(array $user = [])
    {
        if (isset($user['sub'])) {
            $user = $this->_getUser($user['sub']);
        }
        return TableRegistry::getTableLocator()->get('Groups')
            ->find()
            ->where(['id' => $user['group_id']])
            ->select(['name'])
            ->first()
            ->toArray()['name'];
    }

    protected function _getRoleIdFromName($rolename = 'User')
    {

        return TableRegistry::getTableLocator()->get('Groups')
            ->find()
            ->where(['Groups.name' => $rolename])
            ->select(['id'])
            ->first()
            ->toArray()['id'];
    }

    protected function _getGroups()
    {
        $groups = TableRegistry::getTableLocator()->get('Groups');

        $_groups = [];
        $data = $groups->find('all')
            ->toArray();

        foreach ($data as $group) {
            $_groups[] = array('name' => $group->name, 'id' => $group->id);
        }
        return $_groups;
    }

    protected function _getCustomValidationErrorMessage(Event $event, $ruleName)
    {
        if ($event->getSubject()->entity->hasErrors()) {
            $errors = $event->getSubject()->entity->getErrors();

            if (!empty($errors)) {
                $first_key = array_key_first($errors);
                if (isset($errors[$first_key][$ruleName])) {
                    return $errors[$first_key][$ruleName];
                }

            }
        }

    }

}
