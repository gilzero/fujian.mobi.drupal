<?php

namespace Drupal\sitewide_alert\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\sitewide_alert\Entity\SitewideAlertInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SitewideAlertController.
 *
 *  Returns responses for Sitewide Alert routes.
 */
class SitewideAlertController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Constructs a new SitewideAlertController.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route.
   */
  public function __construct(
    protected DateFormatterInterface $dateFormatter,
    protected RendererInterface $renderer,
    protected RouteMatchInterface $routeMatch,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): SitewideAlertController {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer'),
      $container->get('current_route_match')
    );
  }

  /**
   * Displays a Sitewide Alert revision.
   *
   * @param int $sitewide_alert_revision
   *   The Sitewide Alert revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function revisionShow(int $sitewide_alert_revision): array {
    /** @var \Drupal\Core\Entity\RevisionableStorageInterface $sitewide_alert_storage */
    $sitewide_alert_storage = $this->entityTypeManager()->getStorage('sitewide_alert');
    return $this
      ->entityTypeManager()
      ->getViewBuilder('sitewide_alert')
      ->view($sitewide_alert_storage);
  }

  /**
   * Page title callback for a Sitewide Alert revision.
   *
   * @param int $sitewide_alert_revision
   *   The Sitewide Alert revision ID.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The page title.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function revisionPageTitle(int $sitewide_alert_revision): TranslatableMarkup {
    /** @var \Drupal\Core\Entity\RevisionableStorageInterface $sitewide_alert_storage */
    $sitewide_alert_storage = $this->entityTypeManager()->getStorage('sitewide_alert');
    $sitewide_alert = $sitewide_alert_storage->loadRevision($sitewide_alert_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $sitewide_alert->label(),
      '%date' => $this->dateFormatter->format($sitewide_alert->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Sitewide Alert.
   *
   * @param \Drupal\sitewide_alert\Entity\SitewideAlertInterface $sitewide_alert
   *   A Sitewide Alert object.
   *
   * @return array
   *   An array as expected by drupal_render().
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function revisionOverview(SitewideAlertInterface $sitewide_alert): array {
    $account = $this->currentUser();
    $sitewide_alert_storage = $this->entityTypeManager()->getStorage('sitewide_alert');

    $langcode = $sitewide_alert->language()->getId();
    $langName = $sitewide_alert->language()->getName();
    $languages = $sitewide_alert->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations
      ? $this->t('@$langName revisions for %title',
        ['@$langName' => $langName, '%title' => $sitewide_alert->label()])
      : $this->t('Revisions for %title',
        ['%title' => $sitewide_alert->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all sitewide alert revisions") || $account->hasPermission('administer sitewide alert entities')));
    $delete_permission = (($account->hasPermission("delete all sitewide alert revisions") || $account->hasPermission('administer sitewide alert entities')));

    $rows = [];

    $vids = array_column($sitewide_alert_storage->getAggregateQuery()
      ->allRevisions()
      ->condition('id', $sitewide_alert->id())
      ->groupBy('vid')
      ->accessCheck()
      ->execute(), 'vid');

    $latest_revision = TRUE;

    /** @var \Drupal\Core\Entity\RevisionableStorageInterface $entity_storage */
    $entity_storage = $this->entityTypeManager()->getStorage('sitewide_alert');
    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\sitewide_alert\Entity\SitewideAlertInterface $revision */
      $revision = $entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => Link::fromTextAndUrl(
                $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short'),
                new Url('entity.sitewide_alert.revision', [
                  'sitewide_alert' => $sitewide_alert->id(),
                  'sitewide_alert_revision' => $vid,
                ])
              )->toString(),
              'username' => $this->renderer->renderInIsolation($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          unset($current);
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.sitewide_alert.translation_revert', [
                'sitewide_alert' => $sitewide_alert->id(),
                'sitewide_alert_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.sitewide_alert.revision_revert', [
                'sitewide_alert' => $sitewide_alert->id(),
                'sitewide_alert_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.sitewide_alert.revision_delete', [
                'sitewide_alert' => $sitewide_alert->id(),
                'sitewide_alert_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['sitewide_alert_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

  /**
   * Edit route title callback.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null
   *   The title for the sitewide alert edit page, if an entity was found.
   */
  public function editTitle(): ?TranslatableMarkup {
    $sitewideAlert = $this->routeMatch->getParameter('sitewide_alert');
    if (!$sitewideAlert instanceof SitewideAlertInterface) {
      return NULL;
    }

    return $this->t('Edit %label', ['%label' => $sitewideAlert->label()]);
  }

}
