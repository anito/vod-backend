<?php

/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller\V1;

use Cake\Core\Configure;
use Cake\Event\Event;

class SettingsController extends AppController
{

  public function initialize(): void
  {
    parent::initialize();

    $this->Authentication->allowUnauthenticated(['index']);

    $this->loadComponent('Crud.Crud', [
      'actions' => [
        'Crud.Index',
      ],
      'listeners' => [
        'Crud.Api',
        'Crud.ApiPagination',
      ],
    ]);
  }

  public function index()
  {
    /**
     * Publicly available settings
     */

    // Site
    $allowedSite = ['logo', 'name', 'description', 'defaultadmintab', 'defaultusertab','salutations'];
    // Session
    $allowedSession = ['lifetime', 'logoutredirect'];
    // Console
    $allowedConsole = ['infoLevel', 'log'];
    // Misc
    $allowedMisc = ['web-vitals'];

    $Session = array_intersect_key(Configure::read('Session'), array_flip($allowedSession));
    $Site = array_intersect_key(Configure::read('Site'), array_flip($allowedSite));
    $Console = array_intersect_key(Configure::read('Console'), array_flip($allowedConsole));
    $Misc = array_intersect_key(Configure::read('Misc'), array_flip($allowedMisc));

    $this->set([
      'success' => true,
      'data' => compact('Session', 'Site', 'Console', 'Misc'),
    ]);
    $this->viewBuilder()->setOption('serialize', ['success', 'data']);
  }
}
