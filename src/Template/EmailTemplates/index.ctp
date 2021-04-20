<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EmailTemplate[]|\Cake\Collection\CollectionInterface $emailTemplates
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Email Template'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Templates'), ['controller' => 'Templates', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Template'), ['controller' => 'Templates', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="emailTemplates index large-9 medium-8 columns content">
    <h3><?= __('Email Templates') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('template_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('name') ?></th>
                <th scope="col"><?= $this->Paginator->sort('before_sitename') ?></th>
                <th scope="col"><?= $this->Paginator->sort('sitename') ?></th>
                <th scope="col"><?= $this->Paginator->sort('after_sitename') ?></th>
                <th scope="col"><?= $this->Paginator->sort('protected') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($emailTemplates as $emailTemplate): ?>
            <tr>
                <td><?= h($emailTemplate->id) ?></td>
                <td><?= $emailTemplate->has('template') ? $this->Html->link($emailTemplate->template->slug, ['controller' => 'Templates', 'action' => 'view', $emailTemplate->template->id]) : '' ?></td>
                <td><?= h($emailTemplate->name) ?></td>
                <td><?= h($emailTemplate->before_sitename) ?></td>
                <td><?= h($emailTemplate->sitename) ?></td>
                <td><?= h($emailTemplate->after_sitename) ?></td>
                <td><?= h($emailTemplate->protected) ?></td>
                <td><?= h($emailTemplate->created) ?></td>
                <td><?= h($emailTemplate->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $emailTemplate->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $emailTemplate->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $emailTemplate->id], ['confirm' => __('Are you sure you want to delete # {0}?', $emailTemplate->id)]) ?>
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
