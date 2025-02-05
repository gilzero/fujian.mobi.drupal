<?php

declare(strict_types=1);

namespace Drupal\Tests\ui_patterns\Kernel\Source;

use Drupal\Tests\ui_patterns\Kernel\SourcePluginsTestBase;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Test MenuSource.
 *
 * @coversDefaultClass \Drupal\ui_patterns\Plugin\UiPatterns\Source\MenuSource
 * @group ui_patterns
 */
class MenuSourceTest extends SourcePluginsTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ["link", "menu_link_content", "menu_ui"];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    // @todo Change the autogenerated stub
    parent::setUp();
    $this->installEntitySchema('menu_link_content');
    $this->installEntitySchema('user');
    $link = MenuLinkContent::create([
      'menu_name' => 'main',
      'link' => [['uri' => 'internal:/example-path']],
      'weight' => 5,
    ]);
    $link->save();
  }

  /**
   * Test MenuSource Plugin.
   */
  public function testPlugin(): void {
    $testData = self::loadTestDataFixture(__DIR__ . "/../../../fixtures/menu_tests.yml");
    $testSet = $testData->getTestSet("menu_1");
    $testSet["output"] = [
      "props" => [
        "links" => [
          "closure" => function ($output_of_source) {
            $this->assertNotNull($output_of_source);
            $this->assertTrue(is_array($output_of_source));
            $this->assertTrue(count($output_of_source) > 0);
            $this->assertTrue(is_array($output_of_source[0]));
            $this->assertEquals($output_of_source[0]["url"], "/example-path");
          },
        ],
      ],
    ];
    $this->runSourcePluginTest($testSet);
  }

}
