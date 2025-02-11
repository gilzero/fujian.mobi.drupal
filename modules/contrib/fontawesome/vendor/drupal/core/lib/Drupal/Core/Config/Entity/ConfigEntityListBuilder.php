<?php

namespace Drupal\Core\Config\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines the default class to build a listing of configuration entities.
 *
 * @ingroup entity_api
 */
class ConfigEntityListBuilder extends EntityListBuilder {

  /**
   * The config entity storage class.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity_ids = $this->getEntityIds();
    $entities = $this->storage->loadMultipleOverrideFree($entity_ids);

    // Sort the entities using the entity class's sort() method.
    // See \Drupal\Core\Config\Entity\ConfigEntityBase::sort().
    uasort($entities, [$this->entityType->getClass(), 'sort']);
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
    $operations = parent::getDefaultOperations($entity);

    if ($this->entityType->hasKey('status')) {
      if (!$entity->status() && $entity->hasLinkTemplate('enable')) {
        $operations['enable'] = [
          'title' => $this->t('Enable'),
          'weight' => -10,
          'url' => $this->ensureDestination($entity->toUrl('enable')),
        ];
      }
      elseif ($entity->hasLinkTemplate('disable')) {
        $operations['disable'] = [
          'title' => $this->t('Disable'),
          'weight' => 40,
          'url' => $this->ensureDestination($entity->toUrl('disable')),
        ];
      }
    }

    return $operations;
  }

  /**
   * Gets the config entity storage.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   *   The config storage used by this list builder.
   */
  public function getStorage(): ConfigEntityStorageInterface {
    return $this->storage;
  }

}
