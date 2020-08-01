<?php

/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\App;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Cache\Cache;
use Cake\I18n\Time;
use Cake\Log\Log;

class UploadsController extends AppController {

    function initialize() {
        parent::initialize();
        Cache::disable();
        $this->autoRender = false;
        $this->loadModel('Videos');
        $this->loadComponent( 'File' );
    }

    function _beforeRender() {

    }

    public function file() {
        $user = $this->Auth->identify();

        if (!$user) {
            throw new UnauthorizedException(__('Invalid username or password'));
        }

        $user_id = $user['sub']['id'];

        if (!is_dir( UPLOADS )) {
            $this->File->makeDir( UPLOADS );
        }
        if ( !is_dir( UPLOADS . DS . $user_id ) ) {
            $this->File->makeDir( UPLOADS . DS . $user_id );
        }

        if (!$this->request->is('post')) {
            exit;
        }

        Log::write('debug', $this->Videos);
        Log::write('debug', $this->request->getData());

        if ( !empty( $files = $this->request->getData( 'Video' ) ) ) {

            $videos = array();

            foreach ( $files as $file ) {

                $file_name = str_replace( ' ', '_', $file['name'] );
                $file_name = preg_replace( '/[^A-Za-z0-9._-]/', '_', $file_name );
                $file_temp = $file['tmp_name'];

                $ext = $this->File->returnExt( $file_name );

                if (in_array($ext, array('jpg', 'jpeg', 'jpe', 'gif', 'png'))) {

                    if ( is_uploaded_file( $file_temp ) ) {

                        $path = UPLOADS . DS . $user_id;
                        $lg_path = $path . DS . 'lg' . DS . $file_name;
                        $lg_temp = $lg_path . '.tmp';

                        Log::write( 'debug', $lg_temp );

                        if( $this->File->makeDir( $path ) &&
                            $this->File->setFolderPerms( $user_id ) &&
                            move_uploaded_file( $file_temp, $lg_temp )
                        ) {

                            copy( $lg_temp, $lg_path );
                            unlink( $lg_temp );

                            list( $meta, $captured ) = $this->File->imageMetadata( $lg_path );

                            Log::write( 'debug', $meta );

                            $file['exposure'] = $this->File->parseMetaTags('exif:exposure', $meta);
                            $file['iso'] = $this->File->parseMetaTags('exif:iso', $meta);
                            $file['longitude'] = $this->File->parseMetaTags('exif:longitude', $meta);
                            $file['aperture'] = $this->File->parseMetaTags('exif:aperture', $meta);
                            $file['model'] = $this->File->parseMetaTags('exif:model', $meta);
                            $file['date'] = $this->File->parseMetaTags('exif:date time', $meta);
                            $file['title'] = $this->File->parseMetaTags('exif:title', $meta);
                            $file['bias'] = $this->File->parseMetaTags('exif:exposure bias', $meta);
                            $file['metering'] = $this->File->parseMetaTags('exif:metering mode', $meta);
                            $file['focal'] = $this->File->parseMetaTags('exif:focal length', $meta);
                            $file['software'] = $this->File->parseMetaTags('exif:software', $meta);

                            $file['user_id'] = $user_id;
                            $file['src'] = $file_name;
                            $file['filesize'] = filesize( $lg_path );
                            $file['order'] = -1;

                        }
                    }
                }
                // append to array
                $videos[] = $file;
                // $videos[] = $this->request->getData();
            }// foreach

            $videos = $this->Videos->newEntities( $videos );

            if( $this->Videos->saveMany( $videos ) ) {
                $this->set( '_serialize', $videos );
                $this->render( SIMPLE_JSON );
            }
        }
    }
}