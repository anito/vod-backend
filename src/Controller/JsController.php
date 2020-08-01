<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Log\Log;

/**
 * Js Controller
 *
 * @property \App\Model\Table\UsersTable $Js
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class JsController extends AppController
{
    public function initialize() {
        parent::initialize();

        $this->autoRender = false;
        $this->viewBuilder()->disableAutoLayout();
    }

    public function app() {
        Log::write('debug', '*** JsController::app***');
    }
}
