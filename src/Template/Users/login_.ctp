<div class="views">
    <div id="login" class="login-view view">
        <div itemscope itemtype="http://schema.org/SoftwareApplication" class="container">
            <header class="jumbotron masthead">
                <div class="inner container">
                    <div class="badge-logo"></div>
                    <h1>Login</h1>
                    <?php echo $this->Form->create('User', array('onsubmit' => 'Login.submit(); return false;')); ?>
                    <?php echo $this->Form->hidden('redirect', array('value' => $this->request->getQuery('redirect'))); ?>
                    <table class="download-info button-wrap">
                        <tr style="text-align: center">
                            <td>
                                <div class="flash"><?= $this->Flash->render() ?></div>
                            </td>
                        </tr>
                        <tr style="text-align: center">
                            <td>
                                <div class="status"></div>
                            </td>
                        </tr>
                        <tr class="" style="text-align: center">
                            <td>
                                <div class="filebox">
                                    <table class="">
                                        <tr style="text-align: center">
                                            <td>
                                                <?php

                                                echo $this->Form->control('username', array(
                                                    'div'       => FALSE,
                                                    'placeholder'     => __('User'),
                                                    'autofocus' => 'autofocus',
                                                    'label'     => FALSE,
                                                    'tabindex'  => 1
                                                )); ?>
                                            </td>
                                        </tr>
                                        <tr style="text-align: center">
                                            <td>
                                                <?php

                                                echo $this->Form->control('password', array(
                                                    'div'       => FALSE,
                                                    'placeholder'   => __('Password'),
                                                    'type'          => 'password',
                                                    'label'         => FALSE,
                                                    'tabindex'      => 2
                                                )); ?>
                                            </td>
                                        </tr>
                                        <tr style="text-align: center">
                                            <td>
                                                <?php echo $this->Form->button('<i class="glyphicon glyphicon-log-in"></i><span>  Login</span>', array( 'type'=>'submit', 'class' => 'btn btn-success btn-large', 'label' => array( TRUE ) ) ); ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <?php echo $this->Form->end(); ?>
                    <div class="centered">
                        <?php echo $this->Html->link( __('Register'), '/register'); ?>
                    </div>
                </div>
            </header>
        </div>
    </div>
    <div id="loader" class="container loader-view view">
        <div class="dialogue-wrap">
            <div class="dialogue">
              <div class="dialogue-content">
                  <div class="status-symbol" style="z-index: 2;">
                    <svg class="lds-gears" width="50%" height="50%" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid"><g transform="translate(50 50)"> <g transform="translate(-19 -19) scale(0.6)"> <g transform="rotate(59.1174)">
                        <animateTransform attributeName="transform" type="rotate" values="0;360" keyTimes="0;1" dur="1s" begin="0s" repeatCount="indefinite"></animateTransform><path d="M37.3496987939662 -7 L47.3496987939662 -7 L47.3496987939662 7 L37.3496987939662 7 A38 38 0 0 1 31.359972760794346 21.46047782418268 L31.359972760794346 21.46047782418268 L38.431040572659825 28.531545636048154 L28.531545636048154 38.431040572659825 L21.46047782418268 31.359972760794346 A38 38 0 0 1 7.0000000000000036 37.3496987939662 L7.0000000000000036 37.3496987939662 L7.000000000000004 47.3496987939662 L-6.999999999999999 47.3496987939662 L-7 37.3496987939662 A38 38 0 0 1 -21.46047782418268 31.35997276079435 L-21.46047782418268 31.35997276079435 L-28.531545636048154 38.431040572659825 L-38.43104057265982 28.531545636048158 L-31.359972760794346 21.460477824182682 A38 38 0 0 1 -37.3496987939662 7.000000000000007 L-37.3496987939662 7.000000000000007 L-47.3496987939662 7.000000000000008 L-47.3496987939662 -6.9999999999999964 L-37.3496987939662 -6.999999999999997 A38 38 0 0 1 -31.35997276079435 -21.460477824182675 L-31.35997276079435 -21.460477824182675 L-38.431040572659825 -28.531545636048147 L-28.53154563604818 -38.4310405726598 L-21.4604778241827 -31.35997276079433 A38 38 0 0 1 -6.999999999999992 -37.3496987939662 L-6.999999999999992 -37.3496987939662 L-6.999999999999994 -47.3496987939662 L6.999999999999977 -47.3496987939662 L6.999999999999979 -37.3496987939662 A38 38 0 0 1 21.460477824182686 -31.359972760794342 L21.460477824182686 -31.359972760794342 L28.531545636048158 -38.43104057265982 L38.4310405726598 -28.53154563604818 L31.35997276079433 -21.4604778241827 A38 38 0 0 1 37.3496987939662 -6.999999999999995 M0 -23A23 23 0 1 0 0 23 A23 23 0 1 0 0 -23" fill="#28292f"></path></g></g> <g transform="translate(19 19) scale(0.6)"> <g transform="rotate(278.383)">
                        <animateTransform attributeName="transform" type="rotate" values="360;0" keyTimes="0;1" dur="1s" begin="-0.0625s" repeatCount="indefinite"></animateTransform><path d="M37.3496987939662 -7 L47.3496987939662 -7 L47.3496987939662 7 L37.3496987939662 7 A38 38 0 0 1 31.359972760794346 21.46047782418268 L31.359972760794346 21.46047782418268 L38.431040572659825 28.531545636048154 L28.531545636048154 38.431040572659825 L21.46047782418268 31.359972760794346 A38 38 0 0 1 7.0000000000000036 37.3496987939662 L7.0000000000000036 37.3496987939662 L7.000000000000004 47.3496987939662 L-6.999999999999999 47.3496987939662 L-7 37.3496987939662 A38 38 0 0 1 -21.46047782418268 31.35997276079435 L-21.46047782418268 31.35997276079435 L-28.531545636048154 38.431040572659825 L-38.43104057265982 28.531545636048158 L-31.359972760794346 21.460477824182682 A38 38 0 0 1 -37.3496987939662 7.000000000000007 L-37.3496987939662 7.000000000000007 L-47.3496987939662 7.000000000000008 L-47.3496987939662 -6.9999999999999964 L-37.3496987939662 -6.999999999999997 A38 38 0 0 1 -31.35997276079435 -21.460477824182675 L-31.35997276079435 -21.460477824182675 L-38.431040572659825 -28.531545636048147 L-28.53154563604818 -38.4310405726598 L-21.4604778241827 -31.35997276079433 A38 38 0 0 1 -6.999999999999992 -37.3496987939662 L-6.999999999999992 -37.3496987939662 L-6.999999999999994 -47.3496987939662 L6.999999999999977 -47.3496987939662 L6.999999999999979 -37.3496987939662 A38 38 0 0 1 21.460477824182686 -31.359972760794342 L21.460477824182686 -31.359972760794342 L28.531545636048158 -38.43104057265982 L38.4310405726598 -28.53154563604818 L31.35997276079433 -21.4604778241827 A38 38 0 0 1 37.3496987939662 -6.999999999999995 M0 -23A23 23 0 1 0 0 23 A23 23 0 1 0 0 -23" fill="#898989"></path></g></g></g>
                    </svg>
                  </div>
              </div>
            </div>
        </div>
    </div>
</div>



<script type="text/x-jquery-tmpl" id="flashTemplate">
  {{if message}}
  {{html message}}
  {{/if}}
</script>

<script type="text/x-jquery-tmpl" id="statusTemplate">
  <label>
    {{if status}}
    <span style="display: block;">${statusText} ${status}</span>
    {{/if}}
  </label>
</script>
<!--{json: {flash: ...}} {error: {record: {}, xhr: {}, statusText: {}, error:{}}}-->
