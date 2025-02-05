<?php

namespace Drupal\estimated_read_time\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'estimated_read_time' field type.
 *
 * @FieldType(
 *   id = "estimated_read_time",
 *   label = @Translation("Read Time"),
 *   module = "estimated_read_time",
 *   category = "general",
 *   description = @Translation("Estimates the read time of an entity."),
 *   default_widget = "estimated_read_time_default",
 *   default_formatter = "estimated_read_time_text"
 * )
 */
class EstimatedReadTimeItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'auto' => [
          'type' => 'int',
          'size' => 'tiny',
          'not null' => FALSE,
        ],
        'minutes' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'size' => 'tiny',
          'not null' => FALSE,
        ],
        'seconds' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'size' => 'tiny',
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'view_mode' => [],
      'words_per_minute' => 230,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\ContentEntityBase $entity */
    $entity = $form['#entity'];

    /** @var \Drupal\Core\Entity\EntityDisplayRepository $entityDisplayRepository */
    $entityDisplayRepository = \Drupal::service('entity_display.repository');
    $viewModes = $entityDisplayRepository->getViewModeOptionsByBundle($entity->getEntityTypeId(), $entity->bundle());

    $element = [];

    $element['view_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('View Mode'),
      '#description' => $this->t('The view mode to use to calculate the read time.'),
      '#options' => $viewModes,
      '#default_value' => $this->getSetting('view_mode'),
    ];

    $element['words_per_minute'] = [
      '#type' => 'number',
      '#title' => $this->t('Words Per Minute'),
      '#description' => $this->t('The reading speed of your audience. 230 words per minute is a good average reading speed for adults reading non-technical material. Your audience and type of content may result in a faster or slower reading speed.'),
      '#min' => 1,
      '#step' => 1,
      '#required' => TRUE,
      '#default_value' => $this->getSetting('words_per_minute'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $auto = random_int(0, 1) === 1;
    $minutes = random_int(1, 30);
    $seconds = random_int(1, 59);

    $values['auto'] = $auto;
    $values['minutes'] = $minutes;
    $values['seconds'] = $seconds;

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $minutes = $this->get('minutes')->getValue();
    $seconds = $this->get('seconds')->getValue();
    return ($minutes === NULL || $minutes === '') && ($seconds === NULL || $seconds === '');
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['auto'] = DataDefinition::create('boolean')
      ->setLabel(t('Auto estimate'));
    $properties['minutes'] = DataDefinition::create('integer')
      ->setLabel(t('Minutes'));
    $properties['seconds'] = DataDefinition::create('integer')
      ->setLabel(t('Seconds'));

    return $properties;
  }

}
