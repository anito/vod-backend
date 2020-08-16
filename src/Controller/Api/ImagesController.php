<?php

namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\App;
use Cake\Event\Event;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Utility\Text;
use Cake\Log\Log;


/**
 * Images Controller
 *
 *
 * @method \App\Model\Entity\Video[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ImagesController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow([]);
        $this->loadComponent('File');
        $this->loadComponent('Director');
        $this->loadComponent('Upload');
    }

    public function add()
    {

        if (!empty($files = $this->request->getData('Image'))) {

            // make shure single uploads are handled correctly
            if(!empty($files['tmp_name'])) $files = [$files];

            if (!empty($images = $this->Upload->saveUploadedFiles($files))) {

                $images = $this->Images->newEntities($images);

                if ($data = $this->Images->saveMany($images)) {

                    $this->set([
                        'success' => true,
                        'data' => $data,
                        '_serialize' => ['success', 'data'],
                    ]);
                } else {

                    $this->set([
                        'success' => false,
                        'data' => [],
                        'message' => 'An Error occurred while saving your data',
                        '_serialize' => ['success', 'data', 'message'],
                    ]);
                }
            } else {

                $this->set([
                    'success' => false,
                    'data' => [],
                    'message' => 'An Error occurred while uploading your files',
                    '_serialize' => ['success', 'data', 'message'],
                ]);
            }
        }
    }

    public function delete()
    {
        $this->Crud->on('beforeDelete', function (Event $event) {

            if ($this->Auth->identify()) {

                $id = $event->getSubject()->entity->id;
                $fn = $event->getSubject()->entity->src;

                $path = IMAGES . DS . $id;
                $lg_path = $path . DS . 'lg';

                $oldies = glob($lg_path . DS . $fn);

                if (!empty($oldies) && $oldies && !unlink($oldies[0])) {
                    $event->stopPropagation();
                } else {
                    $this->File->rmdirr($path);
                }
            }
        });
        return $this->Crud->execute();
    }

    public function uri($id)
    {
        $data = [];
        
        $params = $this->getRequest()->getQuery();
        $lg_path = IMAGES . DS . $id . DS . 'lg';
        $files = glob($lg_path . DS . '*.*');
        $fn = basename($files[0]);
        
        if (!empty($files[0])) {
            $options = array_merge(compact(array('fn', 'id')), $params);
            $p = $this->Director->p($options);
            $json = json_encode($params);
            $stringified = preg_replace('/["\'\s]/', '', $json);
            $data = array(
                'id' => $id,
                'url' => $p,
                'params' => $stringified
                // preg_replace('/[\"\'\s]/i', '', json_encode($params)) => $this->Director->p($options)
            );
            
            $this->set(
                [
                    'success' => true,
                    'data' => $data,
                    '_serialize' => ['success', 'data'],
                ]
            );
        } else {
            $this->set(
                [
                    'success' => false,
                    'data' => $data,
                    '_serialize' => ['success', 'data'],
                ]
            );
            // die;
        }
    }

}
