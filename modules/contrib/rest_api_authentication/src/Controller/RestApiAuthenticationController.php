<?php

namespace Drupal\rest_api_authentication\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller for the rest api authentication module.
 */
class RestApiAuthenticationController extends ControllerBase {
  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The Request Stack Service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructor.
   */
  public function __construct(FormBuilderInterface $form_builder, RequestStack $request_stack) {
    $this->formBuilder = $form_builder;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder'),
      $container->get('request_stack')
    );
  }

  /**
   * Opens the support request form in a modal dialog.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response.
   */
  public function openSupportRequestForm() {
    $response = new AjaxResponse();
    $modal_form = $this->formBuilder->getForm('\Drupal\rest_api_authentication\Form\MiniornageAPIAuthnRequestSupport');
    $request = $this->requestStack->getCurrentRequest();
    if ($request->isXmlHttpRequest()) {
      $response->addCommand(new OpenModalDialogCommand($this->t('Support Request/Contact Us'), $modal_form, ['width' => '40%']));
    }
    else {
      $response = $modal_form;
    }
    return $response;
  }

  /**
   * Opens the trial request form in a modal dialog.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The AJAX response.
   */
  public function openTrialRequestForm() {
    $response = new AjaxResponse();
    $modal_form = $this->formBuilder->getForm('\Drupal\rest_api_authentication\Form\MiniornageAPIAuthnRequestTrial');
    $request = $this->requestStack->getCurrentRequest();

    if ($request->isXmlHttpRequest()) {
      $response->addCommand(new OpenModalDialogCommand('Request 7-Days Full Feature Trial License', $modal_form, ['width' => '40%']));
    }
    else {
      $response = $modal_form;
    }
    return $response;
  }

}
