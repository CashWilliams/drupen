<?php

/**
 * @file
 * Contains \Drupal\drupen\ParamHandler\Manager.
 */

namespace Drupal\drupen\ParamHandler;

class Manager {

  /**
   * @var \Drupal\drupen\ParamHandler\ParameterHandlerInterface[]
   */
  protected $handlers;

  public function addHandler(ParameterHandlerInterface $handler, $id) {
    $this->handlers[$id] = $handler;
  }

  public function getHandler($id) {
    return !empty($this->handlers[$id]) ? $this->handlers[$id] : NULL;
  }

  public function getHandlers() {
    return $this->handlers;
  }

}
