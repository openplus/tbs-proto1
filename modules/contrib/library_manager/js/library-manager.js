/**
 * @file
 * Library manager behaviors.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Filters the library list table by a text input search string.
   */
  Drupal.behaviors.libraryFilterByText = {
    attach: function () {

      function filterLibraryList(event) {
        var query = $(event.target).val();
        var regExp = new RegExp(query, 'i');

        if (query.length >= 0) {
          $rows.each(function (index, row) {
            var $rows = $(row);
            var text = $rows.find('td:eq(0)').text();
            $rows.toggle(text.search(regExp) !== -1);
          });
        }

        $emptyRow.toggle($rows.filter(':visible').length === 0);
      }

      var $input = $('[data-drupal-selector="library-filter"]').once('lm-filter');
      if ($input.length === 1) {
        var $table = $('[data-drupal-selector="library-list"]');
        var $rows = $table.find('tbody tr');
        $table.find('tbody').append('<tr class="empty-row"/>');
        var $emptyRow = $('.empty-row');
        $emptyRow
          .hide()
          .append('<td colspan="5">' + Drupal.t('No libraries were found.') + '</td>');
        $input.on({keyup: Drupal.debounce(filterLibraryList, 100)});
      }

    }
  };

}(jQuery, Drupal));
