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
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>

<head>
  <title><?= $this->fetch('title') ?></title>
</head>

<body class="theme-default">
  <table class="wrapper" style="border-collapse: collapse; table-layout: fixed; min-width: 320px; width: 100%; background-color: #fff" cellpadding="0" cellspacing="0" role="presentation">
    <tbody>
      <tr>
        <td>
          <!-- banner start -->
          <div role="banner">
            <div class="preheader" style="margin: 0 auto; max-width: 560px; min-width: 280px; width: 280px; width: calc(28000% - 167440px)">
              <div style="border-collapse: collapse; display: table; width: 100%">
                <div class="snippet" style="
                          display: table-cell;
                          float: left;
                          font-size: 12px;
                          line-height: 19px;
                          max-width: 280px;
                          min-width: 140px;
                          width: 140px;
                          width: calc(14000% - 78120px);
                          padding: 10px 0 5px 0;
                          color: #adb3b9;
                          font-family: sans-serif;
                        "></div>
                <div class="webversion" style="
                          display: table-cell;
                          float: left;
                          font-size: 12px;
                          line-height: 19px;
                          max-width: 280px;
                          min-width: 139px;
                          width: 139px;
                          width: calc(14100% - 78680px);
                          padding: 10px 0 5px 0;
                          text-align: right;
                          color: #adb3b9;
                          font-family: sans-serif;
                        "></div>
              </div>
            </div>
            <div class="header" style="margin: 0 auto; max-width: 600px; min-width: 320px; width: 320px; width: calc(28000% - 167400px)" id="emb-email-header-container">
              <div class="logo emb-logo-margin-box" style="
                        font-size: 26px;
                        line-height: 32px;
                        margin-top: 6px;
                        margin-bottom: 20px;
                        color: #c3ced9;
                        font-family: Roboto, Tahoma, sans-serif;
                        margin-left: 20px;
                        margin-right: 20px;
                      " align="center">
                <div class="logo-center" align="center" id="emb-email-header">
                  <img style="display: block; height: auto; width: 100%; border: 0; max-width: 264px" src="<?php echo $logo; ?>" alt="" width="264" />
                </div>
              </div>
            </div>
          </div>
          <!-- banner end -->
          <!-- main start -->
          <?= $this->fetch('content') ?>
          <!-- main end -->
        </td>
      </tr>
    </tbody>
  </table>
</body>

</html>

<style>
  .theme-default {
    --prime: <?= $prime; ?>;
  }

  @media only screen and (min-width: 620px) {
    .wrapper {
      min-width: 600px !important;
    }
  }

  table {
    border-collapse: collapse;
    table-layout: fixed;
  }

  * {
    line-height: inherit;
  }

  .preheader,
  .header,
  .layout,
  .column {
    transition: width 0.25s ease-in-out, max-width 0.25s ease-in-out;
  }

  .layout,
  div.header {
    max-width: 400px !important;
    -fallback-width: 95% !important;
    width: calc(100% - 20px) !important;
  }

  div.preheader {
    max-width: 360px !important;
    -fallback-width: 90% !important;
    width: calc(100% - 60px) !important;
  }

  .snippet,
  .webversion {
    float: none !important;
  }

  .stack .column {
    max-width: 400px !important;
    width: 100% !important;
  }

  .fixed-width.has-border {
    max-width: 402px !important;
  }

  .fixed-width.has-border .layout__inner {
    box-sizing: border-box;
  }

  .snippet,
  .webversion {
    width: 50% !important;
  }

  .ie .stack .column {
    display: table-cell;
    float: none !important;
  }

  .ie .fixed-width .layout__inner {
    border-left: 0 none white !important;
    border-right: 0 none white !important;
  }

  @media only screen and (min-width: 620px) {
    .column {
      display: table-cell;
      float: none !important;
      vertical-align: top;
    }

    div.preheader,
    .email-footer {
      max-width: 560px !important;
      width: 560px !important;
    }

    .snippet,
    .webversion {
      width: 280px !important;
    }

    div.header,
    .layout,
    .one-col .column {
      max-width: 600px !important;
      width: 600px !important;
    }

    .fixed-width.has-border {
      max-width: 602px !important;
      width: 602px !important;
    }

    .column.narrow {
      max-width: 200px !important;
      width: 200px !important;
    }

    .column.wide {
      width: 400px !important;
    }
  }

  @supports (display: flex) {
    @media only screen and (min-width: 620px) {
      .fixed-width.has-border .layout__inner {
        display: flex !important;
      }
    }
  }

  @media (max-width: 321px) {
    .fixed-width.has-border .layout__inner {
      border-width: 1px 0 !important;
    }

    .layout,
    .stack .column {
      min-width: 320px !important;
      width: 320px !important;
    }
  }
</style>