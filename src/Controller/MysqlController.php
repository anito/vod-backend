<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Routing\Router;
use Cake\Utility\Security;
use Cake\Core\Configure;
use Cake\Cache\Cache;
use Cake\Log\Log;

/**
 * Mysql Controller
 *
 * @property \App\Model\Table\MysqlTable $Mysql
 *
 * @method \App\Model\Entity\Mysql[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MysqlController extends AppController
{

    function initialize() {
        parent::initialize();

        // $this->autoRender = false;
        // $this->viewBuilder()->disableAutoLayout();
        $this->loadComponent('Salt');

        Cache::disable();
        $this->request = Router::parseNamedParams($this->request);

        $this->Auth->allow(['getFile', 'uri']);
        define('USE_X_SEND', false);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $mysql = $this->paginate($this->Mysql);

        $this->set(compact('mysql'));
    }

    /**
     * View method
     *
     * @param string|null $id Mysql id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $mysql = $this->Mysql->get($id, [
            'contain' => []
        ]);

        $this->set('mysql', $mysql);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $mysql = $this->Mysql->newEntity();
        if ($this->request->is('post')) {
            $mysql = $this->Mysql->patchEntity($mysql, $this->request->getData());
            if ($this->Mysql->save($mysql)) {
                $this->Flash->success(__('The mysql has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The mysql could not be saved. Please, try again.'));
        }
        $this->set(compact('mysql'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Mysql id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $mysql = $this->Mysql->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $mysql = $this->Mysql->patchEntity($mysql, $this->request->getData());
            if ($this->Mysql->save($mysql)) {
                $this->Flash->success(__('The mysql has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The mysql could not be saved. Please, try again.'));
        }
        $this->set(compact('mysql'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Mysql id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $mysql = $this->Mysql->get($id);
        if ($this->Mysql->delete($mysql)) {
            $this->Flash->success(__('The mysql has been deleted.'));
        } else {
            $this->Flash->error(__('The mysql could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function uri() {
        $this->autoRender = false;

        $json = array();
        $fn = '*.*';
        if ($this->Auth->user()) {
            $uid = $this->Auth->user('id');

            if (!empty($this->data)) {
                foreach ($this->data as $data) {
                    if(!empty($data['fn'])) {
                        $fn = $data['fn'];
                    }
                }
            }
            $path = MYSQLUPLOAD . DS . $fn;
            $files = glob($path);
            $fn = basename($files[0]);
            $redirect = $this->request->getParam('redirect');

            if(!empty($files[0])) {
                $options = compact(array('uid', 'fn'));
                $file = p($this, $options);
            } else {
                $message = 'kein Download verfügbar';
                $result = 'error';
                header("Location: http://" . $_SERVER['HTTP_HOST'] . str_replace('//', '/', '/' . BASE_URL . '/pages/response?m=' . urlencode( $message ) . '&c=' . $result . '&redirect=' . urlencode($redirect) ));
                die;
            }
        } else {
            header('HTTP/1.1 403 Forbidden');
            die;
        }
        header("Location: $file" );
        die;
    }

    function getFile() {
        $this->autoRender = false;

        $val = $this->request->getParam('named.a');

        if (strpos($val, 'http://') !== false || substr($val, 0, 1) == '/') {
            header('Location: ' . $val);
            exit;
        } else {
            $val = str_replace(' ', '.2B', $val);
        }

        $crypt = $this->Salt->convert($val, false); //decode
        $a = explode(',', $crypt);

        $file = $fn = basename($a[1]);

        // Make sure supplied filename contains only approved chars
        if (preg_match("/[^A-Za-z0-9._-]/", $file)) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $file = MYSQLUPLOAD . DS . $file;
        $disabled_functions = explode(',', ini_get('disable_functions'));

        if (USE_X_SEND) {
            header("X-Sendfile: $file");
            header("Content-type: application/octet-stream");
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        } else {
            header('Content-type: application/octet-stream');
            header('Content-length: ' . filesize($file));
            header('Cache-Control: public');
            header('Expires: ' . gmdate('D, d M Y H:i:s', strtotime('+1 year')));
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file)));
            if (is_callable('readfile') && !in_array('readfile', $disabled_functions)) {
                readfile($file);
            } else {
                die(file_get_contents($file));
            }
        }
    }
}
