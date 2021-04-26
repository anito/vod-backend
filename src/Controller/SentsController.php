<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Sents Controller
 *
 * @property \App\Model\Table\SentsTable $Sents
 *
 * @method \App\Model\Entity\Sent[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SentsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Users'],
        ];
        $sents = $this->paginate($this->Sents);

        $this->set(compact('sents'));
    }

    /**
     * View method
     *
     * @param string|null $id Sent id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $sent = $this->Sents->get($id, [
            'contain' => ['Users'],
        ]);

        $this->set('sent', $sent);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $sent = $this->Sents->newEntity();
        if ($this->request->is('post')) {
            $sent = $this->Sents->patchEntity($sent, $this->request->getData());
            if ($this->Sents->save($sent)) {
                $this->Flash->success(__('The sent has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The sent could not be saved. Please, try again.'));
        }
        $users = $this->Sents->Users->find('list', ['limit' => 200]);
        $this->set(compact('sent', 'users'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Sent id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $sent = $this->Sents->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $sent = $this->Sents->patchEntity($sent, $this->request->getData());
            if ($this->Sents->save($sent)) {
                $this->Flash->success(__('The sent has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The sent could not be saved. Please, try again.'));
        }
        $users = $this->Sents->Users->find('list', ['limit' => 200]);
        $this->set(compact('sent', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Sent id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $sent = $this->Sents->get($id);
        if ($this->Sents->delete($sent)) {
            $this->Flash->success(__('The sent has been deleted.'));
        } else {
            $this->Flash->error(__('The sent could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
