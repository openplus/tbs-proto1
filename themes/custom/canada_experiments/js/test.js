/**
 * @file
 * Drupal WxT Bootstrap object.
 */

/**
 * All Drupal WxT Bootstrap JavaScript APIs are contained in this namespace.
 *
 * @namespace
 */
(function($, Drupal) {
    'use strict';

    Drupal.wxt_bootstrap = {
        settings: drupalSettings.wxt_bootstrap || {},
    };

    // alert('A custom JS file - loads this alert!');
    console.log('Canada Experiments theme active.');
    // console.log('!!!>>???');
  
    /**
     * Returns the version of WxT being used.
     *
     * @return {string}
     *   The version of WxT being used.
     */
    Drupal.wxt_bootstrap.version = 'WxT v4.0.29';

})(window.jQuery, window.Drupal, window.drupalSettings);
