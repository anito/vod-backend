<?php

/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $video
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
  <ul class="side-nav">
    <li class="heading"><?= __('Actions') ?></li>
    <li><?= $this->Html->link(__('Edit Video'), ['action' => 'edit', $video->id]) ?> </li>
    <li><?= $this->Form->postLink(__('Delete Video'), ['action' => 'delete', $video->id], ['confirm' => __('Are you sure you want to delete # {0}?', $video->id)]) ?> </li>
    <li><?= $this->Html->link(__('List Videos'), ['action' => 'index']) ?> </li>
    <li><?= $this->Html->link(__('New Video'), ['action' => 'add']) ?> </li>
    <li><?= $this->Html->link(__('List Images'), ['controller' => 'Images', 'action' => 'index']) ?> </li>
    <li><?= $this->Html->link(__('New Image'), ['controller' => 'Images', 'action' => 'add']) ?> </li>
    <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
    <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
  </ul>
</nav>
<div class="videos view large-9 medium-8 columns content">
  <h3><?= h($video->title) ?></h3>
  <table>
    <tr>
      <th><?= __('Id') ?></th>
      <td><?= h($video->id) ?></td>
    </tr>
    <tr>
      <th><?= __('Image Id') ?></th>
      <td><?= h($video->image_id) ?></td>
    </tr>
    <tr>
      <th><?= __('Title') ?></th>
      <td><?= h($video->title) ?></td>
    </tr>
    <tr>
      <th><?= __('Description') ?></th>
      <td><?= h($video->description) ?></td>
    </tr>
    <tr>
      <th><?= __('Src') ?></th>
      <td><?= h($video->src) ?></td>
    </tr>
    <tr>
      <th><?= __('Filesize') ?></th>
      <td><?= isset($video->filesize) ? $this->Number->format($video->filesize) :'&dash;' ?></td>
    </tr>
    <tr>
      <th><?= __('Playhead') ?></th>
      <td><?= isset($video->playhead) ? $this->Number->format($video->playhead) : '&dash;' ?></td>
    </tr>
    <tr>
      <th><?= __('Sequence') ?></th>
      <td><?= isset($video->sequence) ? $this->Number->format($video->sequence) : '&dash;' ?></td>
    </tr>
    <tr>
      <th><?= __('Created') ?></th>
      <td><?= h($video->created) ?></td>
    </tr>
    <tr>
      <th><?= __('Modified') ?></th>
      <td><?= h($video->modified) ?></td>
    </tr>
    <tr>
      <th><?= __('Teaser') ?></th>
      <td><?= $video->teaser ? __('Yes') : __('No'); ?></td>
    </tr>
  </table>
</div>