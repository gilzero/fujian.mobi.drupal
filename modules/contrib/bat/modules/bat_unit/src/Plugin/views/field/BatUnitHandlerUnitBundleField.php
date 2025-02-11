<?php

namespace Drupal\bat_unit\Plugin\views\field;

use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * Description.
 *
 * Contains a Views field handler to take care of displaying the correct label
 * for unit bundles.
 *
 * @ViewsField("bat_unit_handler_unit_bundle_field")
 */
class BatUnitHandlerUnitBundleField extends FieldPluginBase {

  /**
   * Description.
   */
  public function render(ResultRow $values) {
    $unit_bundle = bat_unit_bundle_load($this->getEntity($values)->bundle());
    return $unit_bundle->label();
  }

}
