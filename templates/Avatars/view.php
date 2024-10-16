<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Avatar $avatar
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Avatar'), ['action' => 'edit', $avatar->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Avatar'), ['action' => 'delete', $avatar->id], ['confirm' => __('Are you sure you want to delete # {0}?', $avatar->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Avatars'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Avatar'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="avatars view large-9 medium-8 columns content">
    <h3><?= h($avatar->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= h($avatar->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Src') ?></th>
            <td><?= h($avatar->src) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('User') ?></th>
            <td><?= $avatar->has('user') ? $this->Html->link($avatar->user->name, ['controller' => 'Users', 'action' => 'view', $avatar->user->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Filesize') ?></th>
            <td><?= isset($avatar->filesize) ? $this->Number->format($avatar->filesize) : '&dash;' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($avatar->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($avatar->modified) ?></td>
        </tr>
    </table>
</div>
