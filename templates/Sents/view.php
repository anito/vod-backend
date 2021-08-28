<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Sent $sent
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?=__('Actions')?></li>
        <li><?=$this->Html->link(__('Edit Sent'), ['action' => 'edit', $sent->id])?> </li>
        <li><?=$this->Form->postLink(__('Delete Sent'), ['action' => 'delete', $sent->id], ['confirm' => __('Are you sure you want to delete # {0}?', $sent->id)])?> </li>
        <li><?=$this->Html->link(__('List Sents'), ['action' => 'index'])?> </li>
        <li><?=$this->Html->link(__('New Sent'), ['action' => 'add'])?> </li>
        <li><?=$this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index'])?> </li>
        <li><?=$this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add'])?> </li>
    </ul>
</nav>
<div class="sents view large-9 medium-8 columns content">
    <h3><?=h($sent->id)?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?=__('Id')?></th>
            <td><?=h($sent->id)?></td>
        </tr>
        <tr>
            <th scope="row"><?=__('User')?></th>
            <td><?=$sent->has('user') ? $this->Html->link($sent->user->name, ['controller' => 'Users', 'action' => 'view', $sent->user->id]) : ''?></td>
        </tr>
        <tr>
            <th scope="row"><?=__(' To')?></th>
            <td><?=h($sent->_to)?></td>
        </tr>
        <tr>
            <th scope="row"><?=__(' From')?></th>
            <td><?=h($sent->_from)?></td>
        </tr>
        <tr>
            <th scope="row"><?=__('Created')?></th>
            <td><?=h($sent->created)?></td>
        </tr>
        <tr>
            <th scope="row"><?=__('Modified')?></th>
            <td><?=h($sent->modified)?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?=__('Message')?></h4>
        <div class="iframe-container">
            <iframe id="<?=h($sent->id)?>" data-id="<?=h($sent->id)?>" src="" frameborder="0" data-message="<?=h($sent->message)?>" ></iframe>
        </div>
    </div>
</div>

<style>
    .iframe-container {
        display: flex;
    }
    iframe {
        flex: 1;
        height: 500px;
    }
</style>

<script>
    window.addEventListener('load', () => {
    let iFrames = document.documentElement.getElementsByTagName('iframe');
    for(let iFrame of iFrames) {
      if(iFrame.dataset.id === iFrame.getAttribute('id')) {
          let html = JSON.parse(iFrame.dataset.message).message;
          iFrame.contentDocument.open();
          iFrame.contentDocument.write(html);
          iFrame.contentDocument.close();
      }
    }
  })
</script>