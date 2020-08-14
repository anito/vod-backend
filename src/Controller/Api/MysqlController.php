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
use Cake\Datasource\ConnectionManager;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Cache\Cache;
use Cake\I18n\Time;
use Cake\Log\Log;

class MysqlController extends AppController {

    function initialize() {
        parent::initialize();
        $this->autoRender = false;
    }

    public function index() {

        $user = $this->Auth->identify();

        if (!$user) {
            throw new UnauthorizedException(__('Invalid username or password'));
        }
        $this->recover();
        $this->Crud->on('beforeRender', [$this, '_beforeRender']);
        return $this->Crud->execute();
    }

    public function _beforeRender($event) {
        $entities = $event->getSubject()->entities;
        foreach ($entities as $entity) {
            $entity->description = "a description";
            $created = $entity->created;
            $timestamp = $created->toUnixString();
            $created = $created->i18nFormat('d. MMM yyy HH:mm B', 'Europe/Berlin', 'de-DE');
            $entity->created = $created;
            $entity->human = express_date_diff($timestamp);
        }

    }

    public function restore() {

        $result = mysql('restore');

        $this->set([
            'success' => $result['success'],
            'message' => $result['message'],
            'filename' => $result['filename'],
            '_serialize' => ['success', 'message', 'filename']
        ]);

        $this->render();

        // c(MAX_DUMPS); // cleanup dump files

    }

    protected function recover( $files = [] ) {
        if( empty($files) ) {
            $files = l();
            // 
        }

        foreach ($files as $key => $file) {
            $mysql = $this->Mysql->newEntity();
            $created = Time::createFromTimestamp($file);
            $$mysql = $this->Mysql->patchEntity($mysql, ['filename' => $key, 'description' => 'recovered', 'created' => $created]);
            $this->Mysql->save($mysql);
        }

    }
}
