# Custom Add Content Page

The Custom add content page module allows the user to customize the "add
content" page by converting it to a hierarchical menu named "Custom add content
page". By configuring this menu the user will be able to:

 - Sort creation links and establish a hierarchy
 - Hide creation links

When a content type is created or deleted the menu is automatically updated.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/custom_add_content).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/custom_add_content).


## Table of contents

 - Requirements
 - Recommended Modules
 - Installation
 - Configuration
 - Maintainers


## Requirements

This module requires no modules outside of Drupal core.


## Recommended modules

[Special menu items](https://www.drupal.org/project/special_menu_items)
[Menu item visibility](https://www.drupal.org/project/menu_item_visibility)


## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration

1. Navigate to Administration » Extend and enable the module.
2. Navigate to Administration » Configurations » User Interface » Custom
   Add Content to configure.
3. Select the desired menu renderer from the dropdown.
   - Drupal's core renderer
   - Module's custom renderer: which uses the provided template
     `custom-add-content-page-add.html.twig` which can be
     overridden by copying it to site's theme.
4. Save configuration.


## Maintainers

- Roger Codina - [rcodina](https://www.drupal.org/u/rcodina)
