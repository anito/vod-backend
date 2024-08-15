<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Avatar[]|\Cake\Collection\CollectionInterface $avatars
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?=__('Actions')?></li>
        <li><?=$this->Html->link(__('New Avatar'), ['action' => 'add'])?></li>
        <li><?=$this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index'])?></li>
        <li><?=$this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add'])?></li>
    </ul>
</nav>
<div class="avatars index large-9 medium-8 columns content">
    <h3><?=__('Avatars')?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?=$this->Paginator->sort('id')?></th>
                <th scope="col"><?=$this->Paginator->sort('src')?></th>
                <th scope="col"><?=$this->Paginator->sort('user_id')?></th>
                <th scope="col"><?=$this->Paginator->sort('filesize')?></th>
                <th scope="col"><?=$this->Paginator->sort('created')?></th>
                <th scope="col"><?=$this->Paginator->sort('modified')?></th>
                <th scope="col" class="actions"><?=__('Actions')?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($avatars as $avatar): ?>
            <tr>
                <td><?=h($avatar->id)?></td>
                <td><?=h($avatar->src)?></td>
                <td><?=$avatar->has('user') ? $this->Html->link($avatar->user->name, ['controller' => 'Users', 'action' => 'view', $avatar->user->id]) : ''?></td>
                <td><?= isset($avatar->filesize) ? $this->Number->format($avatar->filesize) : '&dash;' ?></td>
                <td><?=h($avatar->created)?></td>
                <td><?=h($avatar->modified)?></td>
                <td class="actions">
                    <?=$this->Html->link(__('View'), ['action' => 'view', $avatar->id])?>
                    <?=$this->Html->link(__('Edit'), ['action' => 'edit', $avatar->id])?>
                    <?=$this->Form->postLink(__('Delete'), ['action' => 'delete', $avatar->id], ['confirm' => __('Are you sure you want to delete # {0}?', $avatar->id)])?>
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
