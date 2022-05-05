<?php

/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $video
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
  <ul class="side-nav">
    <li class="heading"><?= __('Actions') ?></li>
    <li><?= $this->Html->link(__('List Videos'), ['action' => 'index']) ?></li>
    <li><?= $this->Html->link(__('List Images'), ['controller' => 'Images', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link(__('New Image'), ['controller' => 'Images', 'action' => 'add']) ?></li>
    <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
  </ul>
</nav>
<div class="videos form large-9 medium-8 columns content">
  <?= $this->Form->create($video) ?>
  <fieldset>
    <legend><?= __('Add Video') ?></legend>
    <?php
    echo $this->Form->control('image_id');
    echo $this->Form->control('title');
    echo $this->Form->control('description');
    echo $this->Form->control('src');
    echo $this->Form->control('filesize');
    echo $this->Form->control('teaser');
    echo $this->Form->control('playhead');
    echo $this->Form->control('sequence');
    ?>
  </fieldset>
  <?= $this->Form->button(__('Submit')) ?>
  <?= $this->Form->end() ?>
</div>