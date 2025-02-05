<?php

namespace Drupal\estimated_read_time\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'estimated_read_time_default' widget.
 *
 * @FieldWidget(
 *   id = "estimated_read_time_default",
 *   module = "estimated_read_time",
 *   label = @Translation("Minutes and seconds"),
 *   field_types = {
 *     "estimated_read_time"
 *   }
 * )
 */
class EstimatedReadTimeWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'sidebar' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['sidebar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Place field in sidebar'),
      '#default_value' => $this->getSetting('sidebar'),
      '#description' => $this->t('If checked, the field will be placed in the sidebar on entity forms.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if ($this->getSetting('sidebar')) {
      $summary['sidebar'] = $this->t('Use sidebar: Yes');
    }
    else {
      $summary['sidebar'] = $this->t('Use sidebar: No');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element += [
      '#title' => $this->t('Read time'),
      '#type' => 'details',
    ];

    // If the "sidebar" option was checked on the field widget, put the form
    // element into the form's "advanced" group. Otherwise, let it default to
    // the main field area and add the fields to a fieldset.
    $sidebar = $this->getSetting('sidebar');
    if ($sidebar) {
      $element['#group'] = 'advanced';
    }

    $element['auto'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Automatically estimate read time'),
      '#default_value' => $items[$delta]->auto ?? TRUE,
      '#description' => $this->t('Uncheck this to set a custom read time below.'),
      '#weight' => -1,
    ];

    $element['minutes'] = [
      '#title' => $this->t('Minutes'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->minutes,
      '#min' => 0,
      '#max' => 255,
      '#size' => 4,
      '#states' => [
        'disabled' => [
          'input[name="' . $items->getName() . '[' . $delta . '][auto]"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    $element['seconds'] = [
      '#title' => $this->t('Seconds'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->seconds,
      '#min' => 0,
      '#max' => 255,
      '#size' => 4,
      '#states' => [
        'disabled' => [
          'input[name="' . $items->getName() . '[' . $delta . '][auto]"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];

    return $element;
  }

}
