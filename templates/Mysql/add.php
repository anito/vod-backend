<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Mysql $mysql
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Mysql'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="mysql form large-9 medium-8 columns content">
    <?= $this->Form->create($mysql) ?>
    <fieldset>
        <legend><?= __('Add Mysql') ?></legend>
        <?php
            echo $this->Form->control('filename');
            echo $this->Form->control('description');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
