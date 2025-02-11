<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\EventSubscriber;

use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\EventSubscriber\ModuleRouteSubscriber;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

/**
 * @coversDefaultClass \Drupal\Core\EventSubscriber\ModuleRouteSubscriber
 * @group EventSubscriber
 */
class ModuleRouteSubscriberTest extends UnitTestCase {

  /**
   * The mock module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->moduleHandler = $this->createMock('Drupal\Core\Extension\ModuleHandlerInterface');

    $value_map = [
      ['enabled', TRUE],
      ['disabled', FALSE],
    ];

    $this->moduleHandler->expects($this->any())
      ->method('moduleExists')
      ->willReturnMap($value_map);
  }

  /**
   * Tests that removeRoute() removes routes when the module is not enabled.
   *
   * @param string $route_name
   *   The machine name for the route.
   * @param array $requirements
   *   An array of requirements to use for the route.
   * @param bool $removed
   *   Whether or not the route is expected to be removed from the collection.
   *
   * @dataProvider providerTestRemoveRoute
   * @covers ::onAlterRoutes
   */
  public function testRemoveRoute($route_name, array $requirements, $removed): void {
    $collection = new RouteCollection();
    $route = new Route('', [], $requirements);
    $collection->add($route_name, $route);

    $event = new RouteBuildEvent($collection);
    $route_subscriber = new ModuleRouteSubscriber($this->moduleHandler);
    $route_subscriber->onAlterRoutes($event);

    if ($removed) {
      $this->assertNull($collection->get($route_name));
    }
    else {
      $this->assertInstanceOf('Symfony\Component\Routing\Route', $collection->get($route_name));
    }
  }

  /**
   * Data provider for testRemoveRoute().
   */
  public static function providerTestRemoveRoute() {
    return [
      ['enabled', ['_module_dependencies' => 'enabled'], FALSE],
      ['disabled', ['_module_dependencies' => 'disabled'], TRUE],
      ['enabled_or', ['_module_dependencies' => 'disabled,enabled'], FALSE],
      ['enabled_or', ['_module_dependencies' => 'enabled,disabled'], FALSE],
      ['disabled_or', ['_module_dependencies' => 'disabled,disabled'], TRUE],
      ['enabled_and', ['_module_dependencies' => 'enabled+enabled'], FALSE],
      ['enabled_and', ['_module_dependencies' => 'enabled+disabled'], TRUE],
      ['enabled_and', ['_module_dependencies' => 'disabled+enabled'], TRUE],
      ['disabled_and', ['_module_dependencies' => 'disabled+disabled'], TRUE],
      ['no_dependencies', [], FALSE],
    ];
  }

}
