<?php

namespace Drupal\field_hidden\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'field_hidden_string_textfield' widget.
 *
 * Adds HTML input[type='hidden'] widget support for the field type
 * 'string' AKA 'Text (plain)'.
 *
 * @FieldWidget(
 *   id = "field_hidden_string_textfield",
 *   label = @Translation("Hidden field"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class FieldHiddenStringTextfieldWidget extends StringTextfieldWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Evading 'element' ambiguity:
    // The field 'element' returned by parent
    // StringTextfieldWidget::formElement() is effectively an entire 'row'.
    // Whereas the 'element' returned by sibling TextfieldWidget::formElement()
    // implementation is a 'column' of the row.
    // For single-columned types it may seem obsolete to stress the actual
    // hierarchical nature of an 'element', whereas for multi-columned field
    // types (like File) it makes things far more transparent.
    $row = parent::formElement($items, $delta, $element, $form, $form_state);

    // Make the 'value' column's HTML element hidden, except when appearing as
    // default value in a field instance settings form.
    if ($form_state->getFormObject()->getFormId() != 'field_ui_field_edit_form') {
      $column_value =& $row['value'];

      $column_value['#type'] = 'hidden';

      // Add Field Hidden CSS selector to column - may well prove useful,
      // particularly for multi-row instances.
      $column_value['#attributes']['class'][] = 'field-hidden-' . str_replace('_', '-', $this->fieldDefinition->getType());

      // Add styles that hide multi-row instances.
      $column_value['#attached']['library'][] = 'field_hidden/drupal.field_hidden';
    }

    return $row;
  }

}
