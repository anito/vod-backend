<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Template $template
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Template'), ['action' => 'edit', $template->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Template'), ['action' => 'delete', $template->id], ['confirm' => __('Are you sure you want to delete # {0}?', $template->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Templates'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Template'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Email Templates'), ['controller' => 'EmailTemplates', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Email Template'), ['controller' => 'EmailTemplates', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="templates view large-9 medium-8 columns content">
    <h3><?= h($template->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= h($template->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Slug') ?></th>
            <td><?= h($template->slug) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($template->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($template->modified) ?></td>
        </tr>
    </table>
    <div class="related">
        <h4><?= __('Related Email Templates') ?></h4>
        <?php if (!empty($template->email_templates)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col"><?= __('Id') ?></th>
                <th scope="col"><?= __('Template Id') ?></th>
                <th scope="col"><?= __('Name') ?></th>
                <th scope="col"><?= __('Subject') ?></th>
                <th scope="col"><?= __('Before Content') ?></th>
                <th scope="col"><?= __('Content') ?></th>
                <th scope="col"><?= __('After Content') ?></th>
                <th scope="col"><?= __('Before Sitename') ?></th>
                <th scope="col"><?= __('Sitename') ?></th>
                <th scope="col"><?= __('After Sitename') ?></th>
                <th scope="col"><?= __('Before Footer') ?></th>
                <th scope="col"><?= __('Footer') ?></th>
                <th scope="col"><?= __('After Footer') ?></th>
                <th scope="col"><?= __('Protected') ?></th>
                <th scope="col"><?= __('Created') ?></th>
                <th scope="col"><?= __('Modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($template->email_templates as $emailTemplates): ?>
            <tr>
                <td><?= h($emailTemplates->id) ?></td>
                <td><?= h($emailTemplates->template_id) ?></td>
                <td><?= h($emailTemplates->name) ?></td>
                <td><?= h($emailTemplates->subject) ?></td>
                <td><?= h($emailTemplates->before_content) ?></td>
                <td><?= h($emailTemplates->content) ?></td>
                <td><?= h($emailTemplates->after_content) ?></td>
                <td><?= h($emailTemplates->before_sitename) ?></td>
                <td><?= h($emailTemplates->sitename) ?></td>
                <td><?= h($emailTemplates->after_sitename) ?></td>
                <td><?= h($emailTemplates->before_footer) ?></td>
                <td><?= h($emailTemplates->footer) ?></td>
                <td><?= h($emailTemplates->after_footer) ?></td>
                <td><?= h($emailTemplates->protected) ?></td>
                <td><?= h($emailTemplates->created) ?></td>
                <td><?= h($emailTemplates->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'EmailTemplates', 'action' => 'view', $emailTemplates->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'EmailTemplates', 'action' => 'edit', $emailTemplates->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'EmailTemplates', 'action' => 'delete', $emailTemplates->id], ['confirm' => __('Are you sure you want to delete # {0}?', $emailTemplates->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
