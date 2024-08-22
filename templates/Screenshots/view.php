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
            <?= $this->Html->link(__('Edit Screenshot'), ['action' => 'edit', $screenshot->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Screenshot'), ['action' => 'delete', $screenshot->id], ['confirm' => __('Are you sure you want to delete # {0}?', $screenshot->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Screenshots'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Screenshot'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="screenshots view content">
            <h3><?= h($screenshot->src) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($screenshot->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Src') ?></th>
                    <td><?= h($screenshot->src) ?></td>
                </tr>
                <tr>
                    <th><?= __('Product Id') ?></th>
                    <td><?= h($screenshot->subfolder) ?></td>
                </tr>
                <tr>
                    <th><?= __('Filesize') ?></th>
                    <td><?= $screenshot->filesize === null ? '' : $this->Number->format($screenshot->filesize) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($screenshot->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($screenshot->modified) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
