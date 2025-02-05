CONTENTS OF THIS FILE
---------------------

* Introduction
* Requirements
* Installation
* Configuration
* Maintainers


INTRODUCTION
------------

The Estimated Read Time module adds a new field type that calculates the time it
takes to read that entity's content. The read time is calculated based on the
content displayed in a selected view mode.

* For a full description of the module, visit the project page:
  https://www.drupal.org/project/estimated_read_time

* To submit bug reports and feature suggestions, or to track changes:
  https://www.drupal.org/project/issues/estimated_read_time


REQUIREMENTS
------------

This module requires the following packages outside of Drupal:

* mtownsend/read-time - https://github.com/mtownsend5512/read-time


INSTALLATION
------------

* Install as you would normally install a contributed Drupal module. Visit
  https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

    1. Add a new field to your entity.
    2. Select the "Read Time" field type.
    3. On the field settings page, select a view mode. The content in the view
       mode will be used to estimate the read time.
    4. There is also a setting available to change the reading speed (word per
       minute).
    5. The read time is calculated on entity save.


MAINTAINERS
-----------

* Mike Taltavull (mtalt) - https://www.drupal.org/u/mtalt
