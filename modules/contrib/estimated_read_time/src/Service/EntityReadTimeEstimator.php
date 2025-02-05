<?php

namespace Drupal\estimated_read_time\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Theme\ThemeInitializationInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\node\Entity\Node;

/**
 * Provides read time estimator for entities.
 */
class EntityReadTimeEstimator implements EntityReadTimeEstimatorInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The read time service.
   *
   * @var \Drupal\estimated_read_time\Service\ReadTimeAdapterInterface
   */
  protected $readTime;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The theme initialization service.
   *
   * @var \Drupal\Core\Theme\ThemeInitializationInterface
   */
  protected $themeInitialization;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * Constructs an EntityReadTimeEstimator service object.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    EntityTypeManagerInterface $entityTypeManager,
    ReadTimeAdapterInterface $readTime,
    RendererInterface $renderer,
    ThemeInitializationInterface $themeInitialization,
    ThemeManagerInterface $themeManager,
  ) {
    $this->configFactory = $configFactory;
    $this->entityTypeManager = $entityTypeManager;
    $this->readTime = $readTime;
    $this->renderer = $renderer;
    $this->themeInitialization = $themeInitialization;
    $this->themeManager = $themeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function setEstimatedReadTime(FieldableEntityInterface $entity): void {

    $fieldDefinitions = $entity->getFieldDefinitions();

    foreach ($fieldDefinitions as $field) {
      if ($field->getType() === 'estimated_read_time') {
        $name = $field->getName();

        // The user may set the estimated read time manually.
        if (($entity->get($name)->auto ?? 1) === 0) {
          continue;
        }

        $viewMode = $field->getSetting('view_mode');
        $wordsPerMinute = $field->getSetting('words_per_minute');

        // Save the active theme so that we can switch back to it later.
        $activeTheme = $this->themeManager->getActiveTheme();

        // Set the theme to the default, frontend theme so that the text used to
        // calculate the read time is generated from the frontend theme. The
        // admin theme will not be accurate.
        $defaultThemeName = $this->configFactory->get('system.theme')->get('default');
        $defaultTheme = $this->themeInitialization->getActiveThemeByName($defaultThemeName);
        $this->themeManager->setActiveTheme($defaultTheme);

        $estimation = $this->doEstimate($entity, $viewMode, $wordsPerMinute);
        $entity->set($name, $estimation + ['auto' => 1]);

        // The entity can have multiple translations. Existing translations are
        // checked for changes, and if a change is detected in a translation
        // other than the current language, the read time is estimated for that
        // translation. Additionally, the entity may be translatable, but the
        // field may not be translatable.
        if ($entity instanceof TranslatableInterface && array_key_exists($name, $entity->getTranslatableFields())) {

          foreach ($entity->getTranslationLanguages() as $language) {
            $langcode = $language->getId();

            // A translation may not exist. The current one was processed.
            if (!$entity->hasTranslation($langcode) || $langcode === $entity->language()->getId()) {
              continue;
            }

            // Process the translation only if there are changes.
            $translation = $entity->getTranslation($langcode);
            if ($translation->hasTranslationChanges()) {
              $estimation = $this->doEstimate($translation, $viewMode, $wordsPerMinute);
              $translation->set($name, $estimation + ['auto' => 1]);
            }
          }
        }

        // Set the theme back to the original active theme.
        $this->themeManager->setActiveTheme($activeTheme);
      }
    }
  }

  /**
   * Estimates the read time for a given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to estimate the read time.
   * @param string $viewMode
   *   The view mode to use when rendering the entity.
   * @param int $wordsPerMinute
   *   The number of words per minute used to calculate the read time.
   *
   * @return array
   *   An array containing the estimated read time in minutes and seconds.
   */
  protected function doEstimate(EntityInterface $entity, string $viewMode, int $wordsPerMinute): array {

    if ($entity instanceof Node) {
      // Set in_preview to TRUE to prevent errors when rendering the links field
      // without a node ID.
      // In order to obtain the read time, the entity is rendered.
      // The Links field that is displayed by default on nodes will throw an
      // error if the node ID does not exist, which will occur for new nodes.
      // The node module prevents this error from occurring when previewing a
      // new node by setting the in_preview property and not building the links
      // if in_preview is set to TRUE.
      $inPreview = $entity->in_preview;
      $entity->in_preview = TRUE;
    }

    $viewBuilder = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
    $build = $viewBuilder->view($entity, $viewMode, $entity->language()->getId());
    $content = (string) $this->renderer->renderInIsolation($build);

    if ($entity instanceof Node) {
      // Set the in_preview property back to it's initial value. This should
      // almost always be NULL.
      $entity->in_preview = $inPreview;
    }

    return $this->readTime->estimate($content, $wordsPerMinute);
  }

}
