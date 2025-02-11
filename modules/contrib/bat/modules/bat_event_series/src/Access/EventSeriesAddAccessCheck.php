<?php

namespace Drupal\bat_event_series\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\bat_event_series\EventSeriesTypeInterface;

/**
 * Determines access to for event add pages.
 */
class EventSeriesAddAccessCheck implements AccessInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a EntityCreateAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * Checks access to the event add page for the event series type.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The currently logged in account.
   * @param \Drupal\bat_event_series\EventSeriesTypeInterface $event_series_type
   *   (optional) The event series type. If not specified, access is
   *   allowed if there exists at least one event series type for which the
   *   user may create a event.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, EventSeriesTypeInterface $event_series_type = NULL) {
    $access_control_handler = $this->entityTypeManager->getAccessControlHandler('bat_event_series');

    if ($account->hasPermission('administer bat_event_series_type entities')) {
      // There are no type bundles defined that the user has permission to
      // create, but the user does have the permission to administer the content
      // types, so grant them access to the page anyway.
      return AccessResult::allowed();
    }

    if ($event_series_type) {
      return $access_control_handler->createAccess($event_series_type->id(), $account, [], TRUE);
    }

    $bundles = bat_event_series_get_types();
    foreach ($bundles as $bundle) {
      $tmp = bat_event_create(['type' => $bundle->id(), 'uid' => 0]);
      if (bat_event_series_access($tmp, 'create', $account->getAccount())->isAllowed()) {
        return AccessResult::allowed();
      }
    }

    return AccessResult::forbidden();
  }

}
