<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Cocktail $cocktail
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Cocktails'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="cocktails form large-9 medium-8 columns content">
    <?= $this->Form->create($cocktail) ?>
    <fieldset>
        <legend><?= __('Add Cocktail') ?></legend>
        <?php
            echo $this->Form->control('name');
            echo $this->Form->control('description');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
