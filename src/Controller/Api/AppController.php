<?php
namespace App\Controller\Api;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Cache\Cache;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;

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

    }

    protected function _getUserRoleName($user) {
        $groups = TableRegistry::getTableLocator()->get('Groups');
        return $groups->find()
            ->where(['id' => $user['group_id']])
            ->select(['name'])
            ->first()
            ->name;
    }

    protected function _getGroups() {
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