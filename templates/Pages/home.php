<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.10.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Error\Debugger;
use Cake\Http\Exception\NotFoundException;

$this->disableAutoLayout();

$cakeDescription = 'CakePHP: the rapid development PHP framework';
?>
<!DOCTYPE html>
<html>
<head>
    <?=$this->Html->charset()?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?=$cakeDescription?>:
        <?=$this->fetch('title')?>
    </title>
    <?=$this->Html->meta('icon')?>

    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">

    <?=$this->Html->css(['normalize.min', 'milligram.min', 'cake', 'home'])?>

    <?=$this->fetch('meta')?>
    <?=$this->fetch('css')?>
    <?=$this->fetch('script')?>
</head>
<body>
    <header>
        <div class="container text-center">
            <a href="https://cakephp.org/" target="_blank" rel="noopener">
                <img alt="CakePHP" src="https://cakephp.org/v2/img/logos/CakePHP_Logo.svg" width="350" />
            </a>
            <h1>
                VoD Manager Backend API
            </h1>
            <h5>
                    CakePHP Version <?=Configure::version()?> Strawberry (🍓)
            </h5>
        </div>
    </header>
    <main class="main">
        <div class="container">
            <div class="content">
                <div class="row">
                    <div class="column">
                        <div class="message default text-center">
                            <small>System Check</small>
                        </div>
                        <?php Debugger::checkSecurityKeys();?>
                    </div>
                </div>
                <div class="row">
                    <div class="column">
                        <h4>Environment</h4>
                        <ul>
                        <?php if (version_compare(PHP_VERSION, '7.2.0', '>=')): ?>
                            <li class="bullet success">Your version of PHP is 7.2.0 or higher (detected <?=PHP_VERSION?>).</li>
                        <?php else: ?>
                            <li class="bullet problem">Your version of PHP is too low. You need PHP 7.2.0 or higher to use CakePHP (detected <?=PHP_VERSION?>).</li>
                        <?php endif;?>

                        <?php if (extension_loaded('mbstring')): ?>
                            <li class="bullet success">Your version of PHP has the mbstring extension loaded.</li>
                        <?php else: ?>
                            <li class="bullet problem">Your version of PHP does NOT have the mbstring extension loaded.</li>
                        <?php endif;?>

                        <?php if (extension_loaded('openssl')): ?>
                            <li class="bullet success">Your version of PHP has the openssl extension loaded.</li>
                        <?php elseif (extension_loaded('mcrypt')): ?>
                            <li class="bullet success">Your version of PHP has the mcrypt extension loaded.</li>
                        <?php else: ?>
                            <li class="bullet problem">Your version of PHP does NOT have the openssl or mcrypt extension loaded.</li>
                        <?php endif;?>

                        <?php if (extension_loaded('intl')): ?>
                            <li class="bullet success">Your version of PHP has the intl extension loaded.</li>
                        <?php else: ?>
                            <li class="bullet problem">Your version of PHP does NOT have the intl extension loaded.</li>
                        <?php endif;?>
                        </ul>
                    </div>
                    <div class="column">
                        <h4>Filesystem</h4>
                        <ul>
                        <?php if (is_writable(TMP)): ?>
                            <li class="bullet success">Your tmp directory is writable.</li>
                        <?php else: ?>
                            <li class="bullet problem">Your tmp directory is NOT writable.</li>
                        <?php endif;?>

                        <?php if (is_writable(LOGS)): ?>
                            <li class="bullet success">Your logs directory is writable.</li>
                        <?php else: ?>
                            <li class="bullet problem">Your logs directory is NOT writable.</li>
                        <?php endif;?>

                        <?php $settings = Cache::getConfig('_cake_core_');?>
                        <?php if (!empty($settings)): ?>
                            <li class="bullet success">The <em><?=$settings['className']?>Engine</em> is being used for core caching. To change the config edit config/app.php</li>
                        <?php else: ?>
                            <li class="bullet problem">Your cache is NOT working. Please check the settings in config/app.php</li>
                        <?php endif;?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
