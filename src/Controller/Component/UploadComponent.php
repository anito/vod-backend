<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Utility\Text;
use Cake\Log\Log;

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

    public function saveAvatar($files) {

        define('PATH', AVATARS);
        $this->saveUploadedFiles($files);

    }
    
    public function saveUploadedFiles($files)
    {

        $_files = [];

        if (!is_dir(UPLOADS)) {
            $this->File->makeDir(UPLOADS);
        }

        foreach ($files as $file) {

            $file_name = $file['name'];
            $isImage = $this->File->isImage($file_name);

            $uuid = Text::uuid();

            if(!defined('PATH')) {
                define('PATH', $this->Director->getPathConstant($file_name));
            }

            if (!defined('PATH')) {
                return;
            }

            if (!is_dir(PATH)) {
                $this->File->makeDir(PATH);
            }
            $path = PATH . DS . $uuid;

            $file_temp = $file['tmp_name'];

            if (is_uploaded_file($file_temp)) {

                $file_name = str_replace(' ', '_', $file_name);
                $file_name = preg_replace('/[^A-Za-z0-9._-]/', '_', $file_name);
                $lg_path = $path . DS . 'lg' . DS . $file_name;
                $file_name = $this->File->patchFilename($lg_path);
                $lg_path = $path . DS . 'lg' . DS . $file_name;
                $lg_temp = $lg_path . '.tmp';

                if (
                    $this->File->makeDir($path) &&
                    $this->File->setFolderPerms($path) &&
                    move_uploaded_file($file_temp, $lg_temp)
                ) {

                    copy($lg_temp, $lg_path);
                    unlink($lg_temp);

                    if ($isImage) {

                        list($meta, $captured) = $this->File->imageMetadata($lg_path);

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

                    }

                    $file['id'] = $uuid;
                    $file['src'] = $file_name;
                    $file['filesize'] = filesize($lg_path);
                }
                // append to array
                $_files[] = $file;
            } // if
        } // foreach
        return $_files;
    }

}
