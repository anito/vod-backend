<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Utility\Text;

class UploadComponent extends Component
{

    public $components = ['File', 'Director'];

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

    public function saveAs($path, $files)
    {

        define('PATH', $path);
        return $this->saveUploadedFiles($files);

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
            $file_name = $file->getClientFilename();

            if (!defined('PATH')) {
                return;
            }

            $isImage = $this->File->isImage($file_name);
            $isAvatar = $isImage && strpos(PATH, 'avatar');

            if (!is_dir(PATH)) {
                $this->File->makeDir(PATH);
            }
            $path = PATH . DS . $uuid;

            if ($file->getStream()) {

                $file_name = str_replace(' ', '_', $file_name);
                $file_name = preg_replace('/[^A-Za-z0-9._-]/', '_', $file_name);
                $lg_path = $path . DS . 'lg' . DS . $file_name;
                $file_name = $this->File->patchFilename($lg_path);
                $lg_path = $path . DS . 'lg' . DS . $file_name;
                $lg_temp = $lg_path . '.tmp';

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

                $_file['id'] = $uuid;
                $_file['src'] = $file_name;
                $_file['filesize'] = filesize($lg_path);

                // append to array
                $_files[] = $_file;
            } // if
        } // foreach
        return $_files;
    }

}
