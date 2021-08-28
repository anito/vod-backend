<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Avatars Controller
 *
 * @property \App\Model\Table\AvatarsTable $Avatars
 *
 * @method \App\Model\Entity\Avatar[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AvatarsController extends AppController
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
        $avatars = $this->paginate($this->Avatars);

        $this->set(compact('avatars'));
    }

    /**
     * View method
     *
     * @param string|null $id Avatar id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $avatar = $this->Avatars->get($id, [
            'contain' => ['Users'],
        ]);

        $this->set('avatar', $avatar);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $avatar = $this->Avatars->newEmptyEntity();
        if ($this->request->is('post')) {
            $avatar = $this->Avatars->patchEntity($avatar, $this->request->getData());
            if ($this->Avatars->save($avatar)) {
                $this->Flash->success(__('The avatar has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The avatar could not be saved. Please, try again.'));
        }
        $users = $this->Avatars->Users->find('list', ['limit' => 200]);
        $this->set(compact('avatar', 'users'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Avatar id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $avatar = $this->Avatars->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $avatar = $this->Avatars->patchEntity($avatar, $this->request->getData());
            if ($this->Avatars->save($avatar)) {
                $this->Flash->success(__('The avatar has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The avatar could not be saved. Please, try again.'));
        }
        $users = $this->Avatars->Users->find('list', ['limit' => 200]);
        $this->set(compact('avatar', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Avatar id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $avatar = $this->Avatars->get($id);
        if ($this->Avatars->delete($avatar)) {
            $this->Flash->success(__('The avatar has been deleted.'));
        } else {
            $this->Flash->error(__('The avatar could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
