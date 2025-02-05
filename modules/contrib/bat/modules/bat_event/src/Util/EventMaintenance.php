<?php

namespace Drupal\bat_event\Util;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * Define a maintenance class.
 *
 * Defines the Utility EventMaintenance class.
 * Cloned from https://www.drupal.org/project/bee_hotel.
 */
class EventMaintenance {

  use StringTranslationTrait;

  /**
   * Remove old events from database.
   */
  public function deleteOldBatEvents($options) {
    $date = new DrupalDateTime($options['days_back'] . ' days ago');
    $date->setTimezone(new \DateTimezone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $formatted = $date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

    $query = \Drupal::entityQuery('bat_event');
    $query->accessCheck(TRUE);
    $count_pre = $query->count()->execute();

    $ids = \Drupal::entityQuery('bat_event')
      ->accessCheck(TRUE)
      ->condition('event_dates.end_value', $formatted, '<')
      ->range(0, $options['how_many_per_cron'])
      ->execute();

    bat_event_delete_multiple($ids);

    $query = \Drupal::entityQuery('bat_event');
    $query->accessCheck(TRUE);
    $count_post = $query->count()->execute();

    $tmp = [
      "%c" => $options['how_many_per_cron'],
      "%older" => $options['days_back'],
      "%remain" => $count_post,
      "%count_pre" => $count_pre,
    ];
    $message = $this->t("counter_pre : [ %counter_pre ].N. %c bat_event(s)  older than %older days deleted. %remain bat_event(s) still in DB", $tmp);
    \Drupal::logger('bat_event')->notice($message);
  }

}
