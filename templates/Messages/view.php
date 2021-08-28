<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Message $message
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?=__('Actions')?></li>
        <li><?=$this->Html->link(__('Edit Message'), ['action' => 'edit', $message->id])?> </li>
        <li><?=$this->Form->postLink(__('Delete Message'), ['action' => 'delete', $message->id], ['confirm' => __('Are you sure you want to delete # {0}?', $message->id)])?> </li>
        <li><?=$this->Html->link(__('List Messages'), ['action' => 'index'])?> </li>
        <li><?=$this->Html->link(__('New Message'), ['action' => 'add'])?> </li>
        <li><?=$this->Html->link(__('List Inboxes'), ['controller' => 'Inboxes', 'action' => 'index'])?> </li>
        <li><?=$this->Html->link(__('New Inbox'), ['controller' => 'Inboxes', 'action' => 'add'])?> </li>
        <li><?=$this->Html->link(__('List Sents'), ['controller' => 'Sents', 'action' => 'index'])?> </li>
        <li><?=$this->Html->link(__('New Sent'), ['controller' => 'Sents', 'action' => 'add'])?> </li>
    </ul>
</nav>
<div class="messages view large-9 medium-8 columns content">
    <h3><?=h($message->id)?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?=__('Id')?></th>
            <td><?=h($message->id)?></td>
        </tr>
        <tr>
            <th scope="row"><?=__('Message')?></th>
            <td>
                <iframe id="<?=$message->id?>" src="" frameborder="0" data-html="<?=h($message->message['message'])?>" ></iframe>
            </td>
        </tr>
        <tr>
            <th scope="row"><?=__('Inbox')?></th>
            <td><?=$message->has('inbox') ? $this->Html->link($message->inbox->id, ['controller' => 'Inboxes', 'action' => 'view', $message->inbox->id]) : ''?></td>
        </tr>
        <tr>
            <th scope="row"><?=__('Sent')?></th>
            <td><?=$message->has('sent') ? $this->Html->link($message->sent->id, ['controller' => 'Sents', 'action' => 'view', $message->sent->id]) : ''?></td>
        </tr>
        <tr>
            <th scope="row"><?=__('Created')?></th>
            <td><?=h($message->created)?></td>
        </tr>
        <tr>
            <th scope="row"><?=__('Modified')?></th>
            <td><?=h($message->modified)?></td>
        </tr>
    </table>
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