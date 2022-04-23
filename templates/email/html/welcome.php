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

use Cake\Core\Configure;

$content = explode("\n", $content);
$magicLinkLabel = Configure::check('Site.magic-link-label') ? Configure::read('Site.magic-link-label') : 'Magic Link';

?>

<!-- main start -->
<div>
  <!-- start title -->
  <div
    class="layout one-col fixed-width has-border stack"
    style="
          margin: 0 auto;
          max-width: 602px;
          min-width: 322px;
          width: 322px;
          width: calc(28000% - 167398px);
          overflow-wrap: break-word;
          word-wrap: break-word;
          word-break: break-word;
        "
  >
    <div
      class="layout__inner"
      style="
            border-collapse: collapse;
            display: table;
            width: 100%;
            background-color: #8c1f76;
            border-top: 1px solid #8c1f76;
            border-right: 1px solid #8c1f76;
            border-bottom: 0 none white;
            border-left: 1px solid #8c1f76;
          "
    >
      <div
        class="column"
        style="
              text-align: left;
              color: #8e959c;
              font-size: 14px;
              line-height: 21px;
              font-family: sans-serif;
            "
      >
        <div style="margin-left: 20px; margin-right: 20px">
          <div style="line-height: 20px; font-size: 1px">&nbsp;</div>
        </div><!-- --1-- -->

        <div style="margin-left: 20px; margin-right: 20px">
          <div style="vertical-align: middle">
            <h3
              style="
                    margin-top: 0;
                    margin-bottom: 12px;
                    font-style: normal;
                    font-weight: normal;
                    color: #281557;
                    font-size: 18px;
                    line-height: 26px;
                    font-family: arial, sans-serif;
                  "
            >
              <span class="font-arial"><span style="color: #fff"><?php echo $subject; ?></span></span>
            </h3>
          </div>
        </div>

        <div style="margin-left: 20px; margin-right: 20px">
          <div style="line-height: 20px; font-size: 1px">&nbsp;</div>
        </div>
      </div>
    </div>
  </div>
  <!-- title end -->
  <!-- content start -->
  <div
    class="layout one-col fixed-width has-border stack"
    style="
          margin: 0 auto;
          max-width: 602px;
          min-width: 322px;
          width: 322px;
          width: calc(28000% - 167398px);
          overflow-wrap: break-word;
          word-wrap: break-word;
          word-break: break-word;
        "
  >
    <div
      class="layout__inner"
      style="
            border-collapse: collapse;
            display: table;
            width: 100%;
            background-color: #fff;
            border-top: 0 none white;
            border-right: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            border-left: 1px solid #ccc;
          "
    >
      <div
        class="column"
        style="
              text-align: left;
              color: #8e959c;
              font-size: 14px;
              line-height: 21px;
              font-family: sans-serif;
            "
      >
        <div style="margin-left: 20px; margin-right: 20px">
          <div style="line-height: 40px; font-size: 1px">&nbsp;</div>
        </div>
        <div class="content">
          <div class="starter"><?php echo 'Hallo ' . $name . ',' ?></div>
          <?php if (!empty($beforeContent)): ?>
            <p style="margin-top: 0; margin-bottom: 30"><?php echo $beforeContent ?></p>
          <?php endif;?>
          <?php
foreach ($content as $line):
    echo '<p> ' . $line . "</p>";
endforeach;
?>
          <?php if (!empty($templateData)): ?>
            <p style="line-height: 40px; font-size: 1px">&nbsp;</p>
            <div class="content magic-link">
            <a href="<?php echo $templateData["href"] ?>" target="_blank" title="<?php echo $templateData["label"] ?>"><?php echo $templateData["label"] ?></a>
            </div>
            <p style="line-height: 40px; font-size: 1px">&nbsp;</p>
          <?php endif;?>
          <?php if (!empty($afterContent)): ?>
            <p style="margin-top: 0; margin-bottom: 0"><?php echo $afterContent ?></p>
          <?php endif;?>
        </div>
        <p style="line-height: 40px; font-size: 1px">&nbsp;</p>
      </div>
    </div>
  </div>
  <!-- content end -->
  <!-- footer start -->
  <div
    class="layout one-col fixed-width stack"
    style="
          margin: 0 auto;
          max-width: 600px;
          min-width: 320px;
          width: 320px;
          width: calc(28000% - 167400px);
          overflow-wrap: break-word;
          word-wrap: break-word;
          word-break: break-word;
        "
  >
    <div
      class="layout__inner"
      style="border-collapse: collapse; display: table; width: 100%; background-color: #fff"
    >
      <div
        class="column"
        style="
              text-align: left;
              color: #8e959c;
              font-size: 14px;
              line-height: 21px;
              font-family: sans-serif;
            "
      >
        <div style="margin-left: 20px; margin-right: 20px">
          <div style="line-height: 38px; font-size: 1px">&nbsp;</div>
        </div>

        <div style="margin-left: 20px; margin-right: 20px">
          <div style="vertical-align: middle">
            <p style="margin-top: 0; margin-bottom: 0">
            <?php if (!empty($beforeSitename)): ?>
              <span><?php echo $beforeSitename ?></span>&nbsp;
            <?php endif;?>
            <?php if (!empty($sitename)): ?>
              <span><?php echo $sitename ?></span>
            <?php endif;?>
            <?php if (!empty($afterSitename)): ?>
              &nbsp;<span><?php echo $afterSitename ?></span>
            <?php endif;?>
            </p>
          </div>
        </div>

        <div style="margin-left: 20px; margin-right: 20px">
          <div style="line-height: 20px; font-size: 1px">&nbsp;</div>
        </div>
      </div>
    </div>
  </div>
  <!-- footer end -->
  <div style="line-height: 20px; font-size: 20px">&nbsp;</div>
</div>
<!-- main end -->

<style>
  .content {
    margin: 0 20px;
  }
  .starter {
    margin-bottom: 30px;
  }
  .magic-link {
    background-color: #8c1f76;
    color: #ffffff;
    padding: 20px;
    vertical-align: middle;
    text-align: center
  }
  .magic-link a {
      color: #ffffff;
  }
</style>