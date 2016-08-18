<?php

/**
 * @file
 * Contains \Drupal\drupen\RouteHandler\Manager.
 */

namespace Drupal\drupen\RouteHandler;

class Manager {

  /**
   * @var \Drupal\drupen\RouteHandler\RouteHandlerInterface[]
   */
  protected $handlers;

  public function addHandler(RouteHandlerInterface $handler, $id) {
    $this->handlers[$id] = $handler;
  }

  public function getHandler($id) {
    return !empty($this->handlers[$id]) ? $this->handlers[$id] : NULL;
  }

  public function getHandlers() {
    return $this->handlers;
  }

}
