<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Cache\Cache;
use Cake\Log\Log;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    use \Crud\Controller\ControllerTrait;

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        // $this->autoRender = false;
        // $this->viewBuilder()->disableAutoLayout();

        if( $this->request->is('ajax') ) {
            $this->viewBuilder()-> disableAutoLayout();
        } else {
            $this->viewBuilder()->setLayout('default');
        }


        Cache::disable();

        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);
        $this->loadComponent('Flash');

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'Crud.Index',
                'Crud.View',
                'Crud.Add',
                'Crud.Edit',
                'Crud.Delete'
            ],
            'listeners' => [
                'Crud.Api',
                'Crud.ApiPagination',
                'Crud.ApiQueryLog'
            ]
        ]);

        $this->loadComponent('Auth', [
            'storage' => 'Session',
            'authError' => __('Please log in'),
            'authenticate' => [
                'Form' => [
                    'scope' => ['Users.active' => 1]
                ],
            ],
            'loginAction' => [
                'controller' => 'Users',
                'action' => 'login'
            ],
            'unauthorizedRedirect' => true, // If unauthorized, return them to page they were just on
            'checkAuthIn' => 'Controller.initialize',
        ]);

        /*
         * Enable the following component for recommended CakePHP security settings.
         * see https://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        // $this->loadComponent('Security');

    }

    public function isAuthGroup() {
        $groups = $this->allowedGroups;
        if (in_array($this->_groupName(), $groups)) {
            return true;
        }
        return false;
    }

    public function isAdmin() {
        $group = 'Administrators';
        if ( $this->_groupName() == $group ) {
            return true;
        }
        return false;
    }

    public function _groupName() {
        if ($this->Auth->user('id')) {
            $this->Users->Groups->recursive = 0;
            $group = $this->Users->Groups->get($this->Auth->user('group_id'));
            return $group['name'];
        }
    }

}
