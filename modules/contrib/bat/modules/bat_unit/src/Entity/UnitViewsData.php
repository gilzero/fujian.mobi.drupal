<?php

namespace Drupal\bat_unit\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Unit entities.
 */
class UnitViewsData extends EntityViewsData implements EntityViewsDataInterface {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['unit']['table']['base'] = [
      'field' => 'id',
      'title' => $this->t('Unit'),
      'help' => $this->t('The Unit ID.'),
    ];

    $data['unit']['type']['field'] = [
      'title' => $this->t('Booking Unit Bundle'),
      'help' => $this->t('Booking Unit Bundle Label.'),
      'id' => 'bat_unit_handler_unit_bundle_field',
    ];

    $data['unit']['unit_bulk_form'] = [
      'title' => $this->t('Unit operations bulk form'),
      'help' => $this->t('Add a form element that lets you run operations on multiple units.'),
      'field' => [
        'id' => 'unit_bulk_form',
      ],
    ];

    $data['unit']['unit_type_id']['filter']['id'] = 'bat_unit_handler_type_id_filter';

    return $data;
  }

}
