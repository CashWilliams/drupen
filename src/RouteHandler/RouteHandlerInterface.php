<?php

/**
 * @file
 * Contains \Drupal\drupen\RouteHandler\RouteHandlerInterface.
 */

namespace Drupal\drupen\RouteHandler;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

interface RouteHandlerInterface {

  /**
   * Determines which handler applies to a route.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route object.
   *
   * @return bool
   */
  public function applies(Route $route);

  /**
   * Gets a list of viable Urls for a route.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *
   * @return \Generator
   */
  public function getUrls(RouteCollection $collection);

}
