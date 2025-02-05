<?php

namespace Drupal\bat_fullcalendar\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Description message.
 */
class FullcalendarForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bat_fullcalendar_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['bat_fullcalendar.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bat_fullcalendar.settings');

    $form['bat_fullcalendar_scheduler'] = [
      '#type' => 'container',
      '#prefix' => '<div id="label-settings">',
      '#suffix' => '</div>',
    ];

    $form['bat_fullcalendar_scheduler']['settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Full Calendar Settings'),
      '#description' => $this->t('Improve user experience with custom settings'),
      '#open' => TRUE,
    ];

    $form['bat_fullcalendar_scheduler']['settings']['bat_fullcalendar_calendar_height'] = [
      '#type' => 'textfield',
      '#attributes' => [
        'type' => 'number',
      ],
      '#title' => $this->t('Calendars height'),
      '#description' => $this->t('Global fixed height for the Fullcalendars'),
      '#default_value' => $config->get("bat_fullcalendar_calendar_height"),
      '#required' => TRUE,
      '#maxlength' => 1400,
    ];

    $form['bat_fullcalendar_scheduler']['settings']['automatic_calendar_height'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable automatic height'),
      '#description' => $this->t('Height will be automatically calculated for every calendar, depending numbers of items. @TODO. See https://www.drupal.org/project/bat/issues/3410466'),
      '#required' => FALSE,
    ];

    $form['bat_fullcalendar_scheduler']['bat_fullcalendar_scheduler_key'] = [
      '#type' => 'radios',
      '#title' => $this->t('FullCalendar Scheduler License'),
      '#default_value' => $config->get('bat_fullcalendar_scheduler_key'),
      '#options' => [
        'commercial' => $this->t('Commercial License'),
        'non-commercial' => $this->t('Non-Commercial Creative Commons'),
        'gpl' => $this->t('GPL License'),
        'none' => $this->t('None'),
      ],
      '#description' => $this->t('Please visit http://fullcalendar.io/scheduler/license/ to find out about the license terms for the Scheduler View of FullCalendar'),
      '#ajax' => [
        'callback' => [$this, 'fullcalendarSettingsAjax'],
        'wrapper' => 'label-settings',
      ],
    ];

    $values = $form_state->getValues();

    if ((isset($values['bat_fullcalendar_scheduler_key']) && $values['bat_fullcalendar_scheduler_key'] == 'commercial') ||
         (!isset($values['bat_fullcalendar_scheduler_key']) && $config->get('bat_fullcalendar_scheduler_key') == 'commercial')) {
      $form['bat_fullcalendar_scheduler']['bat_fullcalendar_scheduler_commercial_key'] = [
        '#type' => 'textfield',
        '#title' => $this->t('FullCalendar Scheduler Commercial License Key'),
        '#required' => TRUE,
        '#default_value' => $config->get('bat_fullcalendar_scheduler_commercial_key'),
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * Ajax callback.
   */
  public function fullcalendarSettingsAjax(array &$form, FormStateInterface $form_state) {
    return $form['bat_fullcalendar_scheduler'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config('bat_fullcalendar.settings')
      ->set('bat_fullcalendar_calendar_height', $form_state->getValue('bat_fullcalendar_calendar_height'))
      ->set('bat_fullcalendar_scheduler_key', $form_state->getValue('bat_fullcalendar_scheduler_key'))
      ->set('bat_fullcalendar_scheduler_commercial_key', $form_state->getValue('bat_fullcalendar_scheduler_commercial_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
