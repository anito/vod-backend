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
use Cake\Event\EventInterface;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cake\View\JsonView;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

  /**
   * Initialization hook method.
   *
   * Use this method to add common initialization code like loading components.
   *
   * e.g. `$this->loadComponent('Security');`
   *
   * @return void
   */
  public function initialize(): void
  {
    parent::initialize();

    $this->loadComponent('Flash');
    $this->loadComponent('Authentication.Authentication', [
      'identityCheckEvent' => 'Controller.initialize', // Defaults to Controllers startup action
      'unauthenticatedMessage' => 'Authentication is required to continue',
      'requireIdentity' => true
    ]);

    /**
     *  Enable the following component for recommended CakePHP security settings.
     *  see https://book.cakephp.org/3/en/controllers/components/security.html
     */
    // $this->loadComponent('Security');
  }

  public function beforeFilter(EventInterface $event)
  {
    parent::beforeFilter($event);
    $this->Authentication->allowUnauthenticated(['display']);
  }

  protected function _isPrivileged(Entity $user)
  {
    $roles = [ADMIN, SUPERUSER];
    return in_array($this->_getUserRoleName($user), $roles);
  }

  protected function _getUserRoleName(Entity $user)
  {
    $groups = TableRegistry::getTableLocator()->get('Groups');
    return $groups->find()
      ->where(['id' => $user->group->id])
      ->select(['name'])
      ->first()
      ->name;
  }
}
