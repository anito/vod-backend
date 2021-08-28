<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Inboxes Controller
 *
 * @property \App\Model\Table\InboxesTable $Inboxes
 *
 * @method \App\Model\Entity\Inbox[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class InboxesController extends AppController
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
        $inboxes = $this->paginate($this->Inboxes);

        $this->set(compact('inboxes'));
    }

    /**
     * View method
     *
     * @param string|null $id Inbox id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $inbox = $this->Inboxes->get($id, [
            'contain' => ['Users'],
        ]);

        $this->set('inbox', $inbox);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $inbox = $this->Inboxes->newEmptyEntity();
        if ($this->request->is('post')) {
            $inbox = $this->Inboxes->patchEntity($inbox, $this->request->getData());
            if ($this->Inboxes->save($inbox)) {
                $this->Flash->success(__('The inbox has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The inbox could not be saved. Please, try again.'));
        }
        $users = $this->Inboxes->Users->find('list', ['limit' => 200]);
        $this->set(compact('inbox', 'users'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Inbox id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $inbox = $this->Inboxes->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $inbox = $this->Inboxes->patchEntity($inbox, $this->request->getData());
            if ($this->Inboxes->save($inbox)) {
                $this->Flash->success(__('The inbox has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The inbox could not be saved. Please, try again.'));
        }
        $users = $this->Inboxes->Users->find('list', ['limit' => 200]);
        $this->set(compact('inbox', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Inbox id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $inbox = $this->Inboxes->get($id);
        if ($this->Inboxes->delete($inbox)) {
            $this->Flash->success(__('The inbox has been deleted.'));
        } else {
            $this->Flash->error(__('The inbox could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
