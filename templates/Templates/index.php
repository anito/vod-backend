<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Template[]|\Cake\Collection\CollectionInterface $templates
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?=__('Actions')?></li>
        <li><?=$this->Html->link(__('New Template'), ['action' => 'add'])?></li>
        <li><?=$this->Html->link(__('List Items'), ['controller' => 'Items', 'action' => 'index'])?></li>
        <li><?=$this->Html->link(__('New Item'), ['controller' => 'Items', 'action' => 'add'])?></li>
    </ul>
</nav>
<div class="templates index large-9 medium-8 columns content">
    <h3><?=__('Templates')?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?=$this->Paginator->sort('id')?></th>
                <th scope="col"><?=$this->Paginator->sort('slug')?></th>
                <th scope="col"><?=$this->Paginator->sort('name')?></th>
                <th scope="col"><?=$this->Paginator->sort('protected')?></th>
                <th scope="col"><?=$this->Paginator->sort('created')?></th>
                <th scope="col"><?=$this->Paginator->sort('modified')?></th>
                <th scope="col" class="actions"><?=__('Actions')?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($templates as $template): ?>
            <tr>
                <td><?=h($template->id)?></td>
                <td><?=h($template->slug)?></td>
                <td><?=h($template->name)?></td>
                <td><?=h($template->protected)?></td>
                <td><?=h($template->created)?></td>
                <td><?=h($template->modified)?></td>
                <td class="actions">
                    <?=$this->Html->link(__('View'), ['action' => 'view', $template->id])?>
                    <?=$this->Html->link(__('Edit'), ['action' => 'edit', $template->id])?>
                    <?=$this->Form->postLink(__('Delete'), ['action' => 'delete', $template->id], ['confirm' => __('Are you sure you want to delete # {0}?', $template->id)])?>
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
