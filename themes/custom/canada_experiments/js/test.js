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

jQuery(document).on("wb-ready.wb", function (event) {
    if (window.location.href.indexOf("layered-navigation-master-file") > -1) {
        let searchParams = new URLSearchParams(window.location.search);
        if (searchParams.has('t')) {
            let a = $('a').filter(function(index) { return $(this).text() === searchParams.get("t"); });
            a.trigger('mouseenter');
        }
        $("#wb-auto-3 *").on("mouseleave",function() {
            event.stopPropagation();
        });
        $("#wb-auto-3 *").on("mouseenter",function() {
            event.stopPropagation();
        });
    }
});
