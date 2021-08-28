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

$cakeDescription = 'CakePHP: the rapid development php framework';
$title = $this->fetch('title');
?>
<!DOCTYPE html>
<html>
<head>
    <?=$this->Html->charset()?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?=$cakeDescription?>:
        <?=$this->fetch('title')?>
    </title>
    <?=$this->Html->meta('icon')?>

    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">

    <?=$this->Html->css(['normalize.min', 'milligram.min', 'cake', 'base', 'style'])?>

    <?=$this->fetch('meta')?>
    <?=$this->fetch('css')?>
    <?=$this->fetch('script')?>
</head>
<body>
    <nav class="top-bar expanded" data-topbar role="navigation">
        <ul class="title-area large-3 medium-4 columns">
            <li class="name">
                <h1><a href=""><?=__($title)?></a></h1>
            </li>
            <li class="logout">
                <?php
if ($this->Identity->isLoggedIn()) {
    $user = $this->request->getAttribute('identity')->get('User');
    echo $this->Html->link(__('Logout') . ' (' . $user['email'] . ')', '/logout', array('class' => 'success'));
} else {
    echo $this->Html->link(__('Login'), '/login', array('class' => 'success'));
}
?>
            </li>
        </ul>
        <div class="top-bar-section">
            <ul class="right">
                <li><a target="_blank" rel="noopener" href="https://book.cakephp.org/3.0/">Documentation</a></li>
                <li><a target="_blank" rel="noopener" href="https://api.cakephp.org/3.0/">API</a></li>
            </ul>
        </div>
    </nav>
    <?=$this->Flash->render()?>
    <div class="container">
        <?=$this->Flash->render()?>
        <?=$this->fetch('content')?>
    </div>
    <footer>
    </footer>
</body>
</html>
