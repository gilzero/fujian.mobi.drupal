<?php

namespace Drupal\registration_waitlist\Plugin\views\field;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to present the spaces reserved for a host entity.
 *
 * @ViewsField("host_entity_waitlist_spaces_reserved")
 */
class HostEntityWaitListSpacesReserved extends FieldPluginBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): HostEntityWaitListSpacesReserved {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function clickSortable() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if ($entity = $this->getEntity($values)) {
      $handler = $this->entityTypeManager->getHandler($entity->getEntityTypeId(), 'registration_host_entity');
      $host_entity = $handler->createHostEntity($entity);
      if ($host_entity->isConfiguredForRegistration() && $host_entity->isWaitListEnabled()) {
        $build = [
          '#markup' => $host_entity->getWaitListSpacesReserved(),
        ];
        $host_entity->addCacheableDependencies($build, [$host_entity->getSettings()]);
        return $build;
      }
    }

    return NULL;
  }

}
