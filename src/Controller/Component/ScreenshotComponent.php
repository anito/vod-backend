<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Http\Client\FormData;
use Cake\Log\Log;
use Cake\ORM\Entity;
use DateTime;
use Exception;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Clip;
use HeadlessChromium\Page;
use Laminas\Diactoros\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;

class ScreenshotComponent extends Component
{

  protected array $components = ['Upload', 'Director', 'File'];

  protected array $params;

  public function initialize(array $config): void
  {
    $queryParams = $this->getController()->getRequest()->getQueryParams();
    $payloadParams = (array) $this->getController()->getRequest()->getParsedBody();
    $this->params = array_merge($queryParams, $payloadParams);
  }

  /**
   * Take a screenshot from an url
   *
   * @version 4.0.0
   * @param string|null $url Url that will be made the screenshot from.
   * @return string
   */
  public function snap()
  {
    $url = array_key_exists('url', $this->params) ? $this->params['url'] : '';
    $format  = array_key_exists('f', $this->params) ? $this->validateFormat($this->params['f']) : $this->get_default_options('f');

    if (!$url) {
      throw 'No url provided';
    } elseif (!str_starts_with($url, 'http')) {
      $url = 'https://' . $url;
    }

    $dt     = new DateTime();
    $date   = $dt->format('Ymd');
    $time   = $dt->format('His');
    $uuid   = sprintf('%04x', random_int(0, 0x3fff) | 0x8000);
    $fn     = "capture_{$date}-{$time}_{$uuid}.{$format}";
    $path   = rtrim(sys_get_temp_dir(), '/\\') . DS . $fn;

    $browser = (new BrowserFactory())->createBrowser([
      'noSandbox' => true,
      'ignoreCertificateErrors' => true,
      'debugLogger'     => Configure::read('Chrome.debug') ? LOGS . 'chrome-debug.log' : false,
      'customFlags' => [
        '--disable-gpu',
      ]
    ]);

    $default_capture_size = $this->get_default_options(['x', 'y', 'w', 'h', 'vw', 'vh']);
    $capture_size = (object) array_map('intval', array_merge($default_capture_size, array_intersect_key($this->params, $default_capture_size)));
    $this->validateCaptureSize($capture_size);

    $x  = $capture_size->x;
    $y  = $capture_size->y;
    $w  = $capture_size->w;
    $h  = $capture_size->h;
    $vw = $capture_size->vw;
    $vh = $capture_size->vh;

    $s  = array_key_exists('s', $this->params) ? (float) $this->params['s'] : $this->get_default_options('s');
    $q  = array_key_exists('q', $this->params) ? (int) $this->validateQuality($this->params['q']) : $this->get_default_options('q');

    $screenshotOptions = [
      'clip' => new Clip($x, $y, $w, $h, $s),
      'format' => $format,
      'quality' => $q
    ];

    // Quality is only allowed on 'jpg' and 'webp' format
    if (!in_array($format, ['jpeg', 'webp'])) {
      unset($screenshotOptions['quality']);
    }

    $page = $browser->createPage();
    $page->setViewport($vw, $vh);

    try {
      $page->navigate($url)->waitForNavigation(Page::INTERACTIVE_TIME, 120000);

      $screenshot = $page->screenshot($screenshotOptions);
      $screenshot->saveToFile($path);

      Log::debug("Screenshot successfully saved to $path");
    } catch (Exception $e) {
      $path = $e;
      $message = $e->getMessage();

      Log::debug("Screenshot failed: $message");
    } finally {
      if (isset($browser)) {
        $browser->close();
      }
    }
    return $path;
  }

  private static function get_default_options($filter = [])
  {
    $vw = 1200;
    $vh = 1600;
    $x = 0;
    $y = 0;
    $s = 0.5;
    $q = 80;
    $f = 'png';

    $_filter = !is_array($filter) ? array($filter) : $filter;

    $filtered = array_intersect_key([
      'x' => $x,
      'y' => $y,
      'w' => $vw - $x,
      'h' => $vh - $y,
      'vw' => $vw,
      'vh' => $vh,
      's' => $s,
      'q' => $q,
      'f' => $f
    ], array_flip($_filter));

    return is_array($filter) ? $filtered : (isset($filtered[$filter]) ? $filtered[$filter] : null);
  }

  private function validateFormat($format)
  {
    $f = 'jpg' == $format ? 'jpeg' : $format;
    if (!in_array($f, ['jpeg', 'png', 'webp'])) {
      $f = $this->get_default_options('f');
    }
    return $f;
  }

  private function validateQuality($quality)
  {
    $quality = intval($quality);
    if (0 > $quality || $quality > 100) {
      $quality = $this->get_default_options('q');
    }
    return $quality;
  }

  private function validateCaptureSize(&$gl_options)
  {
    $minHeight = 100;
    $minWidth = 100;

    $maxWidth = (int) $gl_options->vw - (int) $gl_options->x;
    $maxHeight = (int) $gl_options->vh - (int) $gl_options->y;

    $maxX = (int) $gl_options->vw - $minWidth;
    $maxY = (int) $gl_options->vh - $minHeight;

    if ((int) $gl_options->x > $maxX) {
      (int) $gl_options->x = $maxX;
      $this->validateCaptureSize($gl_options);
    }
    if ((int) $gl_options->y > $maxY) {
      (int) $gl_options->y = $maxY;
      $this->validateCaptureSize($gl_options);
    }
    if ((int) $gl_options->w > $maxWidth) {
      (int) $gl_options->w = $maxWidth;
      $this->validateCaptureSize($gl_options);
    }
    if ((int) $gl_options->h > $maxHeight) {
      (int) $gl_options->h = $maxHeight;
      $this->validateCaptureSize($gl_options);
    }
    if ((int) $gl_options->w < $minWidth) {
      (int) $gl_options->w = $minWidth;
      $this->validateCaptureSize($gl_options);
    }
    if ((int) $gl_options->h < $minHeight) {
      (int) $gl_options->h = $minHeight;
      $this->validateCaptureSize($gl_options);
    }
  }

  public function saveToSeafile($path_to_file)
  {
    $referer = $this->getController()->getRequest()->getEnv('HTTP_REFERER');
    preg_match('/^(?:http(?:s?):\/\/(?:www\.)?)?([A-Za-z0-9_:.-]+)\/?/m', $referer?? '', $matches);
    $domain = 2 === count($matches) ? $matches[1] : null;

    $dt                 = new DateTime();
    $filename           = basename($path_to_file);
    $parent_folder      = $dt->format('Y-m-d');
    $seafile_subfolder  = $domain;
    $seafile_folder     = DS . trailingslashit($parent_folder) . $seafile_subfolder;

    $repo_id  = 'd04a2c3c-eda3-49d6-b946-ac70beb9bbf2';

    $headers = [
      'Authorization' => 'Bearer cdd940c1c82aa99c7d84ef4551c13922c687aecc',
    ];
    $host = 'https://cloud.doojoo.de';
    $client   = new Client([
      'host' => $host,
      'headers' => $headers
    ]);

    /**
     * Create subdirectory if not already exists
     */
    $data = new FormData();
    $data->addMany([
      'operation' => 'mkdir',
      'create_parents' => 1
    ]);

    $response = $client->post(
      "$host/api2/repos/$repo_id/dir/?p=$seafile_folder",
      (string) $data,
      [
        'headers' => [
          'Content-Type' => $data->contentType(),
          'Accept' => 'application/json'
        ]
      ]
    );
    $result = json_decode($response->getBody());
    if (isset($result->error)) {
      throw new Exception(sprintf('%s. Could not create seafile folder %s', $result->error, $seafile_folder), 400);
    }

    // Get upload link to folder
    $response = $client->get("$host/api2/repos/$repo_id/upload-link/?p=$seafile_folder");
    $result = json_decode($response->getBody());

    if (isset($result->error)) {
      throw new Exception($result->error, 400);
    }
    $upload_link = $result;

    /**
     * Upload file to the previously generated upload link
     */
    $data = new FormData();
    $data->addMany([
      'parent_dir' => $seafile_folder,
      'replace' => 1,
    ]);
    $data->addFile('file', fopen($path_to_file, 'r'));

    $response = $client->post(
      "$upload_link?ret-json=1",
      (string) $data,
      [
        'headers' => [
          'Content-Type' => $data->contentType(), // multipart/form-data; boundary=84530945034850
          'Accept' => 'application/json'
        ]
      ]
    );

    $result = json_decode($response->getBody());

    if (isset($result->error)) {
      throw new Exception($result->error, 400);
    }

    /**
     * Get seafile download link
     */
    $path_to_file = $seafile_folder . DS . $filename;
    $response = $client->get("$host/api2/repos/$repo_id/file/?p=$path_to_file&reuse=1");

    $result = json_decode($response->getBody());

    if (isset($result->error)) {
      throw new Exception($result->error, 400);
    }

    /**
     * Create share link
     */
    $response = $client->post(
      "$host/api/v2.1/share-links/",
      json_encode([
        'repo_id' => $repo_id,
        'path' => $path_to_file
      ]),
      ['type' => 'json']
    );

    $result = json_decode($response->getBody());

    if (isset($result->error)) {
      throw new Exception($result->error, 400);
    }

    if (isset($result->link)) {
      return $result->link . '?dl=1';
    } else {
      throw new Exception('Missing share link property in seafile response', 400);
    }
  }
}
