<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EmailTemplate $emailTemplate
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $emailTemplate->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $emailTemplate->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Email Templates'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Templates'), ['controller' => 'Templates', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Template'), ['controller' => 'Templates', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="emailTemplates form large-9 medium-8 columns content">
    <?= $this->Form->create($emailTemplate) ?>
    <fieldset>
        <legend><?= __('Edit Email Template') ?></legend>
        <?php
            echo $this->Form->control('template_id', ['options' => $templates]);
            echo $this->Form->control('name');
            echo $this->Form->control('subject');
            echo $this->Form->control('before_content');
            echo $this->Form->control('content');
            echo $this->Form->control('after_content');
            echo $this->Form->control('before_sitename');
            echo $this->Form->control('sitename');
            echo $this->Form->control('after_sitename');
            echo $this->Form->control('before_footer');
            echo $this->Form->control('footer');
            echo $this->Form->control('after_footer');
            echo $this->Form->control('protected');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
