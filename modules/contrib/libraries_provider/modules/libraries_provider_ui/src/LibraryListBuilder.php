<?php

namespace Drupal\libraries_provider_ui;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\libraries_provider\Entity\Library;
use Drupal\libraries_provider\Autoservice\LibrariesProviderManager;

/**
 * Defines a class to build a listing of library entities.
 *
 * @see \Drupal\libraries_provider\Entity\Library
 */
class LibraryListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entities = parent::load();

    // Load additional entities from the libraries data.
    $libraries = \Drupal::service(LibrariesProviderManager::class)->getManagedLibraries();
    foreach ($libraries as $libraryId => $library) {
      if (!isset($entities[$libraryId])) {
        $entities[$libraryId] = Library::create([
          'id' => $libraryId,
          'label' => $library['libraries_provider']['name'],
          'enabled' => $library['libraries_provider']['enabled'],
          'version' => $library['version'],
          'source' => $library['libraries_provider']['source'],
        ]);
      }
    }
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    $header['version'] = [
      'data' => $this->t('Version'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];
    $header['source'] = [
      'data' => $this->t('Source'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];
    $header['enabled'] = [
      'data' => $this->t('Enabled'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['name'] = [
      'data' => $entity->label(),
      'class' => ['library-label'],
    ];
    $row['version']['data'] = ['#markup' => $entity->get('version')];
    $row['source']['data'] = ['#markup' => $entity->get('source')];
    $row['enabled']['data'] = ['#markup' => $entity->isEnabled() ? $this->t('Yes') : $this->t('No')];
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if (isset($operations['edit'])) {
      $operations['edit']['title'] = $this->t('Edit library');
    }
    if (isset($operations['delete'])) {
      $operations['delete']['title'] = $this->t('Revert library to defaults');
    }

    if (
      $entity->isNew() &&
      $entity->access('add')
    ) {
      $operations['add'] = [
        'title' => $this->t('Override default configuration'),
        'weight' => 100,
        /* 'url' => $entity->urlInfo('edit-form'), */
        'url' => Url::FromRoute('entity.library.add_form', [
          'from_library' => $entity->id(),
        ]),
      ];
      unset($operations['edit']);
    }
    return $operations;
  }

}
