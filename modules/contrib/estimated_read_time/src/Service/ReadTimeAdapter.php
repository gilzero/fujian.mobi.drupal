<?php

namespace Drupal\estimated_read_time\Service;

use Mtownsend\ReadTime\ReadTime as ReadTimeVendor;

/**
 * Provides an adapter for estimating the read time for given content.
 */
class ReadTimeAdapter implements ReadTimeAdapterInterface {

  /**
   * {@inheritdoc}
   */
  public function estimate(string $content, ?int $wordsPerMinute = NULL): array {

    $readTime = $wordsPerMinute !== NULL ?
      new ReadTimeVendor($content, FALSE, FALSE, $wordsPerMinute) :
      new ReadTimeVendor($content, FALSE, FALSE);
    $readTimeEstimation = $readTime->toArray();

    return [
      'minutes' => $readTimeEstimation['minutes'] ?? 0,
      'seconds' => $readTimeEstimation['seconds'] ?? 0,
    ];
  }

}
