<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Avatar $avatar
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Avatars'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="avatars form large-9 medium-8 columns content">
    <?= $this->Form->create($avatar) ?>
    <fieldset>
        <legend><?= __('Add Avatar') ?></legend>
        <?php
            echo $this->Form->control('src');
            echo $this->Form->control('user_id', ['options' => $users]);
            echo $this->Form->control('filesize');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
