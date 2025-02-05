(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.betterPermissionsPage = {
    attach: (context) => {

      // Fire the logic only on page load when the context is the document.
      if (context == document) {
        let data = [];
        // Get the hash fragment from the url.
        let hash = window.location.hash;
        // Get the provider module from the fragment.
        let providers = hash.replace(/#m-/g, "");
        // Check if there is such a provide in the select values.
        $.each(providers.split(','), function(index, element){
          if ($('select[name="providers[]"] option[value="' + element + '"]').length > 0) {
            data.push(element);
          }
        });

        // Set the option to the providers select if there is a fragment.
        if (data) {
          $('select[name="providers[]"]').val(data).change();
        }
      }
    }
  };

})(jQuery, Drupal);
