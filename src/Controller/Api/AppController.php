<?php
namespace App\Controller\Api;

use Cake\Controller\Controller;
use Cake\Cache\Cache;
use Firebase\JWT\JWT;
use Cake\Core\Configure;
use Cake\Utility\Security;
use Cake\ORM\TableRegistry;
use Cake\Log\Log;

class AppController extends Controller
{
    use \Crud\Controller\ControllerTrait;

    public function initialize() {

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
                            'password' => 'password'
                    ],
                    'finder' => 'withEmail'
                ],
                'ADmad/JwtAuth.Jwt' => [
                    'parameter' => 'token',
                    'userModel' => 'Users',
                    'finder' => 'withId',
                    'fields' => [
                        'username' => 'id'
                    ],
                    'queryDatasource' => true
                ]
            ],
            'unauthorizedRedirect' => true,
            'checkAuthIn' => 'Controller.initialize',
            'loginAction' => false
        ]);

    }

    protected function getJWTPayload($jwt) {
        $tks = \explode('.', $jwt);
        list($headb64, $bodyb64, $cryptob64) = $tks;
        return JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
    }

    protected function createToken($id, $exp=null) {
        if(!isset($exp)) {
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

    protected function getAuthUser($key="") {
        if(!$authUser = $this->Auth->user()) return;
        if(isset($authUser["sub"])) {
            $user = $this->getUser($authUser["sub"]);
        } else {
            $user = $this->getUser($authUser["id"]);
        }
        if(!empty($key)) {
            return $user[$key];
        } else {
            return $user;
        }
        
    }
    protected function getUser($id, array $config=[]) {
        $defaults = [
            'contain' => ['Groups', 'Avatars', 'Videos', 'Tokens'],
            'object' => false,
        ];
        $options = array_merge($defaults, $config);
        if (isset($id['sub'])) {
            $id = $id['sub'];
        }

        if(!is_array($id)) {
            $user = TableRegistry::getTableLocator()->get('Users')
                ->find()
                ->contain($options['contain'])
                ->where(['Users.id' => $id])
                ->first()
                ->toArray();
        } else {
            $user = $id;
        }
        
        return $user;
    }

    protected function isAdmin($user) {
        return $this->getUserRoleName($user) === 'Administrator';
    }

    protected function getUserRoleName(array $user) {
        if(isset($user['sub'])) {
            $user = $this->getUser($user['sub']);
        }
        return TableRegistry::getTableLocator()->get('Groups')
            ->find()
            ->where(['id' => $user['group_id']])
            ->select(['name'])
            ->first()
            ->name;
    }

    protected function getGroups() {
        $groups = TableRegistry::getTableLocator()->get('Groups');

        $_groups = [];
        $data = $groups->find('all')
            ->toArray();
        
        foreach( $data as $group ) {
            $_groups[] = array( 'name' => $group->name, 'id' => $group->id );
        }
        return $_groups;
    }

}