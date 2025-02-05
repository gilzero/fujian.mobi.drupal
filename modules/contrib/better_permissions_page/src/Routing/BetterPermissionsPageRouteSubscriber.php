<?php

namespace Drupal\better_permissions_page\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class BetterPermissionsPageRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Change the controlleter for the admin permissions routing.
    if ($route = $collection->get('user.admin_permissions')) {
      $route->setDefault('_form', '\Drupal\better_permissions_page\Form\BetterPermissionsForm');
    }
  }

}
