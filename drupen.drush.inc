<?php

/**
 * @file
 * A few helpful Drush commands for security auditing.
 */

use Drush\Log\LogLevel;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouteCollection;


define("DRUPEN_STRING_SEPERATOR", "~~~");

/**
 * Implementation of hook_drush_help().
 */
function drupen_drush_help($section) {
  switch ($section) {
    case 'meta:drupen:title':
      return dt('Drupen');
    case 'meta:drupen:summary':
      return dt('Drupal Penetration test helper.');
  }
}

/**
 * Implementation of hook_drush_command().
 */
function drupen_drush_command() {
  $items['route-list'] = array(
    'description' => 'List all route entries as valid urls.',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array(
      'route-name' => 'Filter by a single route.',
    ),
  );
  $items['route-test'] = array(
    'description' => 'Test access to all route entries.',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'options' => array(
      'route-name' => 'Filter by a single route.',
      'response-code' => 'Filter routes that respond with the provided HTTP code.',
      'response-cache' => 'Filter routes that have a X-Drupal-Cache value.',
      'profile' => 'Display response timing information.',
      'cookie' => 'Provide cookies to send with requests (for authentication).',
      'verify-ssl' => 'Verify the SSL certificate for responses.',
      'follow-redirects' => 'Follow HTTP redirects.',
    ),
  );
  $items['session-cookie'] = array(
    'description' => 'Output a session cookie.',
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'arguments' => array(
      'username' => 'Username for login.',
      'password' => 'Password for login.',
    ),
  );
  return $items;
}


function drush_drupen_pre_route_list($profile = NULL) {
  // @TODO Not sure if this function is needed.
  $sql = drush_sql_get_class();
  if (!$db_spec = $sql->db_spec()) {
    drush_set_error(dt('Could not determine database connection parameters. Pass --db-url option.'));
    return;
  }

  // Make sure URI is set so we get back a proper $alias_record. Needed for quick-drupal.
  _drush_bootstrap_selected_uri();
	
  $alias_record = drush_sitealias_get_record('@self');
  $sites_subdir = drush_sitealias_local_site_path($alias_record);
  // Override with sites-subdir if specified.
  if ($dir = drush_get_option('sites-subdir')) {
    $sites_subdir = "sites/$dir";
  }
  $conf_path = $sites_subdir;
}

/**
 * Command argument complete callback.
 */
function drush_drupen_route_list() {
  drush_print(dt('Listing all routes.'));
  foreach (routeList(drush_get_option('route-name', FALSE)) as $url) {
    drush_print($url);
  }
}


function drush_drupen_pre_route_test($profile = NULL) {
  // @TODO Not sure if this function is needed.
  $sql = drush_sql_get_class();
  if (!$db_spec = $sql->db_spec()) {
    drush_set_error(dt('Could not determine database connection parameters. Pass --db-url option.'));
    return;
  }

  // Make sure URI is set so we get back a proper $alias_record. Needed for quick-drupal.
  _drush_bootstrap_selected_uri();

  $alias_record = drush_sitealias_get_record('@self');
  $sites_subdir = drush_sitealias_local_site_path($alias_record);
  // Override with sites-subdir if specified.
  if ($dir = drush_get_option('sites-subdir')) {
    $sites_subdir = "sites/$dir";
  }
  $conf_path = $sites_subdir;
}

/**
 * Command argument complete callback.
 */
function drush_drupen_route_test() {
  $responseCode = drush_get_option('response-code', FALSE);

  if ($responseCode) {
    drush_print(dt('Testing routes for \'@code\' HTTP response code.', ['@code' => $responseCode]));
  }
  else {
    drush_print(dt('Testing all routes.'));
  }

  foreach (routeList(drush_get_option('route-name', FALSE)) as $url) {
    loadURL($url);
  }
}

/**
 * Command argument complete callback.
 */
function drush_drupen_session_cookie($username = '', $password = '') {
  $url = renderLink('user.login');
  /** @var \GuzzleHttp\Client $client */
  $client = \Drupal::service('http_client');
  $jar = new CookieJar;

  $client->request('POST', $url,
    [
      'form_params' => [
        'name' => $username,
        'pass' => $password,
        'form_id' => 'user_login_form',
        'op' => 'Log in',
      ],
      'cookies' => $jar,
      'allow_redirects' => [
        'max'             => 5,
        'referer'         => true,
        'on_redirect'     => function($request, $response, $uri) {
          drush_print($response->getHeader('Set-Cookie')[0]);
        },
        'track_redirects' => true
      ],
    ]
  );
}

/**
 * Build a list of routes with replacement parameters.
 *
 * @return \Generator
 */
function routeList($route_name = FALSE) {
  $route_handlers = \Drupal::service('drupen.route.handler.manager')->getHandlers();
  $collections = [];
  $routes = [];

  /** @var \Drupal\Core\Routing\RouteProvider $route_provider */
  $route_provider = \Drupal::service('router.route_provider');
  if ($route_name) {
    try {
      $routes[$route_name] = $route_provider->getRouteByName($route_name);
    }
    catch (RouteNotFoundException $e) {
      drush_set_error('route_mismatch', $e->getMessage());
      yield;
    }
  }
  else {
    $routes = $route_provider->getAllRoutes();
  }

  /** @var \Symfony\Component\Routing\Route $route */
  foreach ($routes as $route_name => $route) {
    /** @var \Drupal\drupen\RouteHandler\RouteHandlerInterface $route_handler */
    foreach ($route_handlers as $handler_name => $route_handler) {
      if ($route_handler->applies($route)) {
        if (empty($collections[$handler_name])) {
          $collections[$handler_name] = new RouteCollection();
        }
        $collections[$handler_name]->add($route_name, $route);
        break;
      }
    }
  }

  foreach ($collections as $handler_name => $collection) {
    $route_handler = $route_handlers[$handler_name];
    foreach ($route_handler->getUrls($collection) as $url) {
      if (!$url) {
        continue;
      }
      yield $url;
    }
  }
}

// Helper function to render an absolute link.
function renderLink($name, $params = []) {
  try {
    $url = \Drupal::urlGenerator()
      ->generateFromRoute($name, $params, ['absolute' => TRUE]);
    return $url;
  }
  catch (\Exception $e) {
    if (drush_get_context('DRUSH_VERBOSE')) {
      drush_log(dt('Skipping @route: Error generating url.', ['@route' => $name]), LogLevel::ERROR);
    }
    return null;
  }
}

/**
 * Guzzle wrapper to load and display urls.
 *
 * @param $url
 */
function loadURL($url) {
  if (drush_get_context('DRUSH_VERBOSE')) {
    drush_log(dt('HTTP Request: @url.', ['@url' => $url]), LogLevel::INFO);
  }

  /** @var \GuzzleHttp\Client $client */
  $client = \Drupal::service('http_client');

  // Use a specific cookie jar
  $jar = new CookieJar;

  if (drush_get_option('cookie', FALSE)) {
    $cookie_string = drush_get_option('cookie', FALSE);
    $newCookie = \GuzzleHttp\Cookie\SetCookie::fromString($cookie_string);
    /**
     * You can also do things such as $newCookie->setSecure(false);
     */
    $jar->setCookie($newCookie);
  }

  $client->request('GET', $url, [
    'on_stats' => function (TransferStats $stats) {
      if ($stats->hasResponse()) {
        $responseCode = drush_get_option('response-code', FALSE);
        $responseCache = drush_get_option('response-cache', FALSE);
        $profile = drush_get_option('profile', FALSE);

        $code = $stats->getResponse()->getStatusCode();
        $time = $stats->getTransferTime();
        $url = $stats->getEffectiveUri();
        if (null !== $stats->getResponse()->getHeader('X-Drupal-Cache')[0]) {
          $cache = $stats->getResponse()->getHeader('X-Drupal-Cache')[0];
        }
        else {
          $cache = 'MISS';
        }

        // Filter out based on response code.
        if ($responseCode && $responseCode != $code) {
          return;
        }

        // Filter out based on cache hit.
        if ($responseCache && $responseCache != $cache) {
          return;
        }

        if ($profile) {
          drush_print(dt('@code, @time, @cache, @url', [
            '@code' => $code,
            '@time' => $time,
            '@cache' => $cache,
            '@url' => $url,
          ]));
        }
        else {
          drush_print(dt('@code, @url', [
            '@code' => $code,
            '@url' => $url,
          ]));
        }
      }
    },
    'http_errors' => false,
    'cookies' => $jar,
    'curl' => [CURLOPT_SSL_VERIFYPEER => drush_get_option('verify-ssl', FALSE)],
    'allow_redirects' => drush_get_option('follow-redirects', FALSE),
  ]);
}

/**
 * Helper function to generate permutations of N sized arrays.
 *
 * @param string $separator
 * @param array $results
 * @param \array[] ...$arrays
 */
function generatePermutations($separator, array &$results, array ...$arrays) {
  $empty = empty($results);
  $array = array_shift($arrays);
  $new_results = [];
  foreach ($array as $key => $value) {
    if ($empty) {
      $results[] = $value;
    }
    else {
      foreach ($results as $result) {
        $new_results[$key][] = $result . $separator . $value;
      }
    }
  }
  if ($new_results) {
    $results = array_merge(...$new_results);
  }
  if (count($arrays)) {
    generatePermutations($separator, $results, ...$arrays);
  }
}

