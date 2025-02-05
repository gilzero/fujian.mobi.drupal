<?php

namespace Drupal\rest_api_authentication\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\rest_api_authentication\MiniorangeApiAuthSupport;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Form for the requesting the trial request.
 */
class MiniornageAPIAuthnRequestTrial extends FormBase {
  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;
  use StringTranslationTrait;

  /**
   * Constructor.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'rest_api_authentication_request_support';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {

    $form['#prefix'] = '<div id="modal_example_form">';
    $form['#suffix'] = '</div>';
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    $form['radio_option'] = [
      '#type' => 'radios',
      '#title' => $this->t('Which type of trial would you prefer?'),
      '#options' => [
        'option1' => $this->t('Sandbox'),
        'option2' => $this->t('On-Premise'),
      ],
      '#default_value' => ($form_state->getValue('radio_option')) ? $form_state->getValue('radio_option') : 'option1',
      '#attributes' => ['class' => ['container-inline']],
      '#ajax' => [
        'callback' => '::updateFormElements',
        'wrapper' => 'additional-fields-wrapper',
      ],
    ];

    $form['additional_fields_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'additional-fields-wrapper'],
    ];

    $form['rest_api_authentication_trial_email_address'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#attributes' => [
        'placeholder' => $this->t('Enter your email'),
        'style' => 'width:99%;margin-bottom:1%;',
      ],
      '#states' => [
        'visible' => [
          ':input[name="radio_option"]' => ['value' => 'option2'],
        ],
        'required' => [
          ':input[name="radio_option"]' => ['value' => 'option2'],
        ],
      ],

    ];

    $form['rest_api_authentication_trial_description'] = [
      '#type' => 'textarea',
      '#rows' => 4,
      '#title' => $this->t('Description'),
      '#attributes' => [
        'placeholder' => $this->t('Describe your use case here!'),
        'style' => 'width:99%;',
      ],
      '#states' => [
        'visible' => [
          ':input[name="radio_option"]' => ['value' => 'option2'],
        ],
        'required' => [
          ':input[name="radio_option"]' => ['value' => 'option2'],
        ],
      ],
    ];

    $form['rest_api_authentication_trial_note'] = [
      '#markup' =>
      '<div>' .
      $this->t('If you have any questions or in case you need any sort of assistance in configuring our module according to your requirements, you can get in touch with us on <a href="mailto:@email">@email</a> and we will assist you further.', [
        '@email' => 'drupalsupport@xecurify.com',
      ]) .
      '</div>',
      '#states' => [
        'visible' => [
          ':input[name="radio_option"]' => ['value' => 'option2'],
        ],
      ],
    ];

    $form['submit_button_option1'] = [
      '#type' => 'submit',
      '#value' => $this->t('Go to Sandbox'),
      '#attributes' => [
        'class' => ['option1-submit', 'use-ajax', 'button--primary'],
        'formtarget' => '_blank',
      ],
      '#prefix' => '<div class="option1-submit-wrapper">',
      '#suffix' => '</div>',
      '#states' => [
        'visible' => [
          ':input[name="radio_option"]' => ['value' => 'option1'],
        ],
      ],
      '#submit' => ['::goToSandbox'],
    ];

    $form['submit_button_other_options'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#attributes' => [
        'class' => ['other-options-submit', 'use-ajax', 'button--primary'],
      ],
      '#prefix' => '<div class="other-options-submit-wrapper">',
      '#suffix' => '</div>',
      '#states' => [
        'visible' => [
          ':input[name="radio_option"]' => ['value' => 'option2'],
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'submitModalFormAjax'],
        'event' => 'click',
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    return $form;
  }

  /**
   * Returns the email address.
   *
   * @return string|null
   *   The email address, or an empty string if invalid.
   */
  public static function getEmail() {
    $user  = \Drupal::currentUser()->getEmail();
    $email = \Drupal::config('rest_api_authentication.settings')->get('rest_api_authentication_customer_admin_email');
    $email = !empty($email) ? $email : $user;
    $email = preg_match('/^(?!.*(?:noreply|no-reply)).*$/i', $email) ? $email : '';
    return $email;
  }

  /**
   * Return the response and redirect the user to the sandbox.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function goToSandbox(array $form, FormStateInterface $form_state) {
    $url = Url::fromUri('https://playground.miniorange.com/drupal.php', [
      'query' => [
        'email' => self::getEmail(),
        'mo_module' => 'rest_api_authentication',
        'drupal_version' => '10',
      ],
    ])->toString();
    $response = new TrustedRedirectResponse($url);
    $form_state->setResponse($response);
  }

  /**
   * Update the form elements based on the selected option.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return mixed
   *   The updated form element.
   */
  public function updateFormElements(array &$form, FormStateInterface $form_state) {
    $selected_value = $form_state->getValue('radio_option');
    if ($selected_value === 'option1') {
      $form['actions']['send']['submit']['#value'] = $this->t('Confirm Option 1');
    }
    elseif ($selected_value === 'option2') {
      $form['actions']['send']['#value'] = $this->t('Confirm Option 2');
    }
    return $form['additional_fields_wrapper'];
  }

  /**
   * Process the 'modal_example_form' Form.
   *
   * @param array $form
   *   Form element of the 'modal_example_form'.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   Object of AjaxResponse.
   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    $response = new AjaxResponse();
    $email = $form_values['rest_api_authentication_trial_email_address'];
    // If there are any form errors, AJAX replace the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#modal_example_form', $form));
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $this->messenger->addMessage($this->t('The email address <b><em>%email</em></b> is not valid.', ['%email' => $email]), 'error');
      $response->addCommand(new ReplaceCommand('#modal_example_form', $form));
    }
    else {
      $query = $form_values['rest_api_authentication_trial_description'];
      $query_type = 'trial';

      $support = new MiniorangeApiAuthSupport($email, '', $query, $query_type);
      $support_response = $support->sendSupportQuery();
      if ($support_response) {
        $message = [
          '#type' => 'item',
          '#markup' => $this->t('Your request for a trial module was sent successfully. Please allow us some time and we will send you the trial module as soon as possible.'),
        ];
        $ajax_form = new OpenModalDialogCommand('Thank you!', $message, ['width' => '50%']);
      }
      else {
        $error = [
          '#type' => 'item',
          '#markup' => $this->t('Error submitting the support query. Please send us your query at
      <a href="mailto:drupalsupport@xecurify.com">
      drupalsupport@xecurify.com</a>.'),
        ];
        $ajax_form = new OpenModalDialogCommand('Error!', $error, ['width' => '50%']);
      }

      $response->addCommand($ajax_form);
    }

    return $response;
  }

  /**
   * {@inheritDoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

}
