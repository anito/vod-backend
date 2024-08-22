<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Screenshots Controller
 *
 * @property \App\Model\Table\ScreenshotsTable $Screenshots
 */
class ScreenshotsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Screenshots->find();
        $screenshots = $this->paginate($query);

        $this->set(compact('screenshots'));
    }

    /**
     * View method
     *
     * @param string|null $id Screenshot id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $screenshot = $this->Screenshots->get($id, contain: []);
        $this->set(compact('screenshot'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $screenshot = $this->Screenshots->newEmptyEntity();
        if ($this->request->is('post')) {
            $screenshot = $this->Screenshots->patchEntity($screenshot, $this->request->getData());
            if ($this->Screenshots->save($screenshot)) {
                $this->Flash->success(__('The screenshot has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The screenshot could not be saved. Please, try again.'));
        }
        $this->set(compact('screenshot'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Screenshot id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $screenshot = $this->Screenshots->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $screenshot = $this->Screenshots->patchEntity($screenshot, $this->request->getData());
            if ($this->Screenshots->save($screenshot)) {
                $this->Flash->success(__('The screenshot has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The screenshot could not be saved. Please, try again.'));
        }
        $this->set(compact('screenshot'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Screenshot id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $screenshot = $this->Screenshots->get($id);
        if ($this->Screenshots->delete($screenshot)) {
            $this->Flash->success(__('The screenshot has been deleted.'));
        } else {
            $this->Flash->error(__('The screenshot could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
