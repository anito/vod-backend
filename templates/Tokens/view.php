<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Token $token
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Token'), ['action' => 'edit', $token->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Token'), ['action' => 'delete', $token->id], ['confirm' => __('Are you sure you want to delete # {0}?', $token->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Tokens'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Token'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="tokens view large-9 medium-8 columns content">
    <h3><?= h($token->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= h($token->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('User') ?></th>
            <td><?= $token->has('user') ? $this->Html->link($token->user->name, ['controller' => 'Users', 'action' => 'view', $token->user->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($token->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($token->modified) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Token') ?></h4>
        <?= $this->Text->autoParagraph(h($token->token)); ?>
    </div>
</div>
