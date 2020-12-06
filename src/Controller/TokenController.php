<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Token Controller
 *
 *
 * @method \App\Model\Entity\Token[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TokenController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $token = $this->paginate($this->Token);

        $this->set(compact('token'));
    }

    /**
     * View method
     *
     * @param string|null $id Token id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $token = $this->Token->get($id, [
            'contain' => [],
        ]);

        $this->set('token', $token);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $token = $this->Token->newEntity();
        if ($this->request->is('post')) {
            $token = $this->Token->patchEntity($token, $this->request->getData());
            if ($this->Token->save($token)) {
                $this->Flash->success(__('The token has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The token could not be saved. Please, try again.'));
        }
        $this->set(compact('token'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Token id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $token = $this->Token->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $token = $this->Token->patchEntity($token, $this->request->getData());
            if ($this->Token->save($token)) {
                $this->Flash->success(__('The token has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The token could not be saved. Please, try again.'));
        }
        $this->set(compact('token'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Token id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $token = $this->Token->get($id);
        if ($this->Token->delete($token)) {
            $this->Flash->success(__('The token has been deleted.'));
        } else {
            $this->Flash->error(__('The token could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
