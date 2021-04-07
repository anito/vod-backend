<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Mail $mail
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Mail'), ['action' => 'edit', $mail->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Mail'), ['action' => 'delete', $mail->id], ['confirm' => __('Are you sure you want to delete # {0}?', $mail->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Mails'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Mail'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="mails view large-9 medium-8 columns content">
    <h3><?= h($mail->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= h($mail->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('User Id') ?></th>
            <td><?= h($mail->user_id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($mail->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($mail->modified) ?></td>
        </tr>
    </table>
</div>
