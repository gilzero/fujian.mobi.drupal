<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_patterns\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\ui_patterns\Traits\TestDataTrait;

/**
 * Base class to test prop type normalization.
 *
 * @group ui_patterns
 */
abstract class PropTypeNormalizationTestBase extends KernelTestBase {

  use TestDataTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'text',
    'field',
    'node',
    'ui_patterns',
    'ui_patterns_test',
    'datetime',
    'filter',
  ];

  /**
   * Props of the test-component Component.
   *
   * @var array|null
   */
  protected ?array $testComponentProps;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('node', 'node_access');
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['system', 'filter']);
    $component = \Drupal::service('plugin.manager.sdc')->find('ui_patterns_test:test-component');
    $this->testComponentProps = $component->metadata->schema['properties'] ?? [];
  }

  /**
   * Run tests with a given prop.
   *
   * @param string $prop
   *   The prop to test.
   * @param array $tested_value
   *   The tested value.
   */
  protected function runRenderPropTest(string $prop, array $tested_value) : void {
    $expectedOutput = [
      "rendered_value" => $tested_value["rendered_value"],
      "assert" => $tested_value["assert"] ?? "assertStringContainsString",
    ];
    $exception_class = $tested_value["exception_class"] ?? NULL;
    if (!empty($exception_class)) {
      $this->expectException($exception_class);
    }
    $this->assertExpectedOutput($expectedOutput, [
      "#type" => "inline_template",
      "#template" => sprintf("{{ include('ui_patterns_test:test-component', {%s: injected}) }}", $prop),
      "#context" => ["injected" => $tested_value["value"]],
    ]);
    if (!empty($exception_class)) {
      $this->expectException($exception_class);
    }
    $this->assertExpectedOutput($expectedOutput, [
      "#type" => "component",
      '#component' => 'ui_patterns_test:test-component',
      "#props" => [$prop => $tested_value["value"]],
    ]);
  }

}
