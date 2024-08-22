<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Screenshot $screenshot
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $screenshot->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $screenshot->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Screenshots'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="screenshots form content">
            <?= $this->Form->create($screenshot) ?>
            <fieldset>
                <legend><?= __('Edit Screenshot') ?></legend>
                <?php
                    echo $this->Form->control('src');
                    echo $this->Form->control('subfolder');
                    echo $this->Form->control('filesize');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
