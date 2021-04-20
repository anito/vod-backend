<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Template $template
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Templates'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Email Templates'), ['controller' => 'EmailTemplates', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Email Template'), ['controller' => 'EmailTemplates', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="templates form large-9 medium-8 columns content">
    <?= $this->Form->create($template) ?>
    <fieldset>
        <legend><?= __('Add Template') ?></legend>
        <?php
            echo $this->Form->control('slug');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
