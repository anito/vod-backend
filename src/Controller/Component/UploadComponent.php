<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Utility\Text;

class UploadComponent extends Component
{

  protected array $components = ['File', 'Director'];

  protected $path;

  protected $type;

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

  public function initialize(array $config): void
  {
    $this->type = $config['type'];
    $this->path = UPLOADS . DS . $this->type;
  }

  public function save($files)
  {

    return $this->saveUploadedFiles($files);
  }

  public function getPath(): string
  {

    return $this->path;
  }

  public function getType(): string
  {

    return $this->type;
  }

  protected function saveUploadedFiles($files)
  {

    $_files = [];

    if (!is_dir(UPLOADS)) {
      $this->File->makeDir(UPLOADS);
    }

    foreach ($files as $file) {

      $_file = [];
      $uuid = Text::uuid();
      $fn = $file->getClientFilename();

      $isImage = $this->File->isImage($fn);
      $isAvatar = $isImage && strpos($this->path, 'avatar');
      $isVideo = $this->File->isVideo($fn);

      if (!is_dir($this->path)) {
        $this->File->makeDir($this->path);
      }
      $path = $this->path . DS . $uuid;

      if ($file->getStream()) {

        $fn = str_replace(' ', '_', $fn);
        $fn = preg_replace('/[^A-Za-z0-9._-]/', '_', $fn);
        $lg_path = $path . DS . 'lg' . DS . $fn;
        $fn = $this->File->patchFilename($lg_path);
        $lg_path = $path . DS . 'lg' . DS . $fn;
        $lg_temp = $lg_path . '.tmp';
        $dir = dirname($lg_path);

        $this->File->makeDir($path);
        $this->File->setFolderPerms($path);
        $file->moveTo($lg_temp);

        copy($lg_temp, $lg_path);
        unlink($lg_temp);

        if ($isImage && !$isAvatar) {

          list($meta, $captured) = $this->File->imageMetadata($lg_path);

          $_file['exposure'] = $this->File->parseMetaTags('exif:exposure', $meta);
          $_file['iso'] = $this->File->parseMetaTags('exif:iso', $meta);
          $_file['longitude'] = $this->File->parseMetaTags('exif:longitude', $meta);
          $_file['aperture'] = $this->File->parseMetaTags('exif:aperture', $meta);
          $_file['model'] = $this->File->parseMetaTags('exif:model', $meta);
          $_file['date'] = $this->File->parseMetaTags('exif:date time', $meta);
          $_file['title'] = $this->File->parseMetaTags('exif:title', $meta);
          $_file['bias'] = $this->File->parseMetaTags('exif:exposure bias', $meta);
          $_file['metering'] = $this->File->parseMetaTags('exif:metering mode', $meta);
          $_file['focal'] = $this->File->parseMetaTags('exif:focal length', $meta);
          $_file['software'] = $this->File->parseMetaTags('exif:software', $meta);
        }

        if ($isVideo) {
          $ffmpeg = $this->Director->ffmpeg();

          if ($ffmpeg) {
            $info = pathinfo($lg_path);
            $ext = $info['extension'];
            $duration = $this->Director->getDuration($lg_path);

            $_file['duration'] = $duration;

            // $duration = $duration - 2;
            // $bits = ceil($duration / 12);
            // if ($bits == 0) {
            //   $bits = 1;
            // }
            // $rate = 1 / $bits;
            // if ($rate < 0.1) {
            //   $rate = 0.1;
            // }

            // $dir = dirname($lg_path);

            // $i = 1;
            // $cmd = array();
            // while ($i < $duration) {
            //   $i_str = str_pad($i, 5, '0', STR_PAD_LEFT);
            //   $cmd[] = FFMPEG_PATH_FINAL . " -ss $i -r 1 -i $lg_path -vframes 1 -an -f mjpeg $dir/__vidtn__{$uuid}_{$i_str}.jpg";
            //   $i += $bits;
            // }

            // $cmd = join(' && ', $cmd);
            // exec($cmd);
          }
        }

        $_file['id'] = $uuid;
        $_file['src'] = $fn;
        $_file['filesize'] = filesize($lg_path);

        // append to array
        $_files[] = $_file;
      } // if
    } // foreach
    return $_files;
  }
}
