<?php

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Video $video
 * @var string[]|\Cake\Collection\CollectionInterface $images
 * @var string[]|\Cake\Collection\CollectionInterface $users
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
  <ul class="side-nav">
    <li class="heading"><?= __('Actions') ?></li>
    <li><?= $this->Form->postLink(
          __('Delete'),
          ['action' => 'delete', $video->id],
          ['confirm' => __('Are you sure you want to delete # {0}?', $video->id)]
        )
        ?></li>
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
    <legend><?= __('Edit Video') ?></legend>
    <?php
    echo $this->Form->control('image_id', ['options' => $images, 'empty' => true]);
    echo $this->Form->control('title');
    echo $this->Form->control('description');
    echo $this->Form->control('src');
    echo $this->Form->control('filesize');
    echo $this->Form->control('teaser');
    echo $this->Form->control('playhead');
    echo $this->Form->control('sequence');
    echo $this->Form->control('users._ids', ['options' => $users]);
    ?>
  </fieldset>
  <?= $this->Form->button(__('Submit')) ?>
  <?= $this->Form->end() ?>
</div>