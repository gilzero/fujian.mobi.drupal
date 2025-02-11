<?php

/**
 * @file
 * Provides mocking data for S3fsCssOptimizerTest.
 */

/**
 * Mock Global base_url.
 *
 * Provide a base_url for use in unit tests.
 *
 * @var string
 */
global $base_url;
$base_url = 'www.example.org';


if (!function_exists('file_create_url')) {

  /**
   * Overrides file_create_url if not exists.
   *
   * @param string $path
   *   Path to create URL from.
   *
   * @return string
   *   file_create_url mocked.
   *
   * @todo Remove when D9.3 becomes minimum supported version.
   */
  function file_create_url($path) {
    global $base_url;
    return 'http://' . $base_url . $path;
  }

}
