<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Inbox $inbox
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Inbox'), ['action' => 'edit', $inbox->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Inbox'), ['action' => 'delete', $inbox->id], ['confirm' => __('Are you sure you want to delete # {0}?', $inbox->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Inboxes'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Inbox'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="inboxes view large-9 medium-8 columns content">
    <h3><?= h($inbox->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= h($inbox->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('User') ?></th>
            <td><?= $inbox->has('user') ? $this->Html->link($inbox->user->name, ['controller' => 'Users', 'action' => 'view', $inbox->user->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __(' From') ?></th>
            <td><?= h($inbox->_from) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __(' To') ?></th>
            <td><?= h($inbox->_to) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($inbox->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($inbox->modified) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __(' Read') ?></th>
            <td><?= $inbox->_read ? __('Yes') : __('No'); ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Message') ?></h4>
        <?= $this->Text->autoParagraph(h($inbox->message)); ?>
    </div>
</div>
