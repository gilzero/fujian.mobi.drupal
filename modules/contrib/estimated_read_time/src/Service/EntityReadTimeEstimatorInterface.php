<?php

namespace Drupal\estimated_read_time\Service;

use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Provides an interface for read time estimators.
 */
interface EntityReadTimeEstimatorInterface {

  /**
   * Sets the read time on an entity.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   A fieldable entity being acted on.
   */
  public function setEstimatedReadTime(FieldableEntityInterface $entity): void;

}
