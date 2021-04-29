<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit User'), ['action' => 'edit', $user->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete User'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete # {0}?', $user->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Groups'), ['controller' => 'Groups', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Group'), ['controller' => 'Groups', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Avatars'), ['controller' => 'Avatars', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Avatar'), ['controller' => 'Avatars', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Tokens'), ['controller' => 'Tokens', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Token'), ['controller' => 'Tokens', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Inboxes'), ['controller' => 'Inboxes', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Inbox'), ['controller' => 'Inboxes', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Sents'), ['controller' => 'Sents', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Sent'), ['controller' => 'Sents', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Videos'), ['controller' => 'Videos', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Video'), ['controller' => 'Videos', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="users view large-9 medium-8 columns content">
    <h3><?= h($user->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= h($user->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Name') ?></th>
            <td><?= h($user->name) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Email') ?></th>
            <td><?= h($user->email) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Password') ?></th>
            <td><?= h($user->password) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Group') ?></th>
            <td><?= $user->has('group') ? $this->Html->link($user->group->name, ['controller' => 'Groups', 'action' => 'view', $user->group->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Avatar') ?></th>
            <td><?= $user->has('avatar') ? $this->Html->link($user->avatar->id, ['controller' => 'Avatars', 'action' => 'view', $user->avatar->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Token') ?></th>
            <td><?= $user->has('token') ? $this->Html->link($user->token->id, ['controller' => 'Tokens', 'action' => 'view', $user->token->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Last Login') ?></th>
            <td><?= h($user->last_login) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($user->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($user->modified) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Active') ?></th>
            <td><?= $user->active ? __('Yes') : __('No'); ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Protected') ?></th>
            <td><?= $user->protected ? __('Yes') : __('No'); ?></td>
        </tr>
    </table>
    <div class="related">
        <h4><?= __('Related Videos') ?></h4>
        <?php if (!empty($user->videos)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col"><?= __('Id') ?></th>
                <th scope="col"><?= __('Image Id') ?></th>
                <th scope="col"><?= __('Title') ?></th>
                <th scope="col"><?= __('Description') ?></th>
                <th scope="col"><?= __('Src') ?></th>
                <th scope="col"><?= __('Filesize') ?></th>
                <th scope="col"><?= __('Created') ?></th>
                <th scope="col"><?= __('Modified') ?></th>
                <th scope="col"><?= __('Sequence') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($user->videos as $videos): ?>
            <tr>
                <td><?= h($videos->id) ?></td>
                <td><?= h($videos->image_id) ?></td>
                <td><?= h($videos->title) ?></td>
                <td><?= h($videos->description) ?></td>
                <td><?= h($videos->src) ?></td>
                <td><?= h($videos->filesize) ?></td>
                <td><?= h($videos->created) ?></td>
                <td><?= h($videos->modified) ?></td>
                <td><?= h($videos->sequence) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Videos', 'action' => 'view', $videos->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Videos', 'action' => 'edit', $videos->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Videos', 'action' => 'delete', $videos->id], ['confirm' => __('Are you sure you want to delete # {0}?', $videos->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <div class="related">
        <h4><?= __('Related Inboxes') ?></h4>
        <?php if (!empty($user->inboxes)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col"><?= __('Id') ?></th>
                <th scope="col"><?= __('User Id') ?></th>
                <th scope="col"><?= __(' From') ?></th>
                <th scope="col"><?= __(' To') ?></th>
                <th scope="col"><?= __(' Read') ?></th>
                <th scope="col"><?= __('Message') ?></th>
                <th scope="col"><?= __('Created') ?></th>
                <th scope="col"><?= __('Modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($user->inboxes as $inboxes): ?>
            <tr>
                <td><?= h($inboxes->id) ?></td>
                <td><?= h($inboxes->user_id) ?></td>
                <td><?= h($inboxes->_from) ?></td>
                <td><?= h($inboxes->_to) ?></td>
                <td><?= h($inboxes->_read) ?></td>
                <td><?= h($inboxes->message) ?></td>
                <td><?= h($inboxes->created) ?></td>
                <td><?= h($inboxes->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Inboxes', 'action' => 'view', $inboxes->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Inboxes', 'action' => 'edit', $inboxes->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Inboxes', 'action' => 'delete', $inboxes->id], ['confirm' => __('Are you sure you want to delete # {0}?', $inboxes->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <div class="related">
        <h4><?= __('Related Sents') ?></h4>
        <?php if (!empty($user->sents)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col"><?= __('Id') ?></th>
                <th scope="col"><?= __('User Id') ?></th>
                <th scope="col"><?= __(' To') ?></th>
                <th scope="col"><?= __(' From') ?></th>
                <th scope="col"><?= __('Message') ?></th>
                <th scope="col"><?= __('Created') ?></th>
                <th scope="col"><?= __('Modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($user->sents as $sents): ?>
            <tr>
                <td><?= h($sents->id) ?></td>
                <td><?= h($sents->user_id) ?></td>
                <td><?= h($sents->_to) ?></td>
                <td><?= h($sents->_from) ?></td>
                <td><?= h($sents->message) ?></td>
                <td><?= h($sents->created) ?></td>
                <td><?= h($sents->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'Sents', 'action' => 'view', $sents->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'Sents', 'action' => 'edit', $sents->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Sents', 'action' => 'delete', $sents->id], ['confirm' => __('Are you sure you want to delete # {0}?', $sents->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
