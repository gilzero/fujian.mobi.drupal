<?php

namespace Drupal\typogrify\Plugin\Filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\typogrify\SmartyPants;
use Drupal\typogrify\Typogrify;
use Drupal\typogrify\UnicodeConversion;

/**
 * Provides a filter to restrict images to site.
 *
 * @Filter(
 *   id = "typogrify",
 *   title = @Translation("Typogrify"),
 *   description = @Translation("Adds typographic refinements"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   settings = {
 *     "smartypants_enabled" = 1,
 *     "smartypants_hyphens" = 3,
 *     "space_hyphens" = 0,
 *     "wrap_ampersand" = 1,
 *     "widont_enabled"= 1,
 *     "space_to_nbsp" = 1,
 *     "hyphenate_shy" = 0,
 *     "wrap_abbr" = 0,
 *     "wrap_caps" = 1,
 *     "wrap_initial_quotes" = 1,
 *     "wrap_numbers" = 0,
 *     "ligatures" = "a:0:{}",
 *     "arrows" = "a:0:{}",
 *     "fractions" = "a:0:{}",
 *     "quotes" = "a:0:{}",
 *   },
 *   weight = 10
 * )
 */
class TypogrifyFilter extends FilterBase {

  /**
   * The version of the Typogrify library being used.
   *
   * @var string
   */
  const TYPOGRIFY_VERSION = '1.0';

  /**
   * The keys in the settings array that are array-valued.
   *
   * @var array
   */
  protected static $arraySettingsKeys = [
    'ligatures',
    'arrows',
    'fractions',
    'quotes',
  ];

  /**
   * Serialize array values.
   *
   * There must be a better way to do this, but it looks as though trying to
   * save an array-valued plugin setting fails. Our solution is to serialize the
   * settings before saving and unserialize them before using.
   *
   * Serialize $settings[$key] for each $key in $arraySettingsKeys.
   *
   * @param array &$settings
   *   The array of plugin settings.
   *
   * @see settingsUnserialize()
   */
  protected static function settingsSerialize(array &$settings) {
    foreach (static::$arraySettingsKeys as $key) {
      if (isset($settings[$key]) && is_array($settings[$key])) {
        $settings[$key] = serialize(array_filter($settings[$key]));
      }
    }
  }

  /**
   * Unserialize array values.
   *
   * Unserialize $settings[$key] for each $key in $arraySettingsKeys.
   *
   * @param array &$settings
   *   The array of plugin settings.
   *
   * @see settingsSerialize()
   */
  protected static function settingsUnserialize(array &$settings) {
    foreach (static::$arraySettingsKeys as $key) {
      if (isset($settings[$key]) && is_string($settings[$key])) {
        $settings[$key] = unserialize($settings[$key], ['allowed_classes' => FALSE]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->settings;
    static::settingsUnserialize($settings);

    $form['help'] = [
      '#type' => 'markup',
      '#value' => '<p>' . $this->t('Enable the following typographic refinements:') . '</p>',
    ];

    // Smartypants settings.
    $form['smartypants_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use typographers quotation marks and dashes (<a href="@smarty-pants-url">SmartyPants</a>)',
        ['@smarty-pants-url' => 'http://daringfireball.net/projects/smartypants']
      ),
      '#default_value' => $settings['smartypants_enabled'],
    ];

    // Smartypants hyphenation settings.
    // Uses the same values as the parse attributes in the
    // SmartyPants::process() function.
    $form['smartypants_hyphens'] = [
      '#type' => 'select',
      '#title' => $this->t('Dash replacement settings for SmartyPants'),
      '#default_value' => $settings['smartypants_hyphens'],
      '#options' => [
        1 => $this->t('“--” for em-dashes; no en-dash support'),
        3 => $this->t('“--” for em-dashes; “---” for en-dashes'),
        2 => $this->t('“---” for em-dashes; “--” for en-dashes'),
      ],
    ];

    // Replace space_hyphens with em-dash.
    $form['space_hyphens'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Replace stand-alone dashes (normal dashes between whitespace) em-dashes.'),
      '#description' => $this->t('" - " will turn into " — ".'),
      '#default_value' => $settings['space_hyphens'],
    ];

    // Remove widows settings.
    $form['widont_enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Remove widows'),
      '#default_value' => $settings['widont_enabled'],
    ];

    // Remove widows settings.
    $form['hyphenate_shy'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Replace <code>=</code> with <code>&amp;shy;</code>'),
      '#description' => $this->t('Words may be broken at the hyphenation points marked by “=”.'),
      '#default_value' => $settings['hyphenate_shy'],
    ];

    // Replace normal spaces with non-breaking spaces before "double punctuation
    // marks". This is especially useful in french.
    $form['space_to_nbsp'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Replace normal spaces with non-breaking spaces before "double punctuation marks" <code>!?:;</code>.'),
      '#description' => $this->t('This is especially useful for french.'),
      '#default_value' => $settings['space_to_nbsp'],
    ];

    // Wrap caps settings.
    $form['wrap_caps'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Wrap caps'),
      '#default_value' => $settings['wrap_caps'],
    ];

    // Wrap ampersand settings.
    $form['wrap_ampersand'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Wrap ampersands'),
      '#default_value' => $settings['wrap_ampersand'],
    ];

    $form['wrap_abbr'] = [
      '#type' => 'select',
      '#title' => $this->t('Thin space in abbreviations'),
      '#description' => $this->t('Wraps abbreviations with <code>@wrapper-code</code> and inserts space after the dots.', ['@wrapper-code' => '<span class="abbr">…</span>']),
      '#default_value' => $settings['wrap_abbr'],
      '#options' => [
        0 => $this->t('Do nothing'),
        4 => $this->t('Insert no space'),
        1 => $this->t('“U+202F“ Narrow no-break space'),
        2 => $this->t('“U+2009“ Thin space'),
        3 => $this->t('span with margin-left: 0.167em'),
      ],
    ];

    $form['wrap_numbers'] = [
      '#type' => 'select',
      '#title' => $this->t('Digit grouping in numbers'),
      '#description' => $this->t('Wraps numbers with <code>@wrapper-code</code> and inserts thin space for digit grouping.', ['@wrapper-code' => '<span class="number">…</span>']),
      '#default_value' => $settings['wrap_numbers'],
      '#options' => [
        0 => $this->t('Do nothing'),
        1 => $this->t('“U+202F“ Narrow no-break space'),
        2 => $this->t('“U+2009“ Thin space'),
        3 => $this->t('span with margin-left: 0.167em'),
        4 => $this->t('just wrap numbers'),
      ],
    ];

    // Wrap initial quotes settings.
    $form['wrap_initial_quotes'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Wrap quotation marks'),
      '#default_value' => $settings['wrap_initial_quotes'],
    ];

    // Ligature conversion settings.
    $ligature_options = [];
    foreach (UnicodeConversion::map('ligature') as $ascii => $unicode) {
      $ligature_options[$ascii] = $this->t('Convert <code>@ascii</code> to <code>@unicode</code>', [
        '@ascii' => $ascii,
        '@unicode' => $unicode,
      ]);
    }

    $form['ligatures'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Ligatures'),
      '#options' => $ligature_options,
      '#default_value' => $settings['ligatures'],
    ];

    // Arrow conversion settings.
    $arrow_options = [];
    foreach (UnicodeConversion::map('arrow') as $ascii => $unicode) {
      $arrow_options[$ascii] = $this->t('Convert <code>@ascii</code> to <code>@unicode</code>', [
        '@ascii' => $this->unquote($ascii),
        '@unicode' => $unicode,
      ]);

    }

    $form['arrows'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Arrows'),
      '#options' => $arrow_options,
      '#default_value' => $settings['arrows'],
    ];

    // Fraction conversion settings.
    $fraction_options = [];
    foreach (UnicodeConversion::map('fraction') as $ascii => $unicode) {
      $fraction_options[$ascii] = $this->t('Convert <code>@ascii</code> to <code>@unicode</code>', [
        '@ascii' => $ascii,
        '@unicode' => $unicode,
      ]);

    }

    $form['fractions'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Fractions'),
      '#options' => $fraction_options,
      '#default_value' => $settings['fractions'],
    ];

    // Quotes conversion settings.
    $quotes_options = [];
    foreach (UnicodeConversion::map('quotes') as $quotes => $unicode) {
      $quotes_options[$quotes] = $this->t('Convert <code>@ascii</code> to <code>@unicode</code>', [
        '@ascii' => $this->unquote($quotes),
        '@unicode' => $unicode,
      ]);
    }

    $form['quotes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Quotes'),
      '#options' => $quotes_options,
      '#default_value' => $settings['quotes'],
    ];

    // Version Information Settings.
    $version_strings = [];
    $version_strings[] = $this->t('SmartyPants PHP version: <a href=":smarty-pants-url">@version</a>', [
      ':smarty-pants-url' => 'http://www.michelf.com/projects/php-smartypants/',
      '@version' => SmartyPants::SMARTYPANTS_PHP_VERSION,
    ]);
    $version_strings[] = $this->t('PHP Typogrify Version: <a href=":php-typogrify-url">@version</a>', [
      ':php-typogrify-url' => 'http://blog.hamstu.com/',
      '@version' => static::TYPOGRIFY_VERSION,
    ]);

    $form['info']['typogrify_status'] = [
      '#theme' => 'item_list',
      '#items' => $version_strings,
      '#title' => $this->t('Versions'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    static::settingsSerialize($configuration['settings']);
    parent::setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $settings = $this->settings;
    static::settingsUnserialize($settings);
    $characters_to_convert = [];
    $ctx = [];

    if ($langcode == LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      $language = \Drupal::languageManager()->getCurrentLanguage();
      $ctx['langcode'] = $language->getId();
    }
    else {
      $ctx['langcode'] = $langcode;
    }

    // Build a list of ligatures to convert.
    foreach (UnicodeConversion::map('ligature') as $ascii => $unicode) {
      if (isset($settings['ligatures'][$ascii]) && $settings['ligatures'][$ascii]) {
        $characters_to_convert[] = $ascii;
      }
    }

    // Wrap caps.
    if ($settings['wrap_caps']) {
      $text = Typogrify::caps($text);
    }

    // Build a list of arrows to convert.
    foreach (UnicodeConversion::map('arrow') as $ascii => $unicode) {
      $htmle = $this->unquote($ascii);
      if ((isset($settings['arrows'][$ascii]) && $settings['arrows'][$ascii]) ||
        (isset($settings['arrows'][$htmle]) && $settings['arrows'][$htmle])) {
        $characters_to_convert[] = $ascii;
      }
    }

    // Build a list of fractions to convert.
    foreach (UnicodeConversion::map('fraction') as $ascii => $unicode) {
      if (isset($settings['fractions'][$ascii]) && $settings['fractions'][$ascii]) {
        $characters_to_convert[] = $ascii;
      }
    }

    // Build a list of quotation marks to convert.
    foreach (UnicodeConversion::map('quotes') as $ascii => $unicode) {
      if (isset($settings['quotes'][$ascii]) && $settings['quotes'][$ascii]) {
        $characters_to_convert[] = $ascii;
      }
    }

    // Convert ligatures and arrows.
    if (count($characters_to_convert) > 0) {
      $text = UnicodeConversion::convertCharacters($text, $characters_to_convert);
    }

    // Wrap ampersands.
    if ($settings['wrap_ampersand']) {
      $text = SmartyPants::smartAmpersand($text);
    }

    // Smartypants formatting.
    if ($settings['smartypants_enabled']) {
      $text = SmartyPants::process($text, $settings['smartypants_hyphens'], $ctx);
    }

    // Wrap abbreviations.
    if ($settings['wrap_abbr'] > 0) {
      $text = SmartyPants::smartAbbreviation($text, $settings['wrap_abbr']);
    }

    // Wrap huge numbers.
    if ($settings['wrap_numbers'] > 0) {
      $text = SmartyPants::smartNumbers($text, $settings['wrap_numbers']);
    }

    // Wrap initial quotes.
    if ($settings['wrap_initial_quotes']) {
      $text = Typogrify::initialQuotes($text);
    }

    // Wrap initial quotes.
    if ($settings['hyphenate_shy']) {
      $text = SmartyPants::hyphenate($text);
    }

    // Remove widows.
    if ($settings['widont_enabled']) {
      $text = Typogrify::widont($text);
    }

    // Replace normal spaces with non-breaking spaces before "double punctuation
    // marks". This is especially useful in french.
    if (isset($settings['space_to_nbsp']) && $settings['space_to_nbsp']) {
      $text = SmartyPants::spaceToNbsp($text);
    }

    // Replace normal whitespace '-' whitespace with em-dash.
    if (isset($settings['space_hyphens']) && $settings['space_hyphens']) {
      $text = SmartyPants::spaceHyphens($text);
    }

    $result = new FilterProcessResult($text);
    $result->setAttachments([
      'library' => [
        'typogrify/typogrify',
      ],
    ]);

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    $settings = $this->settings;
    static::settingsUnserialize($settings);

    if ($long) {
      $output = $this->t('Typogrify.module brings the typographic refinements of Typogrify to Drupal.');
      $output .= '<ul>';
      if ($settings['wrap_ampersand']) {
        $output .= '<li>' . $this->t('Wraps ampersands (the “&amp;” character) with <code>@wrapper-code</code>.',
          ['@wrapper-code' => '<span class="amp">&</span>']) . '</li>';
      }
      if ($settings['widont_enabled']) {
        $output .= '<li>' . $this->t("Prevents single words from wrapping onto their own line using Shaun Inman's Widont technique.") . '</li>';
      }
      if ($settings['wrap_initial_quotes']) {
        $output .= '<li>' . $this->t("Converts straight quotation marks to typographer's quotation marks, using SmartyPants.");
        $output .= '</li><li>' . $this->t('Wraps initial quotation marks with <code>@wrapper-code-quote</code> or <code>@wrapper-code-dquote</code>.', [
          '@wrapper-code-quote' => '<span class="quo"></span>;',
          '@wrapper-code-dquote' => '<span class="dquo"></span>;',
        ]) . '</li>';
      }
      $output .= $this->t('<li>Converts multiple hyphens to en dashes and em dashes (according to your preferences), using SmartyPants.</li>');
      if ($settings['hyphenate_shy']) {
        $output .= '<li>' . $this->t('Words may be broken at the hyphenation points marked by “=”.') . '</li>';
      }
      if ($settings['wrap_abbr']) {
        $output .= '<li>' . $this->t('Wraps abbreviations as “e.g.” to <code>@wrapper-code</code> and adds a thin space (1/6 em) after the dots.</li>', [
          '@wrapper-code' => '<span class="abbr">e.g.</span>',
        ]) . '</li>';
      }
      if ($settings['wrap_numbers']) {
        $output .= '<li>' . $this->t('Wraps large numbers &gt; 1&thinsp;000 with <code>@wrapper-code</code> and inserts thin space for digit grouping.', [
          '@wrapper-code' => '<span class="number">…</span>',
        ]) . '</li>';
      }
      if ($settings['wrap_caps']) {
        $output .= '<li>' . $this->t('Wraps multiple capital letters with <code>@wrapper-code</code>.', [
          '@wrapper-code' => '<span class="caps">CAPS</span>',
        ]) . '</li>';
      }
      $output .= '<li>' . $this->t('Adds a css style sheet that uses the &lt;span&gt; tags to substitute a showy ampersand in headlines, switch caps to small caps, and hang initial quotation marks.') . '</li>';
      // Build a list of quotation marks to convert.
      foreach (UnicodeConversion::map('quotes') as $ascii => $unicode) {
        if (!empty($settings['quotes'][$ascii])) {
          $ascii_to_unicode = $this->t('Converts <code>@ascii</code> to @unicode', [
            '@ascii' => $ascii,
            '@unicode' => $unicode,
          ]);
          $output .= "<li>$ascii_to_unicode</li>\n";
        }
      }
      $output .= '</ul>';
    }
    else {
      $output = $this->t('Typographic refinements will be added.');
    }

    return $output;
  }

  /**
   * Helper function to unquote a string.
   *
   * Unquotes a string.
   *
   * @param string|array $text
   *   String or array of strings to be unquoted.
   *
   * @return string|array
   *   Original $text with simple '<' and '>' instead of HTML entities.
   */
  private function unquote($text) {
    $text = str_replace(
      ['&lt;', '&gt;'],
      ['<', '>'],
      $text);

    return $text;
  }

}
