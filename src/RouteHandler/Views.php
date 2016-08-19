<?php

/**
 * @file
 * Contains \Drupal\drupen\RouteHandler\Views.
 */

namespace Drupal\drupen\RouteHandler;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Views implements RouteHandlerInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $database) {
    $this->entityTypeManager = $entity_type_manager;
    $this->database = $database;
  }

  public function applies(Route $route) {
    return !empty($route->getDefaults()['view_id']);
  }

  public function getUrls(RouteCollection $collection) {
    $urls = [];
    $views_storage = $this->entityTypeManager->getStorage('view');
    /**
     * @var string $route_name
     * @var \Symfony\Component\Routing\Route $route
     */
    foreach ($collection as $route_name => $route) {
      /** @var \Drupal\views\ViewEntityInterface $view */
      $view = $views_storage->load($route->getDefaults()['view_id']);
      $executable = $view->getExecutable();
      $executable->setDisplay($route->getDefaults()['display_id']);
      $executable->mergeDefaults();
      $display = $executable->getDisplay();
      $display->mergeDefaults();
      $arg_count = 0;
      $replacements = [];
      foreach ($display->getHandlers('argument') as $argument_id => $argument) {
        $options = $argument->options;
        $table = $options['table'];
        $field = $options['field'];
        $query = $this->database->select($table, "t_$arg_count");
        $query->addField("t_$arg_count", $field);
        foreach ($query->execute() as $result) {
          $replacements["arg_$arg_count"][] = $result->$field;
        }
      }
      $results = [];
      if ($replacements) {
        generatePermutations(DRUPEN_STRING_SEPERATOR, $results, ...array_values($replacements));
        $keys = array_keys($replacements);
        foreach ($results as $result) {
          $result = explode(DRUPEN_STRING_SEPERATOR, $result);
          if (count($keys) == count($result)) {
            $urls[] = renderLink($route_name, array_combine($keys, $result));
          }
        }
      }
    }
    return $urls;
  }

}
