<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Image[]|\Cake\Collection\CollectionInterface $images
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?=__('Actions')?></li>
        <li><?=$this->Html->link(__('New Image'), ['action' => 'add'])?></li>
        <li><?=$this->Html->link(__('List Videos'), ['controller' => 'Videos', 'action' => 'index'])?></li>
        <li><?=$this->Html->link(__('New Video'), ['controller' => 'Videos', 'action' => 'add'])?></li>
    </ul>
</nav>
<div class="images index large-9 medium-8 columns content">
    <h3><?=__('Images')?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?=$this->Paginator->sort('id')?></th>
                <th scope="col"><?=$this->Paginator->sort('iso')?></th>
                <th scope="col"><?=$this->Paginator->sort('longitude')?></th>
                <th scope="col"><?=$this->Paginator->sort('aperture')?></th>
                <th scope="col"><?=$this->Paginator->sort('make')?></th>
                <th scope="col"><?=$this->Paginator->sort('model')?></th>
                <th scope="col"><?=$this->Paginator->sort('title')?></th>
                <th scope="col"><?=$this->Paginator->sort('exposure')?></th>
                <th scope="col"><?=$this->Paginator->sort('captured')?></th>
                <th scope="col"><?=$this->Paginator->sort('software')?></th>
                <th scope="col"><?=$this->Paginator->sort('src')?></th>
                <th scope="col"><?=$this->Paginator->sort('filesize')?></th>
                <th scope="col"><?=$this->Paginator->sort('created')?></th>
                <th scope="col"><?=$this->Paginator->sort('modified')?></th>
                <th scope="col" class="actions"><?=__('Actions')?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($images as $image): ?>
            <tr>
                <td><?=h($image->id)?></td>
                <td><?=h($image->iso)?></td>
                <td><?=h($image->longitude)?></td>
                <td><?=h($image->aperture)?></td>
                <td><?=h($image->make)?></td>
                <td><?=h($image->model)?></td>
                <td><?=h($image->title)?></td>
                <td><?=h($image->exposure)?></td>
                <td><?=h($image->captured)?></td>
                <td><?=h($image->software)?></td>
                <td><?=h($image->src)?></td>
                <td><?= isset($image->filesize) ? $this->Number->format($image->filesize) : '&dsah;' ?></td>
                <td><?=h($image->created)?></td>
                <td><?=h($image->modified)?></td>
                <td class="actions">
                    <?=$this->Html->link(__('View'), ['action' => 'view', $image->id])?>
                    <?=$this->Html->link(__('Edit'), ['action' => 'edit', $image->id])?>
                    <?=$this->Form->postLink(__('Delete'), ['action' => 'delete', $image->id], ['confirm' => __('Are you sure you want to delete # {0}?', $image->id)])?>
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
