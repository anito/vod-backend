<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Inbox[]|\Cake\Collection\CollectionInterface $inboxes
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Inbox'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="inboxes index large-9 medium-8 columns content">
    <h3><?= __('Inboxes') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('user_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('_from') ?></th>
                <th scope="col"><?= $this->Paginator->sort('_to') ?></th>
                <th scope="col"><?= $this->Paginator->sort('_read') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inboxes as $inbox): ?>
            <tr>
                <td><?= h($inbox->id) ?></td>
                <td><?= $inbox->has('user') ? $this->Html->link($inbox->user->name, ['controller' => 'Users', 'action' => 'view', $inbox->user->id]) : '' ?></td>
                <td><?= h($inbox->_from) ?></td>
                <td><?= h($inbox->_to) ?></td>
                <td><?= h($inbox->_read) ?></td>
                <td><?= h($inbox->created) ?></td>
                <td><?= h($inbox->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $inbox->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $inbox->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $inbox->id], ['confirm' => __('Are you sure you want to delete # {0}?', $inbox->id)]) ?>
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
