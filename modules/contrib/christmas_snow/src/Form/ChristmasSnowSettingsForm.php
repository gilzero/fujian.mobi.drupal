<?php

namespace Drupal\christmas_snow\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class ChristmasSnowSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'christmas_snow_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'christmas_snow.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('christmas_snow.settings');
    $form = [];
    $form['snow_setting'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Christmas snow'),
    ];
    $form['snow_setting']['christmas_snow'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable snow'),
      '#default_value' => $config->get('christmas_snow'),
    ];
    $form['snow_setting']['snow_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Snow settings'),
      '#states' => [
        'visible' => [
          '#edit-christmas-snow' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['snow_setting']['snow_settings']['christmas_snow_flakes_max'] = [
      '#type' => 'select',
      '#title' => $this->t('Maximum snow flakes'),
      '#default_value' => $config->get('christmas_snow_flakes_max') ?: 128,
      '#options' => [
        '16' => $this->t('a flurry'),
        '32' => $this->t('a snow fall'),
        '64' => $this->t('heavy snow'),
        '128' => $this->t('a blizzard'),
        '512' => $this->t("nor'easter"),
      ],
      '#description' => $this->t('Sets the maximum number of snowflakes that can exist on the screen at any given time.'),
    ];
    $form['snow_setting']['snow_settings']['christmas_snow_snowcolor'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Snow color'),
      '#suffix' => '<div id="color-picker"></div>',
      '#default_value' => $config->get('christmas_snow_snowcolor') ?: '#FFFFFF',
      '#maxlength' => 7,
      '#size' => 7,
      '#attached' => [
        'library' => [
          'core/jquery.farbtastic',
          'christmas_snow/colorpicker',
        ],
      ],
    ];
    $form['snow_setting']['snow_settings']['christmas_snow_flake_bottom'] = [
      '#type' => 'select',
      '#title' => $this->t('Flakes on the bottom'),
      '#default_value' => $config->get('christmas_snow_flake_bottom') ?: 500,
      '#options' => [
        '500' => 'shallow',
        '750' => 'medium',
        '1000' => 'thick',
      ],
      '#description' => $this->t("Limits the 'floor' (pixels) of the snow. If unspecified, snow will 'stick' to the bottom of the browser window and persists through browser resize/scrolling."),
    ];

    $form['snow_setting']['snow_settings']['christmas_snow_follow_mouse'] = [
      '#type' => 'select',
      '#title' => $this->t('Flakes follow mouse'),
      '#default_value' => $config->get('christmas_snow_follow_mouse') ?: 'true',
      '#options' => [
        "true" => 'Yes',
        "false" => 'No',
      ],
      '#description' => $this->t("Allows snow to move dynamically with the 'wind', relative to the mouse's X (left/right) coordinates."),
    ];

    $form['snow_setting']['snow_settings']['christmas_snow_melt'] = [
      '#type' => 'select',
      '#title' => $this->t('Flakes melt away'),
      '#default_value' => $config->get('christmas_snow_melt') ?: 'true',
      '#options' => [
        "true" => 'Yes',
        "false" => 'No',
      ],
      '#description' => $this->t("When recycling fallen snow (or rarely, when falling), have it 'melt' and fade out if browser supports it"),
    ];

    $form['snow_setting']['snow_settings']['christmas_snow_stick'] = [
      '#type' => 'select',
      '#title' => $this->t('Flakes stick'),
      '#default_value' => $config->get('christmas_snow_stick') ?: 'false',
      '#options' => [
        "false" => 'Yes',
        "true" => 'No',
      ],
      '#description' => $this->t("Allows the snow to 'stick' to the bottom of the window. When off, snow will never sit at the bottom."),
    ];

    $form['snow_setting']['snow_settings']['christmas_snow_twinkle'] = [
      '#type' => 'select',
      '#title' => $this->t('Flakes twinkle'),
      '#default_value' => $config->get('christmas_snow_twinkle') ?: 'false',
      '#options' => [
        "false" => 'Yes',
        "true" => 'No',
      ],
      '#description' => $this->t("Allow snow to randomly 'flicker' in and out of view while falling"),
    ];

    $form['snow_setting']['snow_settings']['christmas_snow_character'] = [
      '#type' => 'select',
      '#title' => $this->t('The character for the flake'),
      '#default_value' => $config->get('christmas_snow_character') ?: '•',
      '#options' => [
        "•" => "• (bullet)",
        "·" => "· (middot)",
      ],
      '#description' => $this->t("&bull; (•) = bullet. &middot; entity (·) is not used as it's square on some systems etc. Changing this may result in cropping of the character and may require flakeWidth/flakeHeight changes, so be careful."),
    ];

    $animation_options = [
      '20' => $this->t('fast & smooth - poor performance'),
      '33' => $this->t('normal speed - moderate performance'),
      '50' => $this->t('slow & choppy - conservative performance'),
    ];

    $form['snow_setting']['snow_settings']['christmas_snow_animation_int'] = [
      '#type' => 'select',
      '#title' => $this->t('Performance'),
      '#default_value' => $config->get('christmas_snow_animation_int') ?: '33',
      '#options' => $animation_options,
      '#description' => $this->t("Theoretical 'miliseconds per frame' measurement. 20 = fast + smooth, but high CPU use. 50 = more conservative, but slower"),
    ];
    $form['snow_setting']['snow_settings']['christmas_snow_minified'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use minified libraries if it possible'),
      '#default_value' => $config->get('christmas_snow_minified'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('christmas_snow.settings')
      ->set('christmas_snow', $values['christmas_snow'])
      ->set('christmas_snow_flakes_max', $values['christmas_snow_flakes_max'])
      ->set('christmas_snow_snowcolor', $values['christmas_snow_snowcolor'])
      ->set('christmas_snow_flake_bottom', $values['christmas_snow_flake_bottom'])
      ->set('christmas_snow_follow_mouse', $values['christmas_snow_follow_mouse'])
      ->set('christmas_snow_melt', $values['christmas_snow_melt'])
      ->set('christmas_snow_stick', $values['christmas_snow_stick'])
      ->set('christmas_snow_twinkle', $values['christmas_snow_twinkle'])
      ->set('christmas_snow_character', $values['christmas_snow_character'])
      ->set('christmas_snow_animation_int', $values['christmas_snow_animation_int'])
      ->set('christmas_snow_minified', $values['christmas_snow_minified'])
      ->save();

    $this->messenger()->addMessage($this->t('The configuration options have been saved.'));
  }

}
