<?php

namespace Drupal\commerce\Cache\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\commerce\CurrentCountry;

/**
 * Defines the country cache context, for "per country" caching.
 *
 * Cache context ID: 'country'.
 */
class CountryCacheContext implements CacheContextInterface {

  /**
   * The current country.
   *
   * @var \Drupal\commerce\CurrentCountry
   */
  protected $currentCountry;

  /**
   * Constructs a new CountryCacheContext object.
   *
   * @param \Drupal\commerce\CurrentCountry $country
   *   The current country.
   */
  public function __construct(CurrentCountry $country) {
    $this->currentCountry = $country;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Country');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    // In case the current country cannot be determined, return "none" as the
    // cache context.
    return $this->currentCountry->getCountry()?->getCountryCode() ?? 'none';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
