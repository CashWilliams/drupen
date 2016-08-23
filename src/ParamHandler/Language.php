<?php

/**
 * @file
 * Contains \Drupal\drupen\ParamHandler\Language.
 */

namespace Drupal\drupen\ParamHandler;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;

class Language implements ParameterHandlerInterface {

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Language constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   */
  public function __construct(LanguageManagerInterface $language_manager) {
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($type) {
    return $type == 'langcode';
  }

  /**
   * {@inheritdoc}
   */
  public function getParameters($type) {
    return array_keys($this->languageManager->getLanguages(LanguageInterface::STATE_ALL));
  }

}
