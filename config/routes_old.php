<?php

/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

/**
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass()`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 *
 * Cache: Routes are cached to improve performance, check the RoutingMiddleware
 * constructor in your `src/Application.php` file to change this behavior.
 *
 */
$routes->setRouteClass(DashedRoute::class);

$routes->scope('/', function (RouteBuilder $builder): void {
  /**
   * Here, we are connecting '/' (base path) to a controller called 'Pages',
   * its action called 'display', and we pass a param to select the view file
   * to use (in this case, templates/Pages/home.php)...
   */
  $builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

  /**
   * ...and connect the rest of 'Pages' controller's URLs.
   */
  $builder->connect('/pages/*', 'Pages::display');
  $builder->connect('/pages/*', ['controller' => 'Pages', 'action' => 'display']);
  $builder->connect('/login', array('controller' => 'Users', 'action' => 'login'));
  $builder->connect('/logout', array('controller' => 'Users', 'action' => 'logout'));
  $builder->connect('/register', array('controller' => 'Users', 'action' => 'add'));

  /**
   * Connect catchall routes for all controllers.
   *
   * Using the argument `DashedRoute`, the `fallbacks` method is a shortcut for
   *
   * ```
   * $builder->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);
   * $builder->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);
   * ```
   *
   * Any route class can be used with this method, such as:
   * - DashedRoute
   * - InflectedRoute
   * - Route
   * - Or your own route class
   *
   * You can remove these routes once you've connected the
   * routes you want in your application.
   */
  $builder->fallbacks(DashedRoute::class);
});

$routes->scope('/api', function (RouteBuilder $builder) {

  $builder->prefix('v1', function (RouteBuilder $builder) {
    // Only controllers explicitly enabled for API use will be accessible through API
    $builder->setExtensions(['json', 'xml']);

    $builder->resources('Avatars');
    $builder->resources('EmailTemplates');
    $builder->resources('Images');
    $builder->resources('Inboxes');
    $builder->resources('Sents');
    $builder->resources('Settings');
    $builder->resources('Tokens');
    $builder->resources('Templates');
    $builder->resources('Users');
    $builder->resources('Videos');
    $builder->resources('Screenshots');

    $builder->connect('/login', ['controller' => 'Users', 'action' => 'login']);
    $builder->connect('/google/*', ['controller' => 'Users', 'action' => 'google']);
    $builder->connect('/facebook/*', ['controller' => 'Users', 'action' => 'facebook']);
    $builder->connect('/logout', ['controller' => 'Users', 'action' => 'logout']);
    $builder->connect('/videos/all', ['controller' => 'Videos', 'action' => 'index', 'all']);
    $builder->connect('/q/{crypt}/{timestamp}/*', ['controller' => 'Kodaks', 'action' => 'process']);
    $builder->connect('/register', ['controller' => 'Users', 'action' => 'add']);
    $builder->connect('/u/v/*', ['controller' => 'Videos', 'action' => 'uri']);
    $builder->connect('/u/i/*', ['controller' => 'Images', 'action' => 'uri']);
    $builder->connect('/u/a/*', ['controller' => 'Avatars', 'action' => 'uri']);
    $builder->connect('/u/s/*', ['controller' => 'Screenshots', 'action' => 'uri']);

    $builder->fallbacks(DashedRoute::class);
  });
});

/**
 * If you need a different set of middleware or none at all,
 * open new scope and define routes there.
 *
 * ```
 * Router::scope('/api', function (RouteBuilder $builder) {
 *     // No $routes->applyMiddleware() here.
 *     // Connect API actions here.
 * });
 * ```
 */
