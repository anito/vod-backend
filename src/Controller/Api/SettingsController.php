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
namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\Configure;

class SettingsController extends AppController
{

    public function initialize(): void
    {
        parent::initialize();

        $this->Auth->allow(['index']);

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

        $allowed = ['Session', 'Site'];
        $settings = Configure::read();
        $settings = array_intersect_key($settings, array_flip($allowed));

        $this->set([
            'success' => true,
            'data' => [
                'settings' => $settings,
            ],
            '_serialize' => ['success', 'data'],
        ]);
    }
}
