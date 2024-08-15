<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Image $image
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Image'), ['action' => 'edit', $image->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Image'), ['action' => 'delete', $image->id], ['confirm' => __('Are you sure you want to delete # {0}?', $image->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Images'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Image'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Videos'), ['controller' => 'Videos', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Video'), ['controller' => 'Videos', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="images view large-9 medium-8 columns content">
    <h3><?= h($image->src) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= h($image->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Iso') ?></th>
            <td><?= h($image->iso) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Longitude') ?></th>
            <td><?= h($image->longitude) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Aperture') ?></th>
            <td><?= h($image->aperture) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Make') ?></th>
            <td><?= h($image->make) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Model') ?></th>
            <td><?= h($image->model) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Title') ?></th>
            <td><?= h($image->title) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Exposure') ?></th>
            <td><?= h($image->exposure) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Software') ?></th>
            <td><?= h($image->software) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Src') ?></th>
            <td><?= h($image->src) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Filesize') ?></th>
            <td><?= isset($image->filesize )? $this->Number->format($image->filesize) : '&dash;' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Captured') ?></th>
            <td><?= h($image->captured) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($image->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($image->modified) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Description') ?></h4>
        <?= $this->Text->autoParagraph(h($image->description)); ?>
    </div>
    <div class="related">
        <h4><?= __('Related Videos') ?></h4>
        <?php if (!empty($image->videos)): ?>
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
            <?php foreach ($image->videos as $videos): ?>
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
</div>
