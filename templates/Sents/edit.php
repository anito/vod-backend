<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Sent $sent
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?=__('Actions')?></li>
        <li><?=$this->Form->postLink(
    __('Delete'),
    ['action' => 'delete', $sent->id],
    ['confirm' => __('Are you sure you want to delete # {0}?', $sent->id)]
)
?></li>
        <li><?=$this->Html->link(__('List Sents'), ['action' => 'index'])?></li>
        <li><?=$this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index'])?></li>
        <li><?=$this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add'])?></li>
    </ul>
</nav>
<div class="sents form large-9 medium-8 columns content">
    <?=$this->Form->create($sent)?>
    <fieldset>
        <legend><?=__('Edit Sent')?></legend>
        <?php
echo $this->Form->control('user_id', ['options' => $users]);
echo $this->Form->control('_to');
echo $this->Form->control('_from');
echo $this->Form->control('message');
?>
    </fieldset>
    <?=$this->Form->button(__('Submit'))?>
    <?=$this->Form->end()?>
</div>
