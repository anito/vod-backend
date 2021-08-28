<?php

namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\App;
use Cake\Event\Event;

/**
 * Images Controller
 *
 *
 * @method \App\Model\Entity\Video[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class ImagesController extends AppController
{

    public function initialize(): void
    {
        parent::initialize();
        $this->Auth->allow();
        $this->loadComponent('File');
        $this->loadComponent('Director');
        $this->loadComponent('Upload');

        $this->loadComponent('Crud.Crud', [
            'actions' => [
                'index' => [
                    'className' => 'Crud.Index',
                    'relatedModels' => true,
                ],
                'Crud.View',
                'Crud.Add',
                'Crud.Edit',
                'Crud.Delete',
            ],
            'listeners' => [
                'Crud.Api',
                'Crud.ApiPagination',
            ],
        ]);

        $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');

    }

    public function add()
    {

        if (!empty($files = $this->request->getData('Files'))) {

            // make shure single uploads are handled correctly
            if (!is_array($files)) {
                $files = [$files];
            }

            if (!empty($images = $this->Upload->saveAs(IMAGES, $files))) {

                $images = $this->Images->newEntities($images);

                if ($data = $this->Images->saveMany($images)) {
                    $this->set([
                        'success' => true,
                        'data' => $data,
                        'message' => __('Image saved'),
                        '_serialize' => ['success', 'data', 'message'],
                    ]);
                } else {
                    $this->set([
                        'success' => false,
                        'message' => __('An error occurred saving your image data'),
                        '_serialize' => ['success', 'message'],
                    ]);
                }
            } else {
                $this->set([
                    'success' => false,
                    'message' => __('An Error occurred while uploading your files'),
                    '_serialize' => ['success', 'message'],
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

                    $this->set([
                        'message' => __('Image could not be deleted'),
                    ]);
                } else {
                    $this->File->rmdirr($path);
                }
                $this->Crud->action()->serialize(['message']);
            }
        });

        $this->Crud->on('afterDelete', function (Event $event) {

            if ($event->getSubject()->success) {
                $this->set([
                    'message' => __('Image deleted'),
                ]);
            }
            $this->Crud->action()->serialize(['message']);
        });

        return $this->Crud->execute();
    }

    public function uri($id)
    {
        $data = [];

        $params = $this->getRequest()->getQuery();
        $lg_path = IMAGES . DS . $id . DS . 'lg';
        $files = glob($lg_path . DS . '*.*');

        if (!empty($files)) {
            $fn = basename($files[0]);
            $type = "images";

            $options = array_merge(compact(array('fn', 'id', 'type')), $params);
            $p = $this->Director->p($options);
            $json = json_encode($params);
            $stringified = preg_replace('/["\'\s]/', '', $json);
            $data = array(
                'id' => $id,
                'url' => $p,
                'params' => $stringified,
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
