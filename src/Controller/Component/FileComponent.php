<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Log\Log;

class FileComponent extends Component {

  /**
   * Constructor
   *
   * @param \Cake\Controller\ComponentRegistry $registry A ComponentRegistry for this component
   * @param array $config Array of config.
   */
  public function __construct(ComponentRegistry $registry, array $config = [])
  {
      parent::__construct($registry, $config);
  }

  var $dirTags = array(
      'image filename' => 'Image: filename', 'album title' => 'Album: title', 'album id' => 'Album: id', 'album tags' => 'Album: tags', 'date captured' => 'Image: date captured', 'date uploaded' => 'Image: date uploaded', 'image number' => 'Image: number', 'image count' => 'Album: content count', 'tags' => 'Image: tags', 'place taken' => 'Album: place taken', 'date taken' => 'Album: date taken', 'contributor username' => 'Contributor: username', 'contributor email' => 'Contributor: email', 'contributor first name' => 'Contributor: first name', 'contributor last name' => 'Contributor: last name', 'contributor display name' => 'Contributor: display name'
  );
  var $smartTags = array(
      'original album title' => 'Original album: title', 'original album id' => 'Original album: id',
      'original album tags' => 'Original album: tags'
  );
  var $iptcTags = array(
      'credit', 'caption', 'copyright', 'title', 'category', 'keywords',
      'byline', 'byline title', 'city', 'state', 'country', 'headline',
      'source', 'contact'
  );
  var $exifTags = array(
      'make', 'model', 'exposure', 'exposure mode', 'iso', 'aperture',
      'focal length', 'flash simple', 'flash', 'exposure bias', 'metering mode',
      'white balance', 'title', 'comment', 'latitude', 'longitude'
  );

  function uploadLimit() {
    $max_upload = ini_get('upload_max_filesize');
    $post_max = ini_get('post_max_size');

    $max_upload_n = explode('m', strtolower($max_upload));
    $max_upload_n = $max_upload_n[0];

    $post_max_n = explode('m', strtolower($post_max));
    $post_max_n = $post_max_n[0];

    $max = $max_upload;
    $post_max_broken = false;

    if ($post_max_n < $max_upload_n) {
      $max = $post_max;
      $post_max_broken = true;
    }
    return array($max, $post_max_broken);
  }

  ////
  // Generate random string
  ////
  function randomStr($len = 6) {
    return substr(md5(uniqid(microtime())), 0, $len);
  }

  ////
  // Central directory creation logic
  // Creates a directory if it does not exits
  ////
  function makeDir($dir) {
    if (!is_dir($dir)) {
      $parent_perms = $this->getPerms(dirname($dir));
      $f = new Folder();
      if ($f->create($dir, octdec($parent_perms))) {
        return true;
      } else if ($parent_perms == '0755') {
        if ($f->chmod($dir, 0777) && $f->create($dir)) {
          return true;
        }
      }
      return false;
    } else {
      return true;
    }
  }

  function getPerms($dir) {
    return substr(sprintf('%o', fileperms($dir)), -4);
  }

  ////
  // Set permissions on a directory
  ////
  function setPerms($dir) {
    if (!is_dir($dir)) {
      return $this->makeDir($dir);
    } elseif (is_writable($dir)) {
      return true;
    } else {
      $test_file = ($dir . DS . '___test___');
      $f = @fopen($test_file, 'a');
      if ($f === false) {
        $fd = new Folder();
        return $fd->chmod($dir, 0777);
      } else {
        fclose($f);
        @unlink($test_file);
        return true;
      }
    }
  }

  ////
  // Create image subdirectories
  ////
  function setFolderPerms( $path ) {
    $cache = $path . DS . 'cache';
    $lg = $path . DS . 'lg';

    if ($this->setPerms($lg) && $this->setPerms($cache)) {
      return true;
    } else {
      return false;
    }
  }

  ////
  // Process permissions for album subdirectories
  ////
  function createFolderDirs($base, $id) {
    $path = $base . DS . $id;
    $lg = $path . DS . 'lg';
    $cache = $path . DS . 'cache';

    if ($this->makeDir($lg) && $this->makeDir($cache)) {
      return true;
    } else {
      return false;
    }
  }

  ////
  // Search a directory for a filename using a regular expression
  // Found in PHP docs: http://us3.php.net/manual/en/function.file-exists.php#64908
  ////

  function regExpSearch( $regExp, $dir ) {
    $open = opendir($dir);
    $files = array();
    while (($file = readdir($open)) !== false) {
      if ( preg_match( $regExp, $file ) ) {
        $files[] = $file;
      }
    }
    return $files;
  }

  ////
  // Grab the extension of of any file
  ////
  function returnExt($file, $raw = false) {
    $pos = strrpos($file, '.');
    $ext = substr($file, $pos + 1, strlen($file));
    if ($raw) {
      return $ext;
    } else {
      return strtolower($ext);
    }
  }

  ////
  // Grab all files in a directory
  ////
  function directory($dir, $filters = 'all') {
    if ($filters == 'accepted') {
      $filters = 'jpg,JPG,JPEG,jpeg,gif,GIF,png,PNG,swf,SWF,flv,FLV,f4v,F4V,mov,MOV,mp4,MP4,m4v,MV4,m4a,M4A,3gp,3GP,3g2,3G2';
    }
    $handle = opendir($dir);
    $files = array();
    if ($filters == "all"):
      while (($file = readdir($handle)) !== false):
        $files[] = $file;
      endwhile;
    endif;
    if ($filters != "all"):
      $filters = explode(",", $filters);
      while (($file = readdir($handle)) !== false):
        for ($f = 0; $f < sizeof($filters); $f++):
          $system = explode(".", $file);
          $count = count($system);
          if ($system[$count - 1] == $filters[$f]):
            $files[] = $file;
          endif;
        endfor;
      endwhile;
    endif;
    closedir($handle);
    return $files;
  }

  ////
  // Recursive Directory Removal
  ////
  function rmdirr( $dir ) {
    $f = new Folder($dir);
    $f->delete();
  }

  ////
  // Transform a string (e.g. 15MB) into an actual byte representation
  ////
  function returnBytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    switch ($last) {
      case 'g':
        $val *= 1024;
      case 'm':
        $val *= 1024;
      case 'k':
        $val *= 1024;
    }
    return $val;
  }

  function _date($format, $date, $tz = true) {
    setlocale(LC_TIME, explode(',', __('[#Set the locale to use for date translations. (http://php.net/setlocale) You can specify as many locales as you like and Director will use the first available from your list. Example: es_MX,es_ES,es_AR#]en_US', true)));
    if (strpos($date, '-') !== false) {
      $date = strtotime($date);
    }
    if ($tz) {
      $offset = $_COOKIE['dir_time_zone'];
      $date = $date + $offset;
    }
    return str_replace('  ', ' ', strftime($format, $date));
  }

  function parseMetaTags($template, $data, $empty = 'Unknown') {
    $bits = explode(':', $template);
    if ($bits[0] == 'iptc') {
      if (isset($data['IPTC'])) {
        $iptc = $data['IPTC'];
        switch ($template) {
          case 'iptc:credit':
            @$tag = $iptc['2#110'];
            break;
          case 'iptc:category':
            @$tag = $iptc['2#050'];
            break;
          case 'iptc:keywords':
            @$tag = $iptc['2#025'];
            if (is_array($tag)) {
              $tag = join(' ', $tag);
            }
            break;
          case 'iptc:byline':
            @$tag = $iptc['2#080'];
            if (is_array($tag)) {
              $tag = $tag[0];
            }
            if (strpos($tag, 'Picasa') !== false) {
              $tag = '';
            }
            break;
          case 'iptc:byline title':
            @$tag = $iptc['2#085'];
            break;
          case 'iptc:city':
            @$tag = $iptc['2#090'];
            break;
          case 'iptc:state':
            @$tag = $iptc['2#095'];
            break;
          case 'iptc:country':
            @$tag = $iptc['2#101'];
            break;
          case 'iptc:headline':
            @$tag = $iptc['2#105'];
            break;
          case 'iptc:title':
            @$tag = $iptc['2#005'];
            break;
          case 'iptc:source':
            @$tag = $iptc['2#115'];
            break;
          case 'iptc:copyright':
            @$tag = $iptc['2#116'];
            break;
          case 'iptc:contact':
            @$tag = $iptc['2#118'];
            break;
          case 'iptc:caption':
            @$tag = $iptc['2#120'];
            break;
        }
      }

      if (isset($tag)) {
        if (!empty($tag)) {
          if (is_array($tag)) {
            $tag = $tag[0];
          }
          if (function_exists('mb_detect_encoding')) {
            $encoding = mb_detect_encoding($tag);
          } else {
            $encoding = 'UTF-8';
          }
          if (is_string($tag)) {
            switch ($encoding) {
              case 'ASCII':
                // Nothing to do here, encoding will be handled
                // by api helper if needed
                break;
              default:
                if (is_callable('iconv')) {
                  $encoding = '';
                  # Weed out charsets
                  foreach (array('UTF-8', 'CP1250', 'MacRoman') as $enc) {
                    @$test = iconv($enc, $enc, $tag);
                    if (md5($test) == md5($tag)) {
                      $encoding = $enc;
                      break;
                    }
                  }
                  if ($encoding == 'CP1250' || $encoding == 'MacRoman') {
                    $tag = iconv($encoding, "UTF-8", $tag);
                  } else if (empty($encoding)) {
                    $tag = utf8_encode($tag);
                  }
                } else {
                  if (utf8_encode(utf8_decode($tag)) != $tag) {
                    $tag = utf8_encode($tag);
                  }
                }
                break;
            }
            return $tag;
          } else {
            return '';
          }
        } else {
          return $empty;
        }
      } else {
        return '';
      }
    } else {
      if (isset($data['Exif']['EXIF'])) {
        $exif = $data['Exif']['EXIF'];
        switch ($template) {
          case 'exif:date time':
            return @$data['Exif']['IFD0']['DateTime'];
            break;
          case 'exif:make':
            return @$data['Exif']['IFD0']['Make'];
            break;
          case 'exif:title':
            return @$data['Exif']['IFD0']['ImageDescription'];
            break;
          case 'exif:comment':
            return @$data['Exif']['COMPUTED']['UserComment'];
            break;
          case 'exif:model':
            return @$data['Exif']['IFD0']['Model'];
            break;
          case 'exif:software':
            return @$data['Exif']['IFD0']['Software'];
            break;
          case 'exif:exposure':
            return @$exif['ExposureTime'];
            break;
          case 'exif:iso':
            return @$exif['ISOSpeedRatings'];
            break;
          case 'exif:aperture':
            return @$this->exif_frac2dec($exif['FNumber']);
            break;
          case 'exif:focal length':
            return @$this->exif_frac2dec($exif['FocalLength']);
            break;
          case 'exif:exposure mode':
            if (isset($exif['ExposureMode'])) {
              switch ($exif['ExposureMode']) {
                case 0: return 'Easy shooting';
                  break;
                case 1: return 'Program';
                  break;
                case 2: return 'Tv-priority';
                  break;
                case 3: return 'Av-priority';
                  break;
                case 4: return 'Manual';
                  break;
                case 5: return 'A-DEP';
                  break;
                default: return 'Unknown';
                  break;
              }
            } else {
              return 'Unknown';
            }
            break;
          case 'exif:exposure bias':
            if (isset($exif['ExposureBiasValue'])) {
              list($n, $d) = explode('/', $exif['ExposureBiasValue']);
              if (!empty($n)) {
                return $exif['ExposureBiasValue'] . ' EV';
              } else {
                return '0 EV';
              }
              return $this->exif_frac2dec($exif['ExposureBiasValue']) . ' EV';
            } else {
              return 'Unknown';
            }
            break;
          case 'exif:metering mode':
            if (isset($exif['MeteringMode'])) {
              switch ($exif['MeteringMode']) {
                case 0: return 'Unknown';
                  break;
                case 1: return 'Average';
                  break;
                case 2: return 'Center Weighted Average';
                  break;
                case 3: return 'Spot';
                  break;
                case 4: return 'Multi-Spot';
                  break;
                case 5: return 'Multi-Segment';
                  break;
                case 6: return 'Partial';
                  break;
                case 255: return 'Other';
                  break;
              }
            } else {
              return 'Unknown';
            }
            break;
          case 'exif:white balance':
            if (isset($exif['WhiteBalance'])) {
              switch ($exif['WhiteBalance']) {
                case 0: return 'Auto';
                  break;
                case 1: return 'Sunny';
                  break;
                case 2: return 'Cloudy';
                  break;
                case 3: return 'Tungsten';
                  break;
                case 4: return 'Fluorescent';
                  break;
                case 5: return 'Flash';
                  break;
                case 6: return 'Custom';
                  break;
                case 129: return 'Manual';
                  break;
              }
            } else {
              return 'Unknown';
            }
            break;
          case 'exif:flash simple':
            if (isset($exif['Flash'])) {
              if (in_array($exif['Flash'], array(0, 16, 24, 32))) {
                return 'Flash did not fire';
              } else {
                return 'Flash fired';
              }
            } else {
              return 'Unknown';
            }
            break;
          case 'exif:latitude':
          case 'exif:longitude':
            if (isset($data['Exif']['GPS'])) {
              $gps = $data['Exif']['GPS'];
              $type = ucwords(array_pop(explode(':', $template)));
              return $this->gps_convert($gps["GPS{$type}"], $gps["GPS{$type}Ref"]);
            } else {
              return '';
            }
            break;
          case 'exif:flash':
            if (isset($exif['Flash'])) {
              switch ($exif['Flash']) {
                case 0: return 'No Flash';
                  break;
                case 1: return 'Flash';
                  break;
                case 5: return 'Flash, strobe return light not detected';
                  break;
                case 7: return 'Flash, strob return light detected';
                  break;
                case 9: return 'Compulsory Flash';
                  break;
                case 13: return 'Compulsory Flash, Return light not detected';
                  break;
                case 16: return 'No Flash';
                  break;
                case 24: return 'No Flash';
                  break;
                case 25: return 'Flash, Auto-Mode';
                  break;
                case 29: return 'Flash, Auto-Mode, Return light not detected';
                  break;
                case 31: return 'Flash, Auto-Mode, Return light detected';
                  break;
                case 32: return 'No Flash';
                  break;
                case 65: return 'Red Eye';
                  break;
                case 69: return 'Red Eye, Return light not detected';
                  break;
                case 71: return 'Red Eye, Return light detected';
                  break;
                case 73: return 'Red Eye, Compulsory Flash';
                  break;
                case 77: return 'Red Eye, Compulsory Flash, Return light not detected';
                  break;
                case 79: return 'Red Eye, Compulsory Flash, Return light detected';
                  break;
                case 89: return 'Red Eye, Auto-Mode';
                  break;
                case 93: return 'Red Eye, Auto-Mode, Return light not detected';
                  break;
                case 95: return 'Red Eye, Auto-Mode, Return light detected';
                  break;
                default: return 'Unknown';
                  break;
              }
            } else {
              return 'Unknown';
            }
            break;
        }
      }
    }
  }

  function exif_frac2dec($str) {
    @list( $n, $d ) = explode('/', $str);
    if (!empty($d))
      return $n / $d;
    return $str;
  }

  function imageMetadata($path) {
    $meta = array();
    $captured = null;
    $meta_s = null;

    if (!$this->isImage(basename($path))) {
      $meta = array();
      if (is_callable('iptcparse')) {
        getimagesize($path, $info);
        if (!empty($info['APP13'])) {
          $meta['IPTC'] = iptcparse($info['APP13']);
        }
        if (!empty($iptc['2#055'][0]) && !empty($iptc['2#060'][0])) {
          $captured = strtotime($iptc['2#055'][0] . ' ' . $iptc['2#060'][0]);
        }
      }

      if ( preg_match('/\.(jpg|jpeg)$/', basename( $path )) && is_callable( 'exif_read_data' ) ) {
        $exif_data = exif_read_data($path, 0, true);
        $meta['Exif'] = $exif_data;
        if (isset($meta['Exif']['EXIF']['DateTimeDigitized'])) {
          $dig = $meta['Exif']['EXIF']['DateTimeDigitized'];
        } else if (isset($meta['Exif']['EXIF']['DateTimeOriginal'])) {
          $dig = $meta['Exif']['EXIF']['DateTimeOriginal'];
        }
        if (isset($dig)) {
          $bits = explode(' ', $dig);
          $captured = str_replace(':', '-', $bits[0]) . ' ' . $bits[1];
        }
      }
    }
    return array($meta, $captured);
  }

  function _divide($str) {
    $bits = explode('/', $str);
    $dec = $bits[0] / $bits[1];
    return $dec;
  }

  function gps_convert($arr, $quadrant) {
    $d = $this->_divide($arr[0]);
    $m = $this->_divide($arr[1]);
    $s = $this->_divide($arr[2]);
    $dec = ((($s / 60) + $m) / 60) + $d;
    if (strtolower($quadrant) == 's' || strtolower($quadrant) == 'w') {
      $dec = -$dec;
    }
    return $dec;
  }

  function isVideo($fn) {
    
    if ( preg_match('/\.(mov|mp4|m4a|m4v|3gp|3g2|webm)$/i', $fn) ) {
        return true;
    } else {
        return false;
    }
  }

  function isImage($fn) {
    if( preg_match('/\.(jpg|jpe|jpeg|gif|png|ico)$/i', $fn) ) {
        return true;
    } else {
        return false;
    }
  }

  function isSwf($fn) {
    if ( preg_match('/\.swf$/i', $fn) ) {
        return true;
    } else {
        return false;
    }
  }

  function patchFilename( $file ) {
    /**
     * @firstRun needed for files stored in the same folder
     * keeps the filename intact and appends "_{d}" on counterlike filenames like IMG_6453.jpg -> IMG_6453_1.jpg
     * avoids upscoring on counterlike filenames eg IMG_6453.jpg -> IMG_6454.jpg
     */
    $firstRun = true;
    while( file_exists( $file ) ) {
      $parts = pathinfo( $file );
      $dir = $parts['dirname'];
      $base = $parts['filename'];
      $ext = $parts['extension'];
      preg_match( '/^([A-Za-z0-9._-]+)_([0-9]+)$/', $base, $match );

      $base = ! empty( $match[1] ) && ! $firstRun ? $match[1] : $base;
      $next = ! empty( $match[2] ) && ! $firstRun ? intval( $match[2] )+1 : 1;
      $firstRun = false;

      $new_fn = "{$base}_{$next}.{$ext}";
      $file = $dir . DS . $new_fn;
    };
    return isset( $new_fn ) && $firstRun ? $new_fn : pathinfo( $file )['basename'];
  }
}

?>