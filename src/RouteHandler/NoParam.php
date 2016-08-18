<?php

/**
 * @file
 * Contains \Drupal\drupen\RouteHandler\NoParam.
 */

namespace Drupal\drupen\RouteHandler;

use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class NoParam implements RouteHandlerInterface {

  /**
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $generator;

  public function __construct(UrlGeneratorInterface $generator) {
    $this->generator = $generator;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    $path_parameters = [];
    preg_match_all('|\{\w+\}|', $route->getPath(), $path_parameters, PREG_PATTERN_ORDER);
    $path_parameters = $path_parameters[0];
    return !count($path_parameters);
  }

  /**
   * {@inheritdoc}
   */
  public function getUrls(RouteCollection $collection) {
    $urls = [];
    foreach ($collection as $name => $route) {
      $urls[] = $this->generator->generateFromRoute($name, [], ['absolute' => TRUE]);
    }
    return $urls;
  }

}