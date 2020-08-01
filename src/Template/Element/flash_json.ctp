<?php
use Cake\Log\Log;
use Cake\Core\Configure;

Configure::write('debug', false);

header("Pragma: no-cache");
header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
header("Content-Type: application/json; charset=utf-8");
header("X-JSON: ");

$_flash = array('flash' => $this->Flash->render());
$_flash = array_merge($_flash, $_serialize);
Log::write('debug', $_flash);
echo $json = json_encode($_flash);