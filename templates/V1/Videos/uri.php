<?php
$this->layout = 'error';
$message = __('You must be authorized to watch this video');
// $this->layout = 'dev_error';

$this->assign('title', $message);
$this->assign('templateName', 'error500.ctp');
?>
<p class="error">
  <strong><?= __d('cake', 'Error') ?>: </strong>
  <?= h($message) ?>
</p>