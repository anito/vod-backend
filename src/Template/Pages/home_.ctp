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
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Error\Debugger;
use Cake\Http\Exception\NotFoundException;
use Cake\Log\Log;

$this->layout = 'db_backup_layout';

$cakeDescription = 'CakePHP: the rapid development PHP framework';

?>
<div itemscope itemtype="http://schema.org/SoftwareApplication" class="container">
    <div class="backup-info" id="time-info"></div>
    <header class="jumbotron masthead">
        <div id="status"></div>
        <div class="badge-logo"></div>
        <div class="inner">
            <h1>Datenbank</h1>
            <p>Backup Tool</p>
            <?php echo $this->Form->create('User', array('url' => '/u')); ?>
                <table class="download-info button-wrap">
                    <tr style="text-align: center">
                        <td>
                            <?php echo $this->Form->hidden( 'filename', array( 'value' => 'test.sql')) ?>
                            <?php echo $this->Form->hidden( 'description', array( 'value' => 'Test Description')) ?>
                            <?php echo $this->Form->button('<i class="glyphicon glyphicon-download"></i><span>Sichern</span>', array(
                                'id'        => 'opt-dump',
                                'title'       => 'aktuellen Datenstand sichern',
                                'href'      => '#',
                                'type'      => 'button',
                                'data-href' => DIR_HOST.'/api/mysql/mysql/dump?redirect=' . urlencode($this->request->getQuery('redirect')),
                                'data-type'   => 'dump',
                                'class'     => ['btn', 'btn-warning', 'btn-large', 'ask'], 
                                'target'    => "_self",
                                'label'     => array(TRUE),
                                'tabindex'  => 10
                            )); ?>
                            <i class="info"></i>
                        </td>
                    </tr>
                    <tr class="" style="text-align: center">
                        <td colspan="2">
                            <div class="filebox">
                                <table class="">
                                    <tr style="text-align: center">
                                        <td colspan="2">
                                            <?php
                                            echo $this->Form->control('fn', array(
                                                'id'        => 'opt-options',
                                                'options'   => [],
                                                'empty' => 'Loading...',
                                                'label'     => FALSE,
                                                'tabindex'  => 20
                                            )); ?>
                                        </td>
                                    </tr>
                                    <tr style="text-align: center">
                                        <td class="control-container">
                                            <?php echo $this->Form->button('<i class="glyphicon glyphicon-upload"></i><span>Wiederherstellen</span>', array(
                                                'id'        => 'opt-restore',
                                                'title'     => 'ausgewählten Datenstand wiedererstellen',
                                                'href'      => '#',
                                                'type'      => 'button',
                                                'data-href' => DIR_HOST.'/api/mysql/restore/?redirect=' . urlencode($this->request->getQuery('redirect')),
                                                'data-type' => 'restore',
                                                'class'     => array('btn', 'btn-danger', 'btn-large', 'ask'),
                                                'target'    => "_self",
                                                'label'     => array(),
                                                'tabindex'  => 30
                                            )); ?>
                                            <i class="info"></i>
                                        </td>
                                    </tr>
                                    <tr style="text-align: center">
                                        <td colspan="2">
                                            <?php echo $this->Form->button('<i class="glyphicon glyphicon-save"></i><span>Download</span>', array(
                                                'id'        => 'opt-download',
                                                'title'     => 'ausgewählten Datenstand herunterladen',
                                                'type'      =>'submit',
                                                'class'     => array('btn', 'btn-info', 'btn-large'),
                                                'label'     => array(),
                                                'tabindex'  => 40
                                            )); ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr style="text-align: center">
                        <td colspan="1">
                            <a href="<?php echo DIR_HOST; ?>/logout" id="opt-logout" class="btn btn-success btn-large" type="submit" target="_self" tabindex="50">
                                <i class="glyphicon glyphicon-log-out"></i>
                                <span itemprop="name">Logout</span>
                            </a>
                            <div class="settings-container">
                                <div class="settings settings-edit trigger-menu-on-click">
                                    <a class="dropdown">
                                        <i class="glyphicon glyphicon-cog"></i>
                                    </a>
                                    <div class="settings-dropdown">
                                        <div class="settings-dropdown-b">
                                            <div class="settings-dropdown-arr">
                                                <i class="glyphicon glyphicon-chevron-up"></i>
                                            </div>
                                            <ul>
                                                <li>
                                                    <a href="<?= sprintf('/users/edit/%s', $user['id']); ?>" target="_self" class="settings">Einstellungen</a>
                                                </li>
                                                <li>
                                                    <a href="#" class="settings-dropdown-hide">Abbrechen</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </header>
</div>

<script type="text/x-jquery-tmpl" id="statusTemplate">
  <label>
    {{if status}}
    <span style="display: block;">${statusText} ${status}</span>
    {{else}}
    keine Statusmeldungen  
    {{/if}}
  </label>
</script>

<script type="text/x-jquery-tmpl" id="optionsTemplate">
    {{tmpl($item.data) "#optionsSelectTemplate"}}
</script>

<script type="text/x-jquery-tmpl" id="optionsSelectTemplate">
    <option value="${filename}">${created}</option>
</script>

<script type="text/x-jquery-tmpl" id="timeInfoTemplate">
    {{if human}}
    Letztes Backup vor <i>${human.total} ${human.name}</i><span> am ${created}</span>
    {{else}}
    Letztes Backup: <span style="color: #f00;"><strong>Noch kein Backup vorhanden!</strong></span>
    {{/if}}
  </label>
</script>