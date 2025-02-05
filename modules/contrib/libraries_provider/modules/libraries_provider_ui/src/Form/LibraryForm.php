<?php

namespace Drupal\libraries_provider_ui\Form;

use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Extension\Exception\UnknownExtensionException;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Drupal\libraries_provider\AutoPluginManager\LibrarySourcePluginManager;
use Drupal\libraries_provider\Autoservice\LibrariesProviderManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form for library edit forms.
 *
 * @internal
 */
class LibraryForm extends EntityForm {

  /**
   * The info stored in MODULENAME.libraries.yml.
   *
   * @var array
   */
  protected $libraryDefinition;

  /**
   * Class constructor.
   */
  public function __construct(
    protected LibrarySourcePluginManager $sourcePluginManager,
    protected LibraryDiscoveryInterface $libraryDiscovery,
    protected LibrariesProviderManager $librariesProviderManager,
    protected ModuleExtensionList $moduleExtensionList,
    protected ThemeExtensionList $themeExtensionList,
    protected Token $token
  ) {
    $this->sourcePluginManager = $sourcePluginManager;
    $this->libraryDiscovery = $libraryDiscovery;
    $this->librariesProviderManager = $librariesProviderManager;
    $this->moduleExtensionList = $moduleExtensionList;
    $this->themeExtensionList = $themeExtensionList;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get(LibrarySourcePluginManager::class),
      $container->get('library.discovery'),
      $container->get(LibrariesProviderManager::class),
      $container->get('extension.list.module'),
      $container->get('extension.list.theme'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $library = $this->entity;
    $libraryEntities = $this->entityTypeManager->getStorage('library')->loadMultiple();

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('Edit library %label', ['%label' => $library->label()]);
    }

    $sourcePlugins = $this->sourcePluginManager->createAllInstances();

    $sourceOptions = [];
    $sourceOptionsAttributes = [];
    $sourceVersions = [];
    $sourceDescription = '<p>' . $this->t('Choose the method for loading the library in the frontend.') . '</p>';
    foreach ($sourcePlugins as $pluginId => $pluginInstance) {
      $sourceOptions[$pluginId] = $pluginId;
      // Disable non-available sources.
      if ($pluginInstance->isAvailable($library->id())) {
        $sourceVersions[$pluginId] = $pluginInstance->getAvailableVersions($library->id());
        $sourceOptionsAttributes[$pluginId] = [
          'data-library-source-versions' => json_encode($sourceVersions[$pluginId]),
        ];
      }
      else {
        $sourceOptionsAttributes[$pluginId] = ['disabled' => 'disabled'];
        $sourceDescription .= $pluginInstance->getAvailabilityMessage($library->id());
      }
    }

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $library->isEnabled(),
    ];

    $form['source'] = [
      '#type' => 'select',
      '#title' => $this->t('Source'),
      '#default_value' => $library->get('source'),
      '#required' => TRUE,
      '#options' => $sourceOptions,
      '#options_attributes' => $sourceOptionsAttributes,
      '#required' => TRUE,
      '#description' => $sourceDescription,
    ];

    $form['version'] = [
      '#type' => 'select',
      '#title' => $this->t('Version'),
      '#default_value' => $library->get('version'),
      '#required' => TRUE,
      '#options' => $sourceVersions[$library->get('source')],
    ];

    $form['minified'] = [
      '#type' => 'select',
      '#title' => $this->t('When do you want to serve the library minified'),
      '#default_value' => $library->get('minified'),
      '#required' => TRUE,
      '#options' => [
        'always' => $this->t('Always'),
        'never' => $this->t('Never'),
        'when_aggregating' => $this->t('When aggregation is enabled'),
      ],
      '#description' => $this->t(
        'Configure the aggregation options at the <a href="@url">Performance settings page</a>',
        ['@url' => Url::fromRoute('system.performance_settings')->toString()]
      ),
    ];

    $variantOptions = [];
    $variantOptionsAttributes = [];
    foreach ($this->libraryDefinition['libraries_provider']['variants_available'] as $variant) {
      $variantKey = strtolower($variant['name']);
      $description = isset($variant['description']) ? ': ' . $variant['description'] : '';
      $variantOptions[$variantKey] = $variant['name'] . $description;
      if (isset($variant['url'])) {
        $variantOptionsAttributes[$variantKey] = [
          'data-library-variant-url' => $variant['url'],
        ];
      }
    }
    if ($variantOptions) {
      $variantDescription = '';
      // Set a default description for the variant..
      if (isset($variantOptionsAttributes[$library->get('variant')]['data-library-variant-url'])) {
        $variantDescription = $this->t('Learn more about this variant at <a href="@url">@url</a>', [
          '@url' => $variantOptionsAttributes[$library->get('variant')]['data-library-variant-url'],
        ]);
      }
      $form['variant'] = [
        '#type' => 'select',
        '#title' => $this->t('Variation of this library'),
        '#default_value' => $library->get('variant'),
        '#required' => TRUE,
        '#options' => $variantOptions,
        '#description' => $variantDescription,
        '#options_attributes' => $variantOptionsAttributes,
      ];
    }

    $replacesOptions = [];
    foreach (array_keys($library->get('replaces') ?? []) as $replacementId) {
      list($extension, $libraryName) = explode('__', $replacementId);
      $replacesOptions[$replacementId] = $this->t('"@libraryName" provided by the extension "@extension"', [
        '@libraryName' => $libraryName,
        '@extension' => $extension,
      ]);
    }
    foreach ($libraryEntities as $libraryId => $replacesLibrary) {
      if ($library->id() != $libraryId) {
        $replacesOptions[$libraryId] = $replacesLibrary->label();
      }
    }
    if ($replacesOptions) {
      $form['replaces'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Libraries replaced by this library'),
        '#description' => $this->t('Usually defaults are fine'),
        '#default_value' => $library->get('replaces'),
        '#options' => $replacesOptions,
      ];
    }

    $customOptionsDefinition = $this->libraryDefinition['libraries_provider']['custom_options'] ?? '';
    if ($customOptionsDefinition) {
      $requirements = $this->librariesProviderManager->getCustomOptionsRequirements($customOptionsDefinition);

      if ($requirements) {
        $form['custom_options_requirements'] = [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
          '#title' => $this->t('This library has custom options to set but you need to fullfil the following requirements first'),
          '#items' => $requirements,
        ];
      }
      else {
        list($extension, $libraryName) = explode('__', $library->id());
        try {
          $extensionPath = $this->themeExtensionList->getPath($extension);
        }
        catch (UnknownExtensionException $e) {
          $extensionPath = $this->moduleExtensionList->getPath($extension);
        }

        $schemaPath = $extensionPath . '/' . $this->token->replace($customOptionsDefinition['schema'], ['library' => $library]);
        if (!file_exists($schemaPath)) {
          $schemaPath = $extensionPath . '/' . str_replace('[library:version]', 'default', $customOptionsDefinition['schema']);
        }
        $customOptionsSchema = json_decode(file_get_contents($schemaPath));

        $customOptions = $library->get('custom_options');

        $form['custom_options'] = [
          '#type' => 'details',
          '#title' => $this->t('Custom options @overrides', [
            '@overrides' => $customOptions ? $this->t('(some overrides)') : '',
          ]),
          '#tree' => TRUE,
        ];
        if ($customOptions) {
          $form['custom_options']['overrides_list'] = [
            '#theme' => 'item_list',
            '#list_type' => 'ul',
            '#title' => $this->t('Variables overridden'),
            '#items' => [],
          ];
          foreach (array_keys($customOptions) as $overriddenCustomOption) {
            $form['custom_options']['overrides_list']['#items'][] = $overriddenCustomOption;
          }
        }

        foreach ($customOptionsSchema as $optionName => $option) {
          $description = $this->t('Original value: <code>@value</code>', ['@value' => $option->value]);
          if (isset($option->computed_value)) {
            $description .= '<br />' . $this->t('Original computed value: <code>@value</code>', ['@value' => $option->computed_value]);
          }

          $form['custom_options'][$optionName] = [
            '#type' => 'textfield',
            '#title' => $optionName,
            '#description' => $description,
            '#default_value' => $customOptions[$optionName] ?? '',
          ];
        }
      }
    }

    $form['#attached']['library'][] = 'libraries_provider_ui/libraryForm';

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $library = $this->entity;
    $customOptions = $library->get('custom_options') ?? [];
    foreach ($customOptions as $optionName => $optionValue) {
      if (empty($optionValue)) {
        unset($customOptions[$optionName]);
      }
    }
    $library->set('custom_options', $customOptions);

    $status = $library->save();
    $edit_link = $this->entity->toLink($this->t('Edit'), 'edit-form')->toString();
    if ($status == SAVED_UPDATED) {
      $this->messenger()->addStatus($this->t('Library %label has been updated.', ['%label' => $library->label()]));
      $this->logger('libraries_provider_ui')->notice('Library %label has been updated.', ['%label' => $library->label(), 'link' => $edit_link]);
    }
    else {
      $this->messenger()->addStatus($this->t('Library %label has been overridden.', ['%label' => $library->label()]));
      $this->logger('libraries_provider_ui')->notice('Library %label has been overridden.', ['%label' => $library->label(), 'link' => $edit_link]);
    }

    $form_state->setRedirectUrl($this->entity->toUrl('edit-form'));

    $this->libraryDiscovery->clearCachedDefinitions();
    return $status;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityFromRouteMatch(RouteMatchInterface $route_match, $entity_type_id) {
    // When adding new configuration the entity is built from
    // the library definition.
    $libraryId = $route_match->getParameter('from_library');
    if ($libraryId) {
      list($extension, $libraryName) = explode('__', $libraryId);
      $this->libraryDefinition = $this->libraryDiscovery->getLibraryByName($extension, $libraryName);
      return $this->createLibraryFromDefinition($this->libraryDefinition);
    }
    $library = parent::getEntityFromRouteMatch($route_match, $entity_type_id);
    list($extension, $libraryName) = explode('__', $library->id());
    $this->libraryDefinition = $this->libraryDiscovery->getLibraryByName($extension, $libraryName);
    return $library;
  }

  /**
   * Return a Library entity created from the values of the definition.
   */
  protected function createLibraryFromDefinition($libraryDefinition) {
    return $this->entityTypeManager->getStorage('library')->create([
      'id' => $libraryDefinition['libraries_provider']['id'],
      'label' => $libraryDefinition['libraries_provider']['name'],
      'enabled' => $libraryDefinition['libraries_provider']['enabled'],
      'version' => $libraryDefinition['version'],
      'source' => $libraryDefinition['libraries_provider']['source'],
      'minified' => $libraryDefinition['libraries_provider']['minified'],
      'variant' => $libraryDefinition['libraries_provider']['variant'],
      'replaces' => $libraryDefinition['libraries_provider']['replaces'],
    ]);
  }

}
