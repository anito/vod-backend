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
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App;

use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Identifier\AbstractIdentifier;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Core\Exception\MissingPluginException;
use Cake\Error\Debugger;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\I18n\Middleware\LocaleSelectorMiddleware;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Utility\Security;
use CorsMiddleware\Middleware\CorsMiddleware;
use Muffin\Footprint\Middleware\FootprintMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
  /**
   * {@inheritDoc}
   */
  public function bootstrap(): void
  {
    // Call parent to load bootstrap from files.
    parent::bootstrap();

    /**
     * passes the currently logged in user info to the model layer
     * see: https://github.com/UseMuffin/Footprint
     */
    $this->addPlugin('Muffin/Footprint');

    $this->addPlugin('Authentication');

    $this->addPlugin('Crud');

    if (PHP_SAPI === 'cli') {
      try {
        $this->addPlugin('Bake');
      } catch (MissingPluginException $e) {
        // Do not halt if the plugin is missing
      }

      $this->addPlugin('Migrations');
    }
  }

  /**
   * Returns a service provider instance.
   *
   * @param \Psr\Http\Message\ServerRequestInterface $request Request
   * @param \Psr\Http\Message\ResponseInterface $response Response
   * @return \Authentication\AuthenticationServiceInterface
   */
  public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
  {
    $path = $request->getUri()->getPath();

    $fields = [
      AbstractIdentifier::CREDENTIAL_USERNAME => 'email',
      AbstractIdentifier::CREDENTIAL_PASSWORD => 'password',
    ];
    $resolver = [
      'className' => 'Authentication.Orm',
      'userModel' => 'Users', // default
      'finder' => 'active'
    ];

    $service = new AuthenticationService();

    if (strpos($path, '/api/v1') === 0) {
      $service->loadAuthenticator('Authentication.Form', [
        'fields' => $fields,
        // 'loginUrl' => '/v1/users/login', // ommit if additional actions (e.g. /users/token) use form authentication
      ]);
      $service->loadIdentifier('Authentication.Password', compact('fields'));

      // Additionally accept API JWT tokens
      $service->loadAuthenticator('Authentication.Jwt', [
        'header' => 'Authorization',
        'queryParam' => 'token',
        'tokenPrefix' => 'Bearer',
        'algorithm' => 'HS256',
        // 'secretKey' => Security::getSalt(),
        'secretKey' => getPublicKey(),
        'returnPayload' => false
      ]);
      $service->loadIdentifier('Authentication.JwtSubject', compact('resolver'));

      return $service;
    }

    $service->setConfig([
      'queryParam' => 'redirect',
      'unauthenticatedRedirect' => '/users/login',
    ]);

    // Load the authenticators, you want session first
    $service->loadAuthenticator('Authentication.Session');
    $service->loadAuthenticator('Authentication.Form', [
      'fields' => $fields,
      'loginUrl' => '/users/login',
    ]);
    $service->loadIdentifier('Authentication.Password', compact('fields', 'resolver'));

    return $service;
  }

  /**
   * Setup the middleware queue your application will use.
   *
   * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
   * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
   */
  public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
  {
    $middlewareQueue
      ->add(new CorsMiddleware(Configure::read('App.cors')))
      // Catch any exceptions in the lower layers,
      // and make an error page/response
      ->add(new ErrorHandlerMiddleware(Configure::read('Error')))

      // Handle plugin/theme assets like CakePHP normally does.
      ->add(new AssetMiddleware([
        'cacheTime' => Configure::read('Asset.cacheTime'),
      ]))

      // Add routing middleware.
      // Routes collection cache enabled by default, to disable route caching
      // pass null as cacheConfig, example: `new RoutingMiddleware($this)`
      // you might want to disable this cache in case your routing is extremely simple
      // ->add(new RoutingMiddleware($this, '_cake_routes_'))
      ->add(new RoutingMiddleware($this))

      // Parse various types of encoded request bodies so that they are
      // available as array through $request->getData()
      // https://book.cakephp.org/4/en/controllers/middleware.html#body-parser-middleware
      ->add(new BodyParserMiddleware())

      // Cross Site Request Forgery (CSRF) Protection Middleware
      // https://book.cakephp.org/4/en/controllers/middleware.html#cross-site-request-forgery-csrf-middleware
      // ->add(new CsrfProtectionMiddleware([
      //     'httponly' => true,
      // ]))

      // Authentication should be added *after* RoutingMiddleware.
      // So that subdirectory information and routes are loaded.
      ->add(new AuthenticationMiddleware($this))

      ->add(new FootprintMiddleware())

      ->add(new LocaleSelectorMiddleware(['en-US', 'de-DE']));

    return $middlewareQueue;
  }

  /**
   * Register application container services.
   *
   * @param \Cake\Core\ContainerInterface $container The Container to update.
   * @return void
   * @link https://book.cakephp.org/5/en/development/dependency-injection.html#dependency-injection
   */
  public function services(ContainerInterface $container): void
  {
  }

  public function routes($routes): void
  {
    // $_defaultConfig = [
    //     'cookieName' => 'csrfToken',
    //     'expiry' => 0,
    //     'secure' => false,
    //     'httpOnly' => false,
    //     'field' => '_csrfToken',
    // ];
    $options = [];
    $routes->registerMiddleware('csrf', new CsrfProtectionMiddleware($options));
    parent::routes($routes);
  }
}
