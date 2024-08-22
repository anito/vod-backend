<?php

namespace App\Controller\Component;

use Cake\Controller\Component;

class UriComponent extends Component
{

  protected array $components = ['Upload', 'Director'];
  
  protected array $params;

  public function initialize(array $config): void
  {
    $this->params = $this->getController()->getRequest()->getQueryParams();
  }

  /**
   * @param id      Subfolder name
   * @return array  Returns array
   */
  public function getUrl($id): array | null
  {
    $path = $this->Upload->getPath();
    $type = $this->Upload->getType();

    $lg_path = $path . DS . $id . DS . 'lg';
    $files = glob($lg_path . DS . '*.*');
    if (!empty($files)) {
      $fn = basename($files[0]);

      $options = array_merge(compact(array('fn', 'id', 'type')), $this->params);
      // encrypt url
      $url = $this->Director->p($options);
      $json = json_encode($this->params);
      $stringified = preg_replace('/["\'\s]/', '', $json);

      return [
        'id' => $id,
        'url' => $url,
        'params' => $stringified,
      ];
    }
    return null;
  }
}
