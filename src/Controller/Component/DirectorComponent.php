<?php
namespace App\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Component;
use Cake\Utility\Security;
use Cake\Event\Event;
use Cake\Log\Log;


class DirectorComponent extends Component
{

    public $components = ['Salt'];

    function startup(Event $event) {

        /** @var \Cake\Controller\Controller $controller */
        $controller = $event->getSubject();
        $request = $controller->getRequest();

    }

    public function initialize(array $config) {
        // Execute any other additional setup
    }

    public function _p( $options ) {

        $defaults = array(
            'fn' => '',
            'id' => null,
            'width' => 176,
            'height' => 132,
            'square' => 2, // 1 => new Size ; 2 => original Size, 3 => aspect ratio
            'quality' => 80,
            'sharpening' => 1,
            'anchor_x' => 50,
            'anchor_y' => 50,
            'force' => false
        );

        $o = array_merge( $defaults, $options );
        $args = join( ',', $o );

        $crypt = $this->Salt->convert( $args ); //encode
        // Log::write( 'debug', $crypt );

        $path = UPLOADS . DS . $o['id'] . DS . 'lg' . DS . $o['fn'];
        $m = filemtime( $path );
        $x = pathinfo( $path, PATHINFO_EXTENSION );

        $timestamp = date('Ymd:His');
        return BASE_URL . '/api/q/' . $crypt . '/' . $timestamp . '_' . $m . '.' . $x;

    }

    public function computeSize($file, $new_w, $new_h, $scale) {
        $dims = getimagesize($file);
        $old_x = $dims[0];
        $old_y = $dims[1];
        $original_aspect = $old_x/$old_y;
        $new_aspect = $new_w/$new_h;
        if ($scale == 2) {
            $x = $old_x;
            $y = $old_y;
        } else if ($scale == 1) {
            $x = $new_w;
            $y = $new_h;
        } else {
            if ($original_aspect >= $new_aspect) {
                if ($new_w > $old_x) {
                    $x = $old_x;
                    $y = $old_y;
                }
                $x = $new_w;
                $y = ($new_w*$old_y)/$old_x;
            } else {
                if ($new_h > $old_y) {
                    $x = $old_x;
                    $y = $old_y;
                }
                $x = ($new_h*$old_x)/$old_y;
                $y = $new_h;
            }
        }
        return array($x, $y);
    }
}