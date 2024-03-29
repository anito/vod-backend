<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User[]|\Cake\Collection\CollectionInterface $users
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?=__('Actions')?></li>
        <li><?=$this->Html->link(__('New User'), ['action' => 'add'])?></li>
        <li><?=$this->Html->link(__('List Groups'), ['controller' => 'Groups', 'action' => 'index'])?></li>
        <li><?=$this->Html->link(__('New Group'), ['controller' => 'Groups', 'action' => 'add'])?></li>
        <li><?=$this->Html->link(__('List Avatars'), ['controller' => 'Avatars', 'action' => 'index'])?></li>
        <li><?=$this->Html->link(__('New Avatar'), ['controller' => 'Avatars', 'action' => 'add'])?></li>
        <li><?=$this->Html->link(__('List Tokens'), ['controller' => 'Tokens', 'action' => 'index'])?></li>
        <li><?=$this->Html->link(__('New Token'), ['controller' => 'Tokens', 'action' => 'add'])?></li>
        <li><?=$this->Html->link(__('List Inboxes'), ['controller' => 'Inboxes', 'action' => 'index'])?></li>
        <li><?=$this->Html->link(__('New Inbox'), ['controller' => 'Inboxes', 'action' => 'add'])?></li>
        <li><?=$this->Html->link(__('List Sents'), ['controller' => 'Sents', 'action' => 'index'])?></li>
        <li><?=$this->Html->link(__('New Sent'), ['controller' => 'Sents', 'action' => 'add'])?></li>
        <li><?=$this->Html->link(__('List Videos'), ['controller' => 'Videos', 'action' => 'index'])?></li>
        <li><?=$this->Html->link(__('New Video'), ['controller' => 'Videos', 'action' => 'add'])?></li>
    </ul>
</nav>
<div class="users index large-9 medium-8 columns content">
    <h3><?=__('Users')?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?=$this->Paginator->sort('id')?></th>
                <th scope="col"><?=$this->Paginator->sort('name')?></th>
                <th scope="col"><?=$this->Paginator->sort('email')?></th>
                <th scope="col"><?=$this->Paginator->sort('password')?></th>
                <th scope="col"><?=$this->Paginator->sort('active')?></th>
                <th scope="col"><?=$this->Paginator->sort('protected')?></th>
                <th scope="col"><?=$this->Paginator->sort('group_id')?></th>
                <th scope="col"><?=$this->Paginator->sort('last_login')?></th>
                <th scope="col"><?=$this->Paginator->sort('created')?></th>
                <th scope="col"><?=$this->Paginator->sort('modified')?></th>
                <th scope="col" class="actions"><?=__('Actions')?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?=h($user->id)?></td>
                <td><?=h($user->name)?></td>
                <td><?=h($user->email)?></td>
                <td><?=h($user->password)?></td>
                <td><?=h($user->active)?></td>
                <td><?=h($user->protected)?></td>
                <td><?=$user->has('group') ? $this->Html->link($user->group->name, ['controller' => 'Groups', 'action' => 'view', $user->group->id]) : ''?></td>
                <td><?=h($user->last_login)?></td>
                <td><?=h($user->created)?></td>
                <td><?=h($user->modified)?></td>
                <td class="actions">
                    <?=$this->Html->link(__('View'), ['action' => 'view', $user->id])?>
                    <?=$this->Html->link(__('Edit'), ['action' => 'edit', $user->id])?>
                    <?=$this->Form->postLink(__('Delete'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete # {0}?', $user->id)])?>
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
