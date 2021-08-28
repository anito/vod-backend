<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Message[]|\Cake\Collection\CollectionInterface $messages
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?=__('Actions')?></li>
        <li><?=$this->Html->link(__('New Message'), ['action' => 'add'])?></li>
        <li><?=$this->Html->link(__('List Inboxes'), ['controller' => 'Inboxes', 'action' => 'index'])?></li>
        <li><?=$this->Html->link(__('New Inbox'), ['controller' => 'Inboxes', 'action' => 'add'])?></li>
        <li><?=$this->Html->link(__('List Sents'), ['controller' => 'Sents', 'action' => 'index'])?></li>
        <li><?=$this->Html->link(__('New Sent'), ['controller' => 'Sents', 'action' => 'add'])?></li>
    </ul>
</nav>
<div class="messages index large-9 medium-8 columns content">
    <h3><?=__('Messages')?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?=$this->Paginator->sort('id')?></th>
                <th scope="col"><?=$this->Paginator->sort('message')?></th>
                <th scope="col"><?=$this->Paginator->sort('inbox_id')?></th>
                <th scope="col"><?=$this->Paginator->sort('mail_id')?></th>
                <th scope="col"><?=$this->Paginator->sort('created')?></th>
                <th scope="col"><?=$this->Paginator->sort('modified')?></th>
                <th scope="col" class="actions"><?=__('Actions')?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $message): ?>
            <tr>
                <td><?=h($message->id)?></td>
                <td>
                    <iframe id="<?=h($message->id)?>" src="" frameborder="0" data-html="<?=h($message->message['message'])?>" ></iframe>
                </td>
                <td><?=$message->has('inbox') ? $this->Html->link($message->inbox->id, ['controller' => 'Inboxes', 'action' => 'view', $message->inbox->id]) : ''?></td>
                <td><?=$message->has('sent') ? $this->Html->link($message->sent->id, ['controller' => 'Sents', 'action' => 'view', $message->sent->id]) : ''?></td>
                <td><?=h($message->created)?></td>
                <td><?=h($message->modified)?></td>
                <td class="actions">
                    <?=$this->Html->link(__('View'), ['action' => 'view', $message->id])?>
                    <?=$this->Html->link(__('Edit'), ['action' => 'edit', $message->id])?>
                    <?=$this->Form->postLink(__('Delete'), ['action' => 'delete', $message->id], ['confirm' => __('Are you sure you want to delete # {0}?', $message->id)])?>
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

<script>
  window.addEventListener('load', () => {
    let iFrames = document.documentElement.getElementsByTagName('iframe');
    for(let iFrame of iFrames) {
      if(iFrame.dataset.html) {
          iFrame.contentDocument.open();
          iFrame.contentDocument.write(iFrame.dataset.html);
          iFrame.contentDocument.close();
      }
    }
  })
</script>