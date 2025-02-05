<?php

namespace Drupal\custom_add_content\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class CustomAddContentRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Use a custom controller for path '/node/add'.
    if ($route = $collection->get('node.add_page')) {
      $route->setDefault('_controller', '\Drupal\custom_add_content\Controller\CustomAddContentController::addPage');
    }
  }

}
