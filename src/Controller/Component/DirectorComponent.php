<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\Entity;

class DirectorComponent extends Component
{

  protected array $components = ['Salt', 'File', 'Upload'];

  public function startup(\Cake\Event\EventInterface $event)
  {

    /** @var \Cake\Controller\Controller $controller */
    $controller = $event->getSubject();
    $request = $controller->getRequest();
  }

  public function initialize(array $config): void
  {
    // Execute any other additional setup
  }

  public function p($options)
  {

    $defaults = array(
      'fn' => '',
      'id' => null,
      'width' => 1024,
      'height' => 576,
      'square' => 1, // 0 => intelligent resize (keep ratio) | 1 => force resize | 2 => no resize (original)
      'quality' => 80,
      'sharpening' => 1,
      'anchor_x' => 50,
      'anchor_y' => 50,
      'force' => false,
    );

    $params = array_merge($defaults, $options);
    $args = join(',', $params);
    $crypt = $this->Salt->encrypt($args); //encrypt
    $path = $this->Upload->getPath();
    $path = $path . DS . $params['id'] . DS . 'lg' . DS . $params['fn'];
    $m = filemtime($path);
    $x = pathinfo($path, PATHINFO_EXTENSION);

    $timestamp = date('Ymd:His');
    return BASE_URL . API_PATH . '/q/' . $crypt . '/' . $timestamp . '_' . $m . '.' . $x;
  }

  public function computeSize($file, $new_w, $new_h, $scale)
  {
    $dims = getimagesize($file);
    $old_x = $dims[0];
    $old_y = $dims[1];
    $original_aspect = $old_x / $old_y;
    $new_aspect = $new_w / $new_h;
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
        $y = ($new_w * $old_y) / $old_x;
      } else {
        if ($new_h > $old_y) {
          $x = $old_x;
          $y = $old_y;
        }
        $x = ($new_h * $old_x) / $old_y;
        $y = $new_h;
      }
    }
    return array($x, $y);
  }

  public function ffmpeg()
  {
    if (function_exists('exec') && (DS == '/' || (DS == '\\' && FFMPEG_PATH_FINAL != 'ffmpeg'))) {
      exec(FFMPEG_PATH_FINAL . ' -version  2>&1', $out);
      if (empty($out)) {
        return false;
      } else {
        if (strpos($out[0], 'FFmpeg') !== false) {
          return true;
        } else {
          return false;
        }
      }
    }
    return false;
  }

  public function getDuration($path)
  {
    $info = pathinfo($path);
    $ext = $info['extension'];
    exec(FFMPEG_PATH_FINAL . " -i $path 2>&1", $out);

    $duration = null;
    foreach ($out as $line) {
      if (strpos($line, 'Duration') !== false) {
        preg_match('/Duration: ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $line, $matches);
        list(, $h, $m, $s) = $matches;
        $duration = ($h * 60 * 60) + ($m * 60) + $s;
        continue;
      }
    }
    return $duration;
  }

  public function getMediaBasePath($path)
  {
    return UPLOADS . DS . $path;
  }
}
