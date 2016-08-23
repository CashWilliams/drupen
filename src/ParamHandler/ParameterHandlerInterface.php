<?php

/**
 * @file
 * Contains \Drupal\drupen\ParamHandler\ParameterHandlerInterface.
 */

namespace Drupal\drupen\ParamHandler;

interface ParameterHandlerInterface {

  public function applies($type);

  public function getParameters($type);
}
