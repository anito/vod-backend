<?php

namespace App\Controller\V1;

use Cake\Cache\Cache;
use Cake\Log\Log;
use Exception;

class NonExistentFileException extends \RuntimeException {}

class KodaksController extends AppController
{

  public function initialize(): void
  {
    parent::initialize();
    $this->Authentication->allowUnauthenticated(['process']);

    define('USE_X_SEND', false);
    Cache::disable();

    $this->loadComponent('File');
    $this->loadComponent('Salt');
    $this->loadComponent('Director');
  }

  private function n($a, $index, $default = null)
  {
    $var = $this->isArrayAt($a, $index);
    $var = trim($var);
    if (is_numeric($var)) {
      return $var;
    } else if (isset($default)) {
      return $default;
    } else {
      exit;
    }
  }

  private function isArrayAt($a, $index)
  {
    if (!isset($a[$index])) {
      throw new Exception(__('No Index Found Error'));
    }
    return $a[$index];
  }

  public function process()
  {

    $val = $this->getRequest()->getParam('crypt');
    $isVideo = false;

    if (strpos($val, 'http://') !== false || substr($val, 0, 1) == '/') {
      header('Location: ' . $val);
      exit;
    } else {
      $val = str_replace(' ', '.2B', $val);
    }

    $val = str_replace(' ', '.2B', $val);
    $decrypt = $this->Salt->decrypt($val); // decrypt

    // Log::debug('{decrypt}', compact('decrypt'));

    $a = explode(',', $decrypt);

    $file = $fn = basename($a[0]);

    // Make sure supplied filename contains only approved chars
    if (preg_match("/[^A-Za-z0-9._-]/", $file)) {
      header('HTTP/1.1 403 Forbidden');
      exit;
    }

    $id     = $this->isArrayAt($a, 1);
    $w      = $this->n($a, 2);
    $h      = $this->n($a, 3);
    $sq     = $this->n($a, 4, 2);
    $q      = $this->n($a, 5, 75);
    $sh     = $this->n($a, 6, 0);
    $x      = $this->n($a, 7, 50);
    $y      = $this->n($a, 8, 50);
    $force  = $this->n($a, 9, 0);

    $type   = $this->isArrayAt($a, 10);
    $ext    = $this->File->returnExt($file);
    $path = $this->Director->getMediaBasePath($type);
    $large = $path . DS . $id . DS . 'lg' . DS . $file;

    if ($this->File->isVideo($file)) {
      // Get the original file
      $sq = 2;
      $isVideo = true;
    }

    if ($sq == 2) {
      $base_dir = $path . DS . $id . DS . 'lg';
      $path_to_file = $large;
    } else {
      $fn .= "_{$w}_{$h}_{$sq}_{$q}_{$sh}_{$x}_{$y}";
      $fn .= ".$ext";
      $base_dir = $path . DS . $id . DS . 'cache';
      $path_to_file = $base_dir . DS . $fn;
    }

    // Make sure dirname of the cached copy is sane
    if (dirname($path_to_file) !== $base_dir) {
      header('HTTP/1.1 403 Forbidden');
      exit;
    }

    $noob = false;

    if (!file_exists($path_to_file)) {
      $noob = true;
      if ($sq == 2) {
        if ($path_to_file === false || !is_file($path_to_file)) {
          throw new NonExistentFileException(
            $path_to_file . ' does not exist or is not a file'
          );
        }

        copy($large, $path_to_file);
      } else {
        if (!defined('MAGICK_PATH')) {
          if (!defined('MAGICK_PATH_FINAL')) {
            define('MAGICK_PATH_FINAL', 'convert');
          }
        } else if (strpos(strtolower(MAGICK_PATH), 'c:\\') !== false) {
          define('MAGICK_PATH_FINAL', '"' . MAGICK_PATH . '"');
        } else {
          define('MAGICK_PATH_FINAL', MAGICK_PATH);
        }
        if (!defined('FORCE_GD')) {
          define('FORCE_GD', 0);
        }
        if (!is_dir(dirname($path_to_file))) {
          $parent_perms = substr(sprintf('%o', fileperms(dirname(dirname($path_to_file)))), -4);
          $old = umask(0);
          mkdir(dirname($path_to_file), octdec($parent_perms));
          umask($old);
        }

        $this->loadComponent('Darkroom');
        $this->Darkroom->develop($large, $path_to_file, $w, $h, $sq, $q, $x, $y, $force);
      }
    }

    $mtime = filemtime($path_to_file);
    $etag = md5($path_to_file . $mtime);
    $content_type = mime_content_type($path_to_file);

    if (!$noob) {
      if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && ($_SERVER['HTTP_IF_NONE_MATCH'] == $etag)) {
        header("HTTP/1.1 304 Not Modified");
        exit;
      }

      if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= filemtime($path_to_file))) {
        header("HTTP/1.1 304 Not Modified");
        exit;
      }
    }

    if (USE_X_SEND) {
      header("X-Sendfile: $path_to_file");
    } else {
      header('Cache-Control: public');
      header('Expires: ' . gmdate('D, d M Y H:i:s', strtotime('+1 year')));
      header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime));
      header('ETag: ' . $etag);
    }

    if ($isVideo) {
      $this->loadComponent('PartialFile');
      die($this->PartialFile->sendFile($path_to_file, $content_type));
    } else {
      header('Content-Length: ' . filesize($path_to_file));
      header('Content-Type: ' . $content_type);
      die(file_get_contents($path_to_file));
    }
  }
}
