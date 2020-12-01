<?php
namespace App\Controller\Api;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Cache\Cache;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use stdClass;

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
                    'scope' => ['Users.active' => 1]
                ],
                'ADmad/JwtAuth.Jwt' => [
                    'parameter' => 'token',
                    'userModel' => 'Users',
                    'scope' => ['Users.active' => 1],
                    'fields' => [
                        'username' => 'id'
                    ],
                    'queryDatasource' => false
                ]
            ],
            'unauthorizedRedirect' => true,
            'checkAuthIn' => 'Controller.initialize',
            'loginAction' => false
        ]);

        // $session = $this->getRequest()->getSession();
        // $session->destroy();

    }

    protected function isAdmin($user) {
        return $this->getUserRoleName($user) === 'Administrator';
    }

    protected function getUser($id, array $config=[]) {
        $defaults = [
            'contain' => ['Groups', 'Avatars', 'Videos']
        ];
        $options = array_merge($defaults, $config);

        if(!is_array($id)) {
            $user = TableRegistry::getTableLocator()->get('Users')
                ->find()
                ->contain($options['contain'])
                ->where(['Users.id' => $id])
                ->first()
                ->toArray();
        }
        return $user;
    }

    protected function getUserRoleName(array $user) {
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