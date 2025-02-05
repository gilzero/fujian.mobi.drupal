<?php

namespace Drupal\custom_add_content\Controller;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\Controller\NodeController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Node routes.
 */
class CustomAddContentController extends NodeController implements ContainerInjectionInterface {

  /**
   * Menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * Configuration object.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Creates a new CustomAddContentController instance.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menuLinkTree
   *   Menu tree service.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Configuration factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    MenuLinkTreeInterface $menuLinkTree,
    ConfigFactory $configFactory,
    RendererInterface $renderer,
  ) {
    $this->menuLinkTree = $menuLinkTree;
    $this->config = $configFactory->get('custom_add_content.config');
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('menu.link_tree'),
      $container->get('config.factory'),
      $container->get('renderer'),
    );
  }

  /**
   * Displays add content links for available content types.
   *
   * Redirects to node/add/[type] if only one content type is available.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the node types that can be added; however,
   *   if there is only one node type defined for the site, the function
   *   will return a RedirectResponse to the node add page for that one node
   *   type.
   */
  public function addPage() {
    $level = 4;
    $menu_name = 'custom-add-content-page';

    $menu_renderer = $this->config->get('custom_add_content_renderer');

    $parameters = $this->menuLinkTree->getCurrentRouteMenuTreeParameters($menu_name);
    $parameters->setMaxDepth($level + 1);
    $tree = $this->menuLinkTree->load($menu_name, $parameters);

    if (is_array($tree) && count($tree) > 0) {

      $manipulators = [
        ['callable' => 'menu.default_tree_manipulators:checkAccess'],
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
      ];
      $tree = $this->menuLinkTree->transform($tree, $manipulators);
      $menu = $this->menuLinkTree->build($tree);

      if ($menu_renderer == 0) {
        $markup = $this->renderer->render($menu);
      }
      else {
        // Call to custom twig template.
        return [
          '#theme' => 'custom_add_content_page_add',
          '#menu_name' => $menu['#menu_name'],
          '#items' => $menu['#items'],
          '#attached' => [
            'library' => ['custom_add_content/custom_add_content'],
          ],
        ];
      }
    }
    else {
      $markup = '<p>' . $this->t('Please, make sure custom_add_content_page menu has links.') . '</p>';
    }

    $build = [
      '#type' => 'markup',
      '#markup' => $markup,
    ];

    return $build;
  }

}
