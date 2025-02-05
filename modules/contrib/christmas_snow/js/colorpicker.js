(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.colorPicker = {
    attach: function (context, settings) {

      $(document).ready(function () {
        $('#color-picker').farbtastic('#edit-christmas-snow-snowcolor');
      });

    }
  };

})(jQuery, Drupal);
