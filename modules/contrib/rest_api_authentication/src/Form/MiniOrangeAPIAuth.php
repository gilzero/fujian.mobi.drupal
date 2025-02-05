<?php

namespace Drupal\rest_api_authentication\Form;

use Drupal\Component\Utility\EmailValidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\rest_api_authentication\AdvancedSettingsForm;
use Drupal\rest_api_authentication\MoHeadlessIntegration;
use Drupal\rest_api_authentication\RequestForDemoForm;
use Drupal\rest_api_authentication\SetupAuthenticationForm;
use Drupal\rest_api_authentication\UpgradePlansForm;
use Drupal\rest_api_authentication\Utilities;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Url;


/**
 * Provides a form for configuring MiniOrange API Authentication module.
 */
class MiniOrangeAPIAuth extends FormBase {
  /**
   * Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Request Stack Service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;
  /**
   * Email validator interface object.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected EmailValidatorInterface $emailValidator;

  /**
   * Constructor.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    RequestStack $request_stack,
    MessengerInterface $messenger,
    EmailValidatorInterface $email_validator,
  ) {
    $this->configFactory = $config_factory;
    $this->requestStack = $request_stack;
    $this->messenger = $messenger;
    $this->emailValidator = $email_validator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('request_stack'),
      $container->get('messenger'),
      $container->get('email.validator'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'rest_api_authentication_config_client';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $auth_method = $this->configFactory->get('authentication_method');
    if (empty($auth_method) || $auth_method = NULL) {
      $this->configFactory->getEditable('rest_api_authentication.settings')->set('authentication_method', 0)->save();
    }
    $form['markup_library_1'] = [
      '#attached' => [
        'library' => [
          "rest_api_authentication/rest_api_authentication.basic_style_settings",
        ],
      ],
    ];

    $current_request = $this->requestStack->getCurrentRequest();
    $tab = $current_request->query->get('tab');
    $tab = (isset($tab)) ? ($tab) : ('edit-api-auth');

    $form['verticaltabs'] = [
      '#type' => 'vertical_tabs',
      '#default_tab' => $tab,
    ];

    // Builds and inserts the Setup Authentication Method form.
    SetupAuthenticationForm::insertForm($form, $form_state);

    // Builds and inserts the Advanced Settings form.
    AdvancedSettingsForm::insertForm($form, $form_state);

    // Builds and inserts the headless SSO form.
    MoHeadlessIntegration::insertForm($form, $form_state);

    // Builds and inserts the Request For Demo form.
    RequestForDemoForm::insertForm($form, $form_state);

    // Builds and inserts the Upgrade Plans form.
    UpgradePlansForm::insertForm($form, $form_state);

    return $form;
  }

  /**
   * Function used to save the basic authentication method.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current state of the form elements.
   *
   * @return void
   *   return nothing.
   */
  public function restApiAuthenticationSaveBasicConfig(array &$form, FormStateInterface $form_state): void {
    $base_url = Utilities::getBaseUrl();
    $form_input = $form_state->getValues();
    $enable_authentication = $form_input['enable_authentication'];

    $this->configFactory->getEditable('rest_api_authentication.settings')->set('enable_authentication', $enable_authentication)->save();
    $this->messenger->addMessage($this->t('Settings Saved Successfully.'));
    $response = new RedirectResponse($base_url . "/admin/config/people/rest_api_authentication/auth_settings?tab=edit-api-auth");
    $response->send();
  }

  /**
   * Generate API key for the API key authentication method.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current state of the form elements.
   *
   * @return void
   *   return nothing.
   */
  public function restApiAuthenticationGenerateApiToken(array &$form, FormStateInterface $form_state) {
    $base_url = Utilities::getBaseUrl();
    $api_key = Utilities::generateRandom(64);
    $this->configFactory->getEditable('rest_api_authentication.settings')
      ->set('api_token', $api_key)
      ->set('authentication_method', 1)->save();
    $this->messenger->addMessage($this->t('New API Key generated successfully.'));
    $response = new RedirectResponse($base_url . "/admin/config/people/rest_api_authentication/auth_settings?tab=edit-api-auth");
    $response->send();
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $base_url = Utilities::getBaseUrl();
    $list_of_apis = $form['advancedsettings']['support_container_outline']['list_apis']['api_textarea']['#value'];
    $api_access = $form['advancedsettings']['support_container_outline']['list_apis']['settings']['#value'];
    $this->configFactory->getEditable('rest_api_authentication.settings')
      ->set('api_access_type', $api_access)
      ->set('list_of_apis', $list_of_apis)->save();
    $this->messenger->addMessage($this->t('Configurations for API Based Restriction saved successfully.'));
    $response = new RedirectResponse($base_url . "/admin/config/people/rest_api_authentication/auth_settings?tab=edit-advanced-settings");
    $response->send();
  }

  /**
   * Save the basic authentication method.
   */
  public function restApiAuthenticationSaveBasicAuthConf(array &$form, FormStateInterface $form_state) {
    $base_url = Utilities::getBaseUrl();
    $this->configFactory->getEditable('rest_api_authentication.settings')->set('authentication_method', 0)->save();
    $this->messenger->addMessage($this->t('Configurations saved successfully.'));
    $response = new RedirectResponse($base_url . "/admin/config/people/rest_api_authentication/auth_settings?tab=edit-api-auth");
    $response->send();
  }

  /**
   * Send a request for Demo.
   */
  public function savedDemoRequest(array &$form, FormStateInterface $form_state) {
    $base_url = Utilities::getBaseUrl();
    $email = trim($form['demo']['container_outline']['rest_api_authentication_email_address']['#value']);
    $phone = $form['demo']['container_outline']['rest_api_authentication_phone_number']['#value'];
    $query = trim($form['demo']['container_outline']['rest_api_authentication_demo_query']['#value']);
    Utilities::sendSupportQuery($email, $phone, $query, 'demo');
    $response = new RedirectResponse($base_url . "/admin/config/people/rest_api_authentication/auth_settings?tab=edit-demo");
    $response->send();
  }

}
