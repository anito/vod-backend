<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\UsersVideo[]|\Cake\Collection\CollectionInterface $usersVideos
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Users Video'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Videos'), ['controller' => 'Videos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Video'), ['controller' => 'Videos', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="usersVideos index large-9 medium-8 columns content">
    <h3><?= __('Users Videos') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('user_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('video_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('start') ?></th>
                <th scope="col"><?= $this->Paginator->sort('end') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usersVideos as $usersVideo): ?>
            <tr>
                <td><?= $this->Number->format($usersVideo->id) ?></td>
                <td><?= $usersVideo->has('user') ? $this->Html->link($usersVideo->user->name, ['controller' => 'Users', 'action' => 'view', $usersVideo->user->id]) : '' ?></td>
                <td><?= $usersVideo->has('video') ? $this->Html->link($usersVideo->video->title, ['controller' => 'Videos', 'action' => 'view', $usersVideo->video->id]) : '' ?></td>
                <td><?= h($usersVideo->start) ?></td>
                <td><?= h($usersVideo->end) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $usersVideo->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $usersVideo->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $usersVideo->id], ['confirm' => __('Are you sure you want to delete # {0}?', $usersVideo->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
