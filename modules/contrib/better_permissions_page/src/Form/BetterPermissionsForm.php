<?php

namespace Drupal\better_permissions_page\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Form\UserPermissionsForm;
use Drupal\user\RoleInterface;

/**
 * Provides a better & faster user permissions administration form.
 */
class BetterPermissionsForm extends UserPermissionsForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'better_permissions_page_user_admin_permissions';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $providers = [];
    $permissions_by_provider = $this->permissionsByProvider();
    foreach (array_keys($permissions_by_provider) as $provider) {
      $providers[$provider] = $this->moduleHandler->getName($provider);
    }

    $form['providers'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => $this->t('Permision provider'),
      '#options' => $providers,
      '#ajax' => [
        'callback' => '::getPermissions',
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Fetching permissions...'),
        ],
        'wrapper' => 'permissions-wrapper',
      ],
      '#description' => $this->t('Select a permission provider to fetch the table with permissions.'),
    ];

    $form['roles'] = [
      '#type' => 'select',
      '#multiple' => TRUE,
      '#title' => $this->t('Roles:'),
      '#options' => array_map(fn(RoleInterface $role) => Html::escape($role->label()), $this->roleStorage->loadMultiple()),
      '#ajax' => [
        'callback' => '::getPermissions',
        'event' => 'change',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Fetching permissions...'),
        ],
        'wrapper' => 'permissions-wrapper',
      ],
      '#states' => [
        'invisible' => [
          'select[name="providers[]"]' => ['value' => []],
        ],
      ],
      '#description' => $this->t('Choose which roles to display.')
    ];

    $form['permissions'] = [
      '#type' => 'table',
      '#title' => $this->t('Permissions'),
      '#attributes' => ['class' => ['permissions']],
      '#sticky' => TRUE,
      '#prefix' => '<div id="permissions-wrapper">',
      '#suffix' => '</div>',
    ];

    if ($form_state->getValues() && $providers = $form_state->getValue('providers')) {
      $form['providers']['#description'] = 'l';
      // Construct the permissions table header.
      $form['permissions']['#header'][] = $this->t('Permission');

      /** @var \Drupal\user\RoleInterface $role */
      foreach ($this->getRoles() as $role_name => $role) {
        $roles = $form_state->getValue('roles');
        if (empty($roles) || in_array($role_name, $roles)) {
          // Retrieve role names for columns.
          $role_names[$role_name] = $role->label();
          // Fetch permissions for the roles.
          $role_permissions[$role_name] = $role->getPermissions();
          $admin_roles[$role_name] = $role->isAdmin();
          // Populate the permissions table header with the remaining columns.
          $form['permissions']['#header'][] = $role->label();
        }
      }

      // Store $role_names for use when saving the data.
      $form['role_names'] = [
        '#type' => 'value',
        '#value' => $role_names,
      ];

      foreach ($providers as $provider) {
        // Module name.
        $form['permissions'][$provider] = [
          [
            '#wrapper_attributes' => [
              'colspan' => count($role_names) + 1,
              'class' => ['module'],
              'id' => 'module-' . $provider,
            ],
            '#markup' => $this->moduleHandler->getName($provider),
          ],
        ];
        foreach ($permissions_by_provider[$provider] as $perm => $perm_item) {
          // Fill in default values for the permission.
          $perm_item += [
            'description' => '',
            'restrict access' => FALSE,
            'warning' => !empty($perm_item['restrict access']) ? $this->t('Warning: Give to trusted roles only; this permission has security implications.') : '',
          ];
          $form['permissions'][$perm]['description'] = [
            '#type' => 'inline_template',
            '#template' => '<div class="permission"><span class="title">{{ title }}</span>{% if description or warning %}<div class="description">{% if warning %}<em class="permission-warning">{{ warning }}</em> {% endif %}{{ description }}</div>{% endif %}</div>',
            '#context' => [
              'title' => $perm_item['title'],
              'warning' => $perm_item['warning'],
              'description' => $perm_item['description'],
            ],
          ];
          foreach ($role_names as $rid => $name) {
            $checked = in_array($perm, $role_permissions[$rid]);
            $form['permissions'][$perm][$rid] = [
              '#title' => $name,
              '#title_display' => 'invisible',
              '#type' => 'checkbox',
              '#disabled' => $admin_roles[$rid] ? TRUE : FALSE,
              '#default_value' => ($checked || $admin_roles[$rid]) ? TRUE : FALSE,
              '#attributes' => ['class' => ['role-checkbox']],
            ];
          }
        }
      }
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save permissions'),
      '#button_type' => 'primary',
      // Hide the submit form button if there is no selected provider value.
      '#states' => [
        'invisible' => [
          'select[name="providers[]"]' => ['value' => []],
        ],
      ],
    ];

    $form['#attached']['library'][] = 'better_permissions_page/better_permissions_page';

    return $form;
  }

  /**
   * The ajax callback used to get the permissions list by the provider.
   *
   * @param array $form
   *   The form object.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   Returns the ajax processed permissions form element.
   */
  public function getPermissions(array &$form, FormStateInterface $form_state): array {
    return $form['permissions'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get the new configured permissions.
    $permissions = $form_state->getValue('permissions');

    if ($permissions) {
      $mapped = [];
      foreach ($permissions as $perm_name => $roles) {
        foreach ($roles as $role => $checked) {
          $mapped[$role][$perm_name] = $checked;
        }
      }

      foreach ($mapped as $role_name => $perms) {
        // Update the permission for the context role/permission.
        user_role_change_permissions($role_name, $perms);
      }
    }

    $this->messenger()->addStatus($this->t('The changes have been saved.'));
    $form_state->setRedirect(
      'user.admin_permissions',
      [],
      ['fragment' => 'm-' .  implode(',', $form_state->getValue('providers'))]
    );
  }

}
