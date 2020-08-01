<?php
namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Core\App;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Security;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Text;
use Cake\I18n\Time;
use Cake\Log\Log;

/**
 * Videos Controller
 *
 *
 * @method \App\Model\Entity\Video[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class VideosController extends AppController
{

    public function initialize() {
        parent::initialize();
        $this->Auth->allow([]);
        $this->loadComponent( 'File' );
        $this->loadComponent( 'Salt' );
        $this->loadComponent( 'Director' );

    }

    function _beforeRender() {

    }

    public function add() {

        $user = $this->Auth->identify();

        if (!$user) {
            throw new UnauthorizedException(__('Invalid username or password'));
        }

        if ( !empty( $files = $this->request->getData( 'Video' ) ) ) {

            if( ! empty( $videos = $this->_saveUploadedFiles( $files ) ) ) {

                $videos = $this->Videos->newEntities( $videos );

                // Log::write('debug', $videos);

                if( $data = $this->Videos->saveMany( $videos ) ) {

                    $this->set([
                        'success' => true,
                        'data' => $data,
                        '_serialize' => ['success', 'data']
                    ]);
                } else {

                    $this->set([
                        'success' => false,
                        'data' => [],
                        '_serialize' => ['success', 'data']
                    ]);

                }
            } else {

                $this->set([
                    'success' => false,
                    'data' => [],
                    'message' => 'Could not save',
                    '_serialize' => ['success', 'data', 'message']
                ]);
            }
        }
    }

    public function delete() {
        $this->Crud->on( 'beforeDelete', function( Event $event ) {

            if( $user = $this->Auth->identify() ) {

                $id = $event->getSubject()->entity->id;
                $fn = $event->getSubject()->entity->src;

                $path = UPLOADS . DS . $id;
                $lg_path = $path . DS . 'lg';

                $oldies = glob($lg_path . DS . $fn);

                if( ! empty( $oldies ) && $oldies && ! unlink($oldies[0]) ) {
                    $event->stopPropagation();
                } else {
                    $this->File->rmdirr( $path );
                }

            }



        } );
        return $this->Crud->execute();
    }

    public function uri( $id ) {

        $data = [];
        $video = $this->Videos->get( $id );
        $fn = $video->get( 'src' );
        $lg_path = UPLOADS . DS . $id . DS . 'lg';
        $files = glob($lg_path . DS . '*.*');
        $fn = basename($files[0]);

        if( ! empty( $files[0] ) ) {
            $options = compact( array('fn', 'id') );
            $data = array( 'id' => $id, 'url' => $this->Director->_p( $options ) );
        } else {
            die;
        }
        $this->set(
            [
                'success' => true,
                'data' => $data,
                '_serialize' => ['success', 'data']
            ]
        );
    }

    private function _saveUploadedFiles( $files ) {

        $videos = [];
        $base = UPLOADS;

        if (!is_dir( UPLOADS )) {
            $this->File->makeDir( UPLOADS );
        }

        foreach ( $files as $file ) {

            $file_name = $file['name'];
            if ( $this->File->isImage( $file_name ) || $this->File->isVideo( $file_name ) ) { // 'accept_file_types' => '/\.(gif|jpe?g|png)$/i',

                $uuid = Text::uuid();
                $path = $base . DS . $uuid;

                $file_temp = $file['tmp_name'];

                if ( is_uploaded_file( $file_temp ) ) {

                    $file_name = str_replace( ' ', '_', $file_name );
                    $file_name = preg_replace( '/[^A-Za-z0-9._-]/', '_', $file_name );
                    $lg_path = $path . DS . 'lg' . DS . $file_name;
                    $file_name = $this->File->patchFilename( $lg_path );
                    $lg_path = $path . DS . 'lg' . DS . $file_name;
                    $lg_temp = $lg_path . '.tmp';

                    // Log::write( 'debug', $lg_temp );
                    // Log::write( 'debug', $uuid );

                    if( $this->File->makeDir( $path ) &&
                        $this->File->setFolderPerms( $path ) &&
                        move_uploaded_file( $file_temp, $lg_temp )
                    ) {

                        copy( $lg_temp, $lg_path );
                        unlink( $lg_temp );

                        list( $meta, $captured ) = $this->File->imageMetadata( $lg_path );

                        // Log::write( 'debug', $meta );

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

                        $file['id'] = $uuid;
                        $file['src'] = $file_name;
                        $file['filesize'] = filesize( $lg_path );
                        $file['order'] = -1;

                    }
                    // append to array
                    $videos[] = $file;
                }//if
            }// if
        }// foreach
        return $videos;
    }

    private function _p( $options ) {

        $defaults = array(
            'src' => '',
            'id' => null,
            'width' => 176,
            'height' => 132,
            'square' => 1, // 1 => new Size ; 2 => original Size, 3 => aspect ratio
            'quality' => 80,
            'sharpening' => 1,
            'anchor_x' => 50,
            'anchor_y' => 50,
            'force' => false
        );

        $o = array_merge( $defaults, $options );
        $args = join( ',', array( $o['id'], $o['fn'] ) );
        $args_ = join( ',', $o );

        $crypt = $this->Salt->convert( $args ); //encode
        Log::write( 'debug', $crypt );

        $path = UPLOADS . DS . $o['id'] . DS . 'lg' . DS . $o['fn'];
        $m = filemtime( $path );
        $x = pathinfo( $path, PATHINFO_EXTENSION );

        $timestamp = date('Ymd:His');
        return 'q/' . $crypt . '/' . $timestamp . '_' . $m . '.' . $x;

    }

}
