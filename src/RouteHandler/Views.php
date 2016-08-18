<?php

/**
 * @file
 * Contains \Drupal\drupen\RouteHandler\Views.
 */

namespace Drupal\drupen\RouteHandler;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Views implements RouteHandlerInterface {

  public function applies(Route $route) {
    return FALSE;
    // check $route->getDefaults()['view_id']
  }

  public function getUrls(RouteCollection $collection) {
    return [];
  }

}