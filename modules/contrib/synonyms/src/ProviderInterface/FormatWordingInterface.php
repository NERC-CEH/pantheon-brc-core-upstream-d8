<?php

namespace Drupal\synonyms\ProviderInterface;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\synonyms\SynonymInterface;

/**
 * Interface to format a synonym into some kind of wording.
 */
interface FormatWordingInterface {

  /**
   * Format a synonym into wording as requested by configuration.
   *
   * @param string $synonym
   *   Synonym that should be formatted.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity to which this synonym belongs.
   * @param \Drupal\synonyms\SynonymInterface $synonym_config
   *   Synonym config entity in the context of which it all happens.
   * @param string $service_id
   *   The caller widget's service id.
   *
   * @return string
   *   Formatted wording
   */
  public function synonymFormatWording($synonym, ContentEntityInterface $entity, SynonymInterface $synonym_config, $service_id);

  /**
   * Get available tokens for format wording.
   *
   * @return array
   *   Array of supported tokens in wording. Keys are the tokens whereas
   *   corresponding values are explanations about what each token means
   */
  public function formatWordingAvailableTokens();

}
