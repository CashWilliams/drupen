<?php

/**
 * @file
 * Contains \Drupal\drupen\RouteHandler\DefaultHandler.
 */

namespace Drupal\drupen\RouteHandler;

use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\drupen\ParamHandler\Manager as ParamManager;
use Drupal\drupen\ParamHandler\ParameterHandlerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class DefaultHandler implements RouteHandlerInterface {

  /**
   * @var \Drupal\drupen\ParamHandler\ParameterHandlerInterface[]
   */
  protected $paramHandler;

  /**
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $generator;

  public function __construct(UrlGeneratorInterface $generator, ParamManager $manager) {
    $this->generator = $generator;
    $this->paramHandler = $manager;
  }

  public function applies(Route $route) {
    return TRUE;
  }

  public function getUrls(RouteCollection $collection) {
    foreach ($collection as $route_name => $route) {
      if (isset($route->getOptions()['parameters']) && count($route->getOptions()['parameters'])) {
        $replacements = [];
        foreach ($route->getOptions()['parameters'] as $key => $param) {
          $params = $this->getParameters($param['type']);
          if (!$params) {
            break;
          }
          $replacements[$key] = $params;
        }

        // Ensure the number of replacements is equal to the number of expected
        // parameters.
        if (count($replacements) != count($route->getOptions()['parameters'])) {
          continue;
        }

        $results = [];
        if ($replacements) {
          generatePermutations(DRUPEN_STRING_SEPERATOR, $results, ...array_values($replacements));
          $keys = array_keys($replacements);

          foreach ($results as $result) {
            $result = explode(DRUPEN_STRING_SEPERATOR, $result);
            if (count($keys) == count($result)) {
              yield renderLink($route_name, array_combine($keys, $result));
            }
          }
        }
      }
    }
  }

  protected function getParameterHandler($type) {
    foreach ($this->paramHandler->getHandlers() as $handler) {
      if ($handler->applies($type)) {
        return $handler;
      }
    }
  }

  protected function getParameters($type) {
    $handler = $this->getParameterHandler($type);
    if ($handler) {
      return $handler->getParameters($type);
    }
    return [];
  }

}
