<?php

/**
 * @file
 * Contains \Drupal\drupen\RouteHandler\BlackList.
 */

namespace Drupal\drupen\RouteHandler;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class BlackList implements RouteHandlerInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    $black_listed = [
      '/<current>',
      '/',
      '/user/logout',
      '/autologout_ahah_logout',
    ];
    return (in_array($route->getPath(), $black_listed));
  }

  /**
   * {@inheritdoc}
   */
  public function getUrls(RouteCollection $collection) {
    yield;
  }

}
