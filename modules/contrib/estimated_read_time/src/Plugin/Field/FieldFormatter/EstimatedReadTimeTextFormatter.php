<?php

namespace Drupal\estimated_read_time\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\estimated_read_time\Plugin\Field\FieldType\EstimatedReadTimeItem;

/**
 * Plugin implementation of the 'estimated_read_time_text' formatter.
 *
 * @FieldFormatter(
 *   id = "estimated_read_time_text",
 *   module = "estimated_read_time",
 *   label = @Translation("Estimated read time text"),
 *   field_types = {
 *     "estimated_read_time"
 *   }
 * )
 */
class EstimatedReadTimeTextFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'tokenized_string' => '@minutes min read',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['tokenized_string'] = [
      '#title' => $this->t('Tokenized string'),
      '#type' => 'textfield',
      '#description' => $this->t('The text to display. @minutes and @seconds are available as replacement values.'),
      '#default_value' => $this->getSetting('tokenized_string'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#theme' => 'estimated_read_time_text',
        '#formatted_read_time' => $this->formatReadTime($item),
        '#minutes' => $item->minutes,
        '#seconds' => $item->seconds,
        '#tokenized_string' => $this->getSetting('tokenized_string'),
      ];
    }

    return $elements;
  }

  /**
   * Replaces the tokens in the tokenized_string setting.
   *
   * @param \Drupal\estimated_read_time\Plugin\Field\FieldType\EstimatedReadTimeItem $item
   *   The EstimatedReadTimeItem.
   *
   * @return string|null
   *   The formatted read time.
   */
  protected function formatReadTime(EstimatedReadTimeItem $item): ?string {
    $tokenizedString = $this->getSetting('tokenized_string');

    if (!$this->canPopulateTokenizedString($item, $tokenizedString)) {
      return NULL;
    }

    $tokenizedString = str_replace('@minutes', $item->minutes, $tokenizedString);

    return str_replace('@seconds', $item->seconds, $tokenizedString);
  }

  /**
   * Determines whether or not the tokenized string can be populated.
   *
   * This method looks at if the tokenized string is not empty and if at least
   * one token that is present in the string has a value. We would not want a
   * string such as "0 minutes 0 seconds read time" to be printed out.
   *
   * @param \Drupal\estimated_read_time\Plugin\Field\FieldType\EstimatedReadTimeItem $item
   *   The EstimatedReadTimeItem.
   * @param string $tokenizedString
   *   The tokenized string from the field formatter settings.
   *
   * @return bool
   *   Whether the string can be populated.
   */
  protected function canPopulateTokenizedString(EstimatedReadTimeItem $item, string $tokenizedString) {
    // If the tokenized string is empty, do not attempt to use it.
    if (empty($tokenizedString)) {
      return FALSE;
    }

    if (str_contains($tokenizedString, '@minutes') && !empty($item->minutes)) {
      return TRUE;
    }

    if (str_contains($tokenizedString, '@seconds') && !empty($item->seconds)) {
      return TRUE;
    }

    // All token values are empty.
    return FALSE;
  }

}
