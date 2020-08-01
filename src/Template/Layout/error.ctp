<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <title>
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css('base.css') ?>
    <?= $this->Html->css('twitter/bootstrap/css/bootstrap'); ?>
    <?= $this->Html->css('bootstrap_glyphicons'); ?>
    <?= $this->Html->css('style.css') ?>
    <?= $this->Html->css('app.css') ?>

    <?= '<style>.badge-logo::before {background-image: url(' . logo_url() . ')}</style>'; ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <div itemscope itemtype="http://schema.org/SoftwareApplication" class="container views">
        <div class="jumbotron masthead view">
            <div class="inner container">
                <div class="badge-logo"></div>
                <h1><?= __('Error') ?></h1>
                <div id="content">
                    <?= $this->Flash->render() ?>
                    <?= $this->fetch('content') ?>
                </div>
            </div>
            <div id="footer">
                <?= $this->Html->link(__('Back'), 'javascript:history.back()') ?>
            </div>
        </div>
    </div>
</body>
</html>
