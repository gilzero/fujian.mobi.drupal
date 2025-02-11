<?php

namespace Drupal\commerce_product\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\commerce\EntityHelper;
use Drupal\inline_entity_form\Form\EntityInlineForm;

/**
 * Defines the inline form for product variations.
 */
class ProductVariationInlineForm extends EntityInlineForm {

  /**
   * The loaded variation types.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationTypeInterface[]
   */
  protected $variationTypes;

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeLabels() {
    $labels = [
      'singular' => $this->t('variation'),
      'plural' => $this->t('variations'),
    ];
    return $labels;
  }

  /**
   * {@inheritdoc}
   */
  public function getTableFields($bundles) {
    $fields = parent::getTableFields($bundles);
    $fields['label']['label'] = $this->t('Title');
    $fields['price'] = [
      'type' => 'field',
      'label' => $this->t('Price'),
      'weight' => 10,
    ];
    $fields['status'] = [
      'type' => 'field',
      'label' => $this->t('Status'),
      'weight' => 100,
      'display_options' => [
        'settings' => [
          'format' => 'custom',
          'format_custom_true' => $this->t('Published'),
          'format_custom_false' => $this->t('Unpublished'),
        ],
      ],
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityLabel(EntityInterface $entity) {
    /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $entity */
    $variation_type = $this->loadVariationType($entity->bundle());
    if (!$variation_type->shouldGenerateTitle()) {
      return $entity->label();
    }

    // The generated variation title includes the product title, which isn't
    // relevant in this context, the user only needs to see the attribute part.
    if ($attribute_values = $entity->getAttributeValues()) {
      $attribute_labels = EntityHelper::extractLabels($attribute_values);
      $label = implode(', ', $attribute_labels);
    }
    else {
      // @todo Replace the Complex widget with the Simple one when there
      // are no attributes, indicating there should only be one variation.
      $label = $this->t('N/A');
    }

    return $label;
  }

  /**
   * Loads and returns a product variation type with the given ID.
   *
   * @param string $variation_type_id
   *   The variation type ID.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationTypeInterface
   *   The loaded product variation type.
   */
  protected function loadVariationType($variation_type_id) {
    if (!isset($this->variationTypes[$variation_type_id])) {
      $storage = $this->entityTypeManager->getStorage('commerce_product_variation_type');
      $this->variationTypes[$variation_type_id] = $storage->load($variation_type_id);
    }

    return $this->variationTypes[$variation_type_id];
  }

}
