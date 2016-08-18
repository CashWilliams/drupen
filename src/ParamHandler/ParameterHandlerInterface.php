<?php

/**
 * @file
 * Contains \Drupal\drupen\ParamHandler\ParameterHandlerInterface.
 */

namespace Drupal\drupen\ParamHandler;

interface ParameterHandlerInterface {

  public function applies(string $type) : bool ;

  public function getParameters(string $type) : array ;
}
