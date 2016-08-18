<?php

/**
 * @file
 * Contains \Drupal\drupen\RouteHandler\DefaultHandler.
 */

namespace Drupal\drupen\RouteHandler;

use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\drupen\ParamHandler\ParameterHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class DefaultHandler implements RouteHandlerInterface {

  /**
   * @var \Drupal\drupen\ParamHandler\ParameterHandlerInterface[]
   */
  protected $param_handlers;

  /**
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $generator;

  public function __construct(UrlGeneratorInterface $generator, ContainerInterface $container) {
    $this->generator = $generator;
    foreach ($container->findTaggedServiceIds('drupen_parameter_handler') as $id => $attributes) {
      $this->param_handlers[$id] = new Reference($id);
    }
  }

  public function applies(Route $route) {
    return TRUE;
  }

  public function getUrls(RouteCollection $collection) {
    $urls = [];
    foreach ($collection as $route_name => $route) {
      if (isset($route->getOptions()['parameters']) && count($route->getOptions()['parameters'])) {
        $replacements = [];
        foreach ($route->getOptions()['parameters'] as $key => $param) {
          $replacements[$key] = $this->getParameters();
        }
        $results = [];
        generatePermutations(DRUPEN_STRING_SEPERATOR, $results, ...array_values($replacements));
        $keys = array_keys($replacements);
        foreach($results as $result) {
          $result = explode(DRUPEN_STRING_SEPERATOR, $result);
          if (count($keys) == count($result)) {
            $urls[] = renderLink($route_name, array_combine($keys, $result));
          }
        }
      }
    }
    return $urls;
  }

  protected function getParameterHandler(string $type) : ParameterHandlerInterface {
    foreach ($this->param_handlers as $handler) {
      if ($handler->applies($type)) {
        return $handler;
      }
    }
  }

  protected function getParameters(string $type) : array {
    $handler = $this->getParameterHandler($type);
    return $handler->getParameters($type);
  }

}