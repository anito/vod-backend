<?php
use Cake\Log\Log;
use Cake\Core\Configure;

Configure::write('debug', false);

header("Pragma: no-cache");
header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
header("Content-Type: application/json; charset=utf-8");

echo json_encode($_serialize);
