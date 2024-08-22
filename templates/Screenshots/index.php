<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Screenshot> $screenshots
 */
?>
<div class="screenshots index content">
    <?= $this->Html->link(__('New Screenshot'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Screenshots') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('src') ?></th>
                    <th><?= $this->Paginator->sort('link') ?></th>
                    <th><?= $this->Paginator->sort('filesize') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($screenshots as $screenshot): ?>
                <tr>
                    <td><?= h($screenshot->id) ?></td>
                    <td><?= h($screenshot->src) ?></td>
                    <td><?= h($screenshot->link) ?></td>
                    <td><?= $screenshot->filesize === null ? '' : $this->Number->format($screenshot->filesize) ?></td>
                    <td><?= h($screenshot->created) ?></td>
                    <td><?= h($screenshot->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $screenshot->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $screenshot->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $screenshot->id], ['confirm' => __('Are you sure you want to delete # {0}?', $screenshot->id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
