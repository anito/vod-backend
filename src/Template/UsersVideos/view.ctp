<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\UsersVideo $usersVideo
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Users Video'), ['action' => 'edit', $usersVideo->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Users Video'), ['action' => 'delete', $usersVideo->id], ['confirm' => __('Are you sure you want to delete # {0}?', $usersVideo->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Users Videos'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Users Video'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Videos'), ['controller' => 'Videos', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Video'), ['controller' => 'Videos', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="usersVideos view large-9 medium-8 columns content">
    <h3><?= h($usersVideo->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('User') ?></th>
            <td><?= $usersVideo->has('user') ? $this->Html->link($usersVideo->user->name, ['controller' => 'Users', 'action' => 'view', $usersVideo->user->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Video') ?></th>
            <td><?= $usersVideo->has('video') ? $this->Html->link($usersVideo->video->title, ['controller' => 'Videos', 'action' => 'view', $usersVideo->video->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($usersVideo->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Start') ?></th>
            <td><?= h($usersVideo->start) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('End') ?></th>
            <td><?= h($usersVideo->end) ?></td>
        </tr>
    </table>
</div>
