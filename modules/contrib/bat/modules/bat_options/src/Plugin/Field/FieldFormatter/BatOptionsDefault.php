<?php

namespace Drupal\bat_options\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Description message.
 *
 * @FieldFormatter(
 *   id = "bat_options_default",
 *   label = @Translation("Bat Options Default"),
 *   field_types = {
 *     "bat_options",
 *   }
 * )
 */
class BatOptionsDefault extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = ['#markup' => "{$item->quantity} x {$item->name}"];
    }

    return $element;
  }

}
