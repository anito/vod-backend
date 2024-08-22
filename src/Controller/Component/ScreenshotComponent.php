<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Log\Log;
use Cake\Utility\Text;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Clip;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use HeadlessChromium\Page;

class ScreenshotComponent extends Component
{

  protected array $components = ['Upload', 'Director'];

  protected array $params;

  public function initialize(array $config): void
  {
    $this->params = $this->getController()->getRequest()->getQueryParams();
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

    if (!$url) {
      throw 'No url provided';
    }

    $uuid = Text::uuid();
    $fn   = "capture-$uuid.png";
    $path = rtrim(sys_get_temp_dir(), '/\\') . DS . $fn;

    try {
      $browser = (new BrowserFactory('chromium-browser'))->createBrowser(array(
        'ignoreCertificateErrors' => true,
      ));

      $default_options = $this->get_default_options();
      $capture_size = array_map('intval', array_merge($default_options, array_intersect_key($this->params, $default_options)));
      $this->validate_capture_size($capture_size);

      $x  = $capture_size['x'];
      $y  = $capture_size['y'];
      $w  = $capture_size['w'];
      $h  = $capture_size['h'];
      $vw = $capture_size['vw'];
      $vh = $capture_size['vh'];
      $s  = array_key_exists('s', $this->params) ? (float) $this->params['s'] : 1;

      $wait = 90000;
      $page = $browser->createPage();
      $page->setViewport($vw, $vh);
      $page->navigate($url)->waitForNavigation(Page::DOM_CONTENT_LOADED, $wait);

      $screenshot = $page->screenshot(array(
        'clip' => new Clip($x, $y, $w, $h, $s)
      ));
      $screenshot->saveToFile($path);
    } catch (\Exception $e) {
      // Something went wrong
      Log::debug('Something went wrong', ['message' => $e->getMessage()]);
    } finally {
      $browser->close();
    }
    return compact('path', 'fn');
  }

  private static function get_default_options()
  {
    $vw = 1200;
    $vh = 1600;
    $x = 0;
    $y = 0;

    return [
      'x' => $x,
      'y' => $y,
      'w' => $vw - $x,
      'h' => $vh - $y,
      'vw' => $vw,
      'vh' => $vh,
    ];
  }

  private function validate_capture_size(&$gl_options)
  {
    $minHeight = 100;
    $minWidth = 100;

    $maxWidth = (int) $gl_options['vw'] - (int) $gl_options['x'];
    $maxHeight = (int) $gl_options['vh'] - (int) $gl_options['y'];

    $maxX = (int) $gl_options['vw'] - $minWidth;
    $maxY = (int) $gl_options['vh'] - $minHeight;

    if ((int) $gl_options['x'] > $maxX) {
      (int) $gl_options['x'] = $maxX;
      $this->validate_capture_size($gl_options);
    }
    if ((int) $gl_options['y'] > $maxY) {
      (int) $gl_options['y'] = $maxY;
      $this->validate_capture_size($gl_options);
    }
    if ((int) $gl_options['w'] > $maxWidth) {
      (int) $gl_options['w'] = $maxWidth;
      $this->validate_capture_size($gl_options);
    }
    if ((int) $gl_options['h'] > $maxHeight) {
      (int) $gl_options['h'] = $maxHeight;
      $this->validate_capture_size($gl_options);
    }
    if ((int) $gl_options['w'] < $minWidth) {
      (int) $gl_options['w'] = $minWidth;
      $this->validate_capture_size($gl_options);
    }
    if ((int) $gl_options['h'] < $minHeight) {
      (int) $gl_options['h'] = $minHeight;
      $this->validate_capture_size($gl_options);
    }
  }

  public function saveToCloud($folder, $filename)
  {
    $source_path = SCREENSHOTS . DS . $folder . DS . 'lg' . DS . $filename;

    // Get Upload Link from Seafile
    $repo_id  = 'd04a2c3c-eda3-49d6-b946-ac70beb9bbf2';
    $folder   = '/captures' . DS . $folder;
    $path_to_file = $folder . DS . $filename;
    $headers  = [
      'Authorization' => 'Bearer cdd940c1c82aa99c7d84ef4551c13922c687aecc',
    ];

    $client   = new Client(array('headers' => $headers));

    // Create directory (will do nothing if already exists)
    $client->request('POST', "https://cloud.doojoo.de/api2/repos/$repo_id/dir/?p=$folder", [
      'multipart' => [
        [
          'name' => 'operation',
          'contents' => 'mkdir'
        ],
        [
          'name' => 'create_parents',
          'contents' => '1'
        ]
      ]
    ]);

    // Get upload link
    $request   = new Request('GET', "https://cloud.doojoo.de/api2/repos/$repo_id/upload-link/?p=$folder");
    $promise = $client->sendAsync($request)->then(function ($response) use ($client, $repo_id, $source_path, $path_to_file, $folder, $filename) {
      $upload_link = json_decode($response->getBody());

      // Post file to upload link
      $response = $client->request('POST', "$upload_link?ret-json=1", [
        'stream' => true,
        'multipart' => [
          [
            'name' => 'parent_dir',
            'contents' => $folder
          ],
          [
            'name' => 'replace',
            'contents' => '1'
          ],
          [
            'name' => 'file',
            'filename' => $filename,
            'contents' => \GuzzleHttp\Psr7\Utils::tryFopen($source_path, 'r'),
            'headers' => [
              'Content-Type' => 'image/png'
            ]
          ]
        ]
      ]);

      // Create download link
      // $response = $client->request('GET', "https://cloud.doojoo.de/api2/repos/$repo_id/file/?p=$path_to_file&reuse=1");
      // $body = $response->getBody()->read(1024);
      // return json_decode($body);

      // Create share link
      $response = $client->request('POST', "https://cloud.doojoo.de/api/v2.1/share-links/", [
        'body' => json_encode([
          'repo_id' => $repo_id,
          'path' => $path_to_file
        ]),
        'headers' => [
          'Accept' => 'application/json',
          'Content-Type' => 'application/json'
        ]
      ]);
      $body = $response->getBody();
      $result = json_decode($body);
      return $result->link . '?dl=1';
    });

    return $promise->wait();
  }
}
