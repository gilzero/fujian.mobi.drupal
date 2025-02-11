<?php

namespace Drupal\bat_event_series;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of Event entities.
 *
 * @ingroup bat
 */
class EventSeriesListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id())
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'id' => [
        'data' => $this->t('Event ID'),
        'field' => 'id',
        'specifier' => 'id',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'label' => [
        'data' => $this->t('Label'),
        'field' => 'label',
        'specifier' => 'label',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'rrule' => [
        'data' => $this->t('RRule'),
        'field' => 'rrule',
        'specifier' => 'rrule',
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['id'] = $entity->id();
    $row['label'] = $entity->label();
    $row['rrule'] = $entity->getRrule();
    return $row + parent::buildRow($entity);
  }

}
