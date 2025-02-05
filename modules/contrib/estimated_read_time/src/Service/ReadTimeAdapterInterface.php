<?php

namespace Drupal\estimated_read_time\Service;

/**
 * Provides an interface for a read time package.
 *
 * This does not actually provide much functionality, but merely wraps whatever
 * vendor package is chosen to estimate the read time. Thus, the used package
 * can be swapped out more easily without affecting the Drupal facing services.
 */
interface ReadTimeAdapterInterface {

  /**
   * Estimates the read time for given content.
   *
   * @param string $content
   *   The content to estimate the read time of.
   * @param ?int $wordsPerMinute
   *   The amount of words a reader can read per minute or NULL if not set.
   *
   * @return array<string, int>
   *   An array with the read time estimation in the format:
   *   ['minutes' => int, 'seconds' => int].
   */
  public function estimate(string $content, ?int $wordsPerMinute = NULL): array;

}
