<?php
$class = 'info-sign';

if (!empty($result) && $result == "success") {
    $class = 'ok';
}
$message = !empty($message) ? $message : '';

?>
<i class="glyphicon glyphicon-<?php echo h($class); ?>"></i>
<span class=""><?php echo h($message) ?></span>
