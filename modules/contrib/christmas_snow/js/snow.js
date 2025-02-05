(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.snow = {
    attach: function (context, settings) {

      snowStorm.flakesMaxActive = drupalSettings.christmas_snow.flakes_max;
      snowStorm.snowColor = drupalSettings.christmas_snow.snowcolor;
      snowStorm.flakeBottom = drupalSettings.christmas_snow.flakeBottom;
      snowStorm.followMouse = drupalSettings.christmas_snow.followMouse;
      snowStorm.useMeltEffect = drupalSettings.christmas_snow.useMeltEffect;
      snowStorm.snowStick = drupalSettings.christmas_snow.snowStick;
      snowStorm.useTwinkleEffect = drupalSettings.christmas_snow.useTwinkleEffect;
      snowStorm.snowCharacter = drupalSettings.christmas_snow.snowCharacter;
      snowStorm.animationInterval = drupalSettings.christmas_snow.animationInterval;

    }
  };

})(jQuery, Drupal);
