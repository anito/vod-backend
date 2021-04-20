<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EmailTemplate $emailTemplate
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Email Template'), ['action' => 'edit', $emailTemplate->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Email Template'), ['action' => 'delete', $emailTemplate->id], ['confirm' => __('Are you sure you want to delete # {0}?', $emailTemplate->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Email Templates'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Email Template'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Templates'), ['controller' => 'Templates', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Template'), ['controller' => 'Templates', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="emailTemplates view large-9 medium-8 columns content">
    <h3><?= h($emailTemplate->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= h($emailTemplate->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Template') ?></th>
            <td><?= $emailTemplate->has('template') ? $this->Html->link($emailTemplate->template->slug, ['controller' => 'Templates', 'action' => 'view', $emailTemplate->template->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Name') ?></th>
            <td><?= h($emailTemplate->name) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Before Sitename') ?></th>
            <td><?= h($emailTemplate->before_sitename) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Sitename') ?></th>
            <td><?= h($emailTemplate->sitename) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('After Sitename') ?></th>
            <td><?= h($emailTemplate->after_sitename) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($emailTemplate->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($emailTemplate->modified) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Protected') ?></th>
            <td><?= $emailTemplate->protected ? __('Yes') : __('No'); ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Subject') ?></h4>
        <?= $this->Text->autoParagraph(h($emailTemplate->subject)); ?>
    </div>
    <div class="row">
        <h4><?= __('Before Content') ?></h4>
        <?= $this->Text->autoParagraph(h($emailTemplate->before_content)); ?>
    </div>
    <div class="row">
        <h4><?= __('Content') ?></h4>
        <?= $this->Text->autoParagraph(h($emailTemplate->content)); ?>
    </div>
    <div class="row">
        <h4><?= __('After Content') ?></h4>
        <?= $this->Text->autoParagraph(h($emailTemplate->after_content)); ?>
    </div>
    <div class="row">
        <h4><?= __('Before Footer') ?></h4>
        <?= $this->Text->autoParagraph(h($emailTemplate->before_footer)); ?>
    </div>
    <div class="row">
        <h4><?= __('Footer') ?></h4>
        <?= $this->Text->autoParagraph(h($emailTemplate->footer)); ?>
    </div>
    <div class="row">
        <h4><?= __('After Footer') ?></h4>
        <?= $this->Text->autoParagraph(h($emailTemplate->after_footer)); ?>
    </div>
</div>
