<?php
namespace App\Controller;

use App\Controller\AppController;

/**
 * Templates Controller
 *
 * @property \App\Model\Table\TemplatesTable $Templates
 *
 * @method \App\Model\Entity\Template[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class TemplatesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $templates = $this->paginate($this->Templates);

        $this->set(compact('templates'));
    }

    /**
     * View method
     *
     * @param string|null $id Template id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $template = $this->Templates->get($id, contain: ['Items']);

        $this->set('template', $template);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $template = $this->Templates->newEmptyEntity();
        if ($this->request->is('post')) {
            $template = $this->Templates->patchEntity($template, $this->request->getData());
            if ($this->Templates->save($template)) {
                $this->Flash->success(__('The template has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The template could not be saved. Please, try again.'));
        }
        $this->set(compact('template'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Template id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $template = $this->Templates->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $template = $this->Templates->patchEntity($template, $this->request->getData());
            if ($this->Templates->save($template)) {
                $this->Flash->success(__('The template has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The template could not be saved. Please, try again.'));
        }
        $this->set(compact('template'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Template id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $template = $this->Templates->get($id);
        if ($this->Templates->delete($template)) {
            $this->Flash->success(__('The template has been deleted.'));
        } else {
            $this->Flash->error(__('The template could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
