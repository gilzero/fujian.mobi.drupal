<?php

/**
 * @file
 * No working PHP code.
 *
 * This file contains no working PHP code; it exists to provide additional
 * documentation for doxygen as well as to document hooks in the standard
 * Drupal manner.
 */

/**
 * Description.
 *
 * Allows modules to deny or provide access for a user to perform a non-view
 * operation on an entity before any other access check occurs.
 * Modules implementing this hook can return FALSE to provide a blanket
 * prevention for the user to perform the requested operation on the specified
 * entity. If no modules implementing this hook return FALSE but at least one
 * returns TRUE, then the operation will be allowed, even for a user without
 * role based permission to perform the operation.
 *
 * If no modules return FALSE but none return TRUE either, normal permission
 * based checking will apply.
 */
function hook_bat_entity_access(\Drupal\Core\Entity\EntityInterface $entity, $operation, \Drupal\Core\Session\AccountInterface $account) {
  // No example.
}
