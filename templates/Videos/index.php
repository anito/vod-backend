<?php

/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface[]|\Cake\Collection\CollectionInterface $videos
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
  <ul class="side-nav">
    <li class="heading"><?= __('Actions') ?></li>
    <li><?= $this->Html->link(__('New Video'), ['action' => 'add']) ?></li>
    <li><?= $this->Html->link(__('List Images'), ['controller' => 'Images', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link(__('New Image'), ['controller' => 'Images', 'action' => 'add']) ?></li>
    <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
    <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
  </ul>
</nav>
<div class="videos index content large-9 medium-8 columns">
  <h3><?= __('Videos') ?></h3>
  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th><?= $this->Paginator->sort('id') ?></th>
          <th><?= $this->Paginator->sort('image_id') ?></th>
          <th><?= $this->Paginator->sort('title') ?></th>
          <th><?= $this->Paginator->sort('description') ?></th>
          <th><?= $this->Paginator->sort('src') ?></th>
          <th><?= $this->Paginator->sort('filesize') ?></th>
          <th><?= $this->Paginator->sort('teaser') ?></th>
          <th><?= $this->Paginator->sort('playhead') ?></th>
          <th><?= $this->Paginator->sort('created') ?></th>
          <th><?= $this->Paginator->sort('modified') ?></th>
          <th><?= $this->Paginator->sort('sequence') ?></th>
          <th class="actions"><?= __('Actions') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($videos as $video) : ?>
          <tr>
            <td><?= h($video->id) ?></td>
            <td><?= h($video->image_id) ?></td>
            <td><?= h($video->title) ?></td>
            <td><?= h($video->description) ?></td>
            <td><?= h($video->src) ?></td>
            <td><?= $this->Number->format($video->filesize) ?></td>
            <td><?= h($video->teaser) ?></td>
            <td><?= $this->Number->format($video->playhead) ?></td>
            <td><?= h($video->created) ?></td>
            <td><?= h($video->modified) ?></td>
            <td><?= $this->Number->format($video->sequence) ?></td>
            <td class="actions">
              <?= $this->Html->link(__('View'), ['action' => 'view', $video->id]) ?>
              <?= $this->Html->link(__('Edit'), ['action' => 'edit', $video->id]) ?>
              <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $video->id], ['confirm' => __('Are you sure you want to delete # {0}?', $video->id)]) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="paginator">
    <ul class="pagination">
      <?= $this->Paginator->first('<< ' . __('first')) ?>
      <?= $this->Paginator->prev('< ' . __('previous')) ?>
      <?= $this->Paginator->numbers() ?>
      <?= $this->Paginator->next(__('next') . ' >') ?>
      <?= $this->Paginator->last(__('last') . ' >>') ?>
    </ul>
    <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
  </div>
</div>