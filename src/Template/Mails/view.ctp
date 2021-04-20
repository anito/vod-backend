<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Mail $mail
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Mail'), ['action' => 'edit', $mail->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Mail'), ['action' => 'delete', $mail->id], ['confirm' => __('Are you sure you want to delete # {0}?', $mail->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Mails'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Mail'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="mails view large-9 medium-8 columns content">
    <h3><?= h($mail->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= h($mail->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('User') ?></th>
            <td><?= $mail->has('user') ? $this->Html->link($mail->user->name, ['controller' => 'Users', 'action' => 'view', $mail->user->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __(' To') ?></th>
            <td><?= h($mail->_to) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __(' From') ?></th>
            <td><?= h($mail->_from) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Message') ?></th>
            <td>
                <iframe id="<?= h($mail->id)?>" src="" frameborder="0" data-html="<?= h($mail->message['message'])?>" ></iframe>
            </td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($mail->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($mail->modified) ?></td>
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