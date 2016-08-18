<?php

/**
 * @file
 * Contains \Drupal\drupen\ParamHandler\Entity.
 */

namespace Drupal\drupen\ParamHandler;

use \Drupal\Core\Entity\EntityTypeManager;

class Entity implements ParameterHandlerInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  public function __construct(EntityTypeManager $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public function applies(string $type) : bool {
    return substr($type, 0, 7) == 'entity:' && $type[8] != '{';
  }

  public function getParameters(string $type) : array {
    list(, $entity_type) = explode(':', $type);
    // Load all entities of a given type.
    return array_keys($this->entityTypeManager->getStorage($entity_type)->loadMultiple());
  }

}
