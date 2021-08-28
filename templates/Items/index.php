<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Item[]|\Cake\Collection\CollectionInterface $items
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?=__('Actions')?></li>
        <li><?=$this->Html->link(__('New Item'), ['action' => 'add'])?></li>
        <li><?=$this->Html->link(__('List Fields'), ['controller' => 'Fields', 'action' => 'index'])?></li>
        <li><?=$this->Html->link(__('New Field'), ['controller' => 'Fields', 'action' => 'add'])?></li>
        <li><?=$this->Html->link(__('List Templates'), ['controller' => 'Templates', 'action' => 'index'])?></li>
        <li><?=$this->Html->link(__('New Template'), ['controller' => 'Templates', 'action' => 'add'])?></li>
    </ul>
</nav>
<div class="items index large-9 medium-8 columns content">
    <h3><?=__('Items')?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?=$this->Paginator->sort('id')?></th>
                <th scope="col"><?=$this->Paginator->sort('field_id')?></th>
                <th scope="col"><?=$this->Paginator->sort('template_id')?></th>
                <th scope="col" class="actions"><?=__('Actions')?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?=h($item->id)?></td>
                <td><?=$item->has('field') ? $this->Html->link($item->field->name, ['controller' => 'Fields', 'action' => 'view', $item->field->id]) : ''?></td>
                <td><?=$item->has('template') ? $this->Html->link($item->template->slug, ['controller' => 'Templates', 'action' => 'view', $item->template->id]) : ''?></td>
                <td class="actions">
                    <?=$this->Html->link(__('View'), ['action' => 'view', $item->id])?>
                    <?=$this->Html->link(__('Edit'), ['action' => 'edit', $item->id])?>
                    <?=$this->Form->postLink(__('Delete'), ['action' => 'delete', $item->id], ['confirm' => __('Are you sure you want to delete # {0}?', $item->id)])?>
                </td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?=$this->Paginator->first('<< ' . __('first'))?>
            <?=$this->Paginator->prev('< ' . __('previous'))?>
            <?=$this->Paginator->numbers()?>
            <?=$this->Paginator->next(__('next') . ' >')?>
            <?=$this->Paginator->last(__('last') . ' >>')?>
        </ul>
        <p><?=$this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total'))?></p>
    </div>
</div>
