/**
 * @file
 * CodeMirror toolbar.
 */

(function ($, Drupal, baseUrl) {

  'use strict';

  /**
   * Creates a toolbar.
   *
   * @param {object} editor
   *   The editor instance.
   * @param {object} options
   *   The editor options.
   */
  Drupal.codeMirrorToolbar = function (editor, options) {
    editor.$toolbar = $('<div class="cme-toolbar"/>')
      .prependTo($(editor.getWrapperElement()));
    createButtons(editor, options);
    createModeSelect(editor, options);
  };

  /**
   * Creates editor buttons.
   */
  function createButtons(editor, options) {
    $('<div class="cme-buttons"/>')
      .prependTo(editor.$toolbar)
      .load(options.buttonsBaseUrl);

    options.buttons.forEach(function (button) {
      // @TODO: Add title attribute.
      $('<svg data-cme-button="' + button + '" class="cme-button"><use xlink:href="#icon-' + button + '"></use></svg>')
        .appendTo(editor.$toolbar);
    });
    editor.$toolbar.find('[data-cme-button="shrink"]').hide();

    function setFullScreen(state) {
      editor.setOption('fullScreen', state);
      editor.$toolbar.find('svg[data-cme-button="enlarge"]').toggle(!state);
      editor.$toolbar.find('svg[data-cme-button="shrink"]').toggle(state);
    }

    var extraKeys = {
      F11: function (editor) {
        setFullScreen(!editor.getOption('fullScreen'));
      },
      Esc: function () {
        setFullScreen(false);
      }
    };
    editor.setOption('extraKeys', extraKeys);

    var doc = editor.getDoc();

    function createHtmlList(type) {
      var list = '<' + type + '>\n';
      doc.getSelection().split('\n').forEach(function (value) {
        list += '  <li>' + value + '</li>\n';
      });
      list += '</' + type + '>\n';
      doc.replaceSelection(list, doc.getCursor());
    }

    function buttonClickHandler(event) {
      var button = $(event.target).closest('[data-cme-button]').data('cme-button');
      switch (button) {

        case 'bold':
          doc.replaceSelection('<strong>' + doc.getSelection() + '</strong>', doc.getCursor());
          break;

        case 'italic':
          doc.replaceSelection('<em>' + doc.getSelection() + '</em>', doc.getCursor());
          break;

        case 'underline':
          doc.replaceSelection('<u>' + doc.getSelection() + '</u>', doc.getCursor());
          break;

        case 'strike-through':
          doc.replaceSelection('<s>' + doc.getSelection() + '</s>', doc.getCursor());
          break;

        case 'list-numbered':
          createHtmlList('ol');
          break;

        case 'list-bullet':
          createHtmlList('ul');
          break;

        case 'link':
          doc.replaceSelection('<a href="">' + doc.getSelection() + '</a>', doc.getCursor());
          break;

        case 'horizontal-rule':
          doc.replaceSelection('<hr/>', doc.getCursor());
          break;

        case 'undo':
          doc.undo();
          break;

        case 'redo':
          doc.redo();
          break;

        case 'clear-formatting':
          doc.replaceSelection($('<div>' + doc.getSelection() + '</div>').text(), doc.getCursor());
          break;

        case 'enlarge':
          setFullScreen(true);
          break;

        case 'shrink':
          setFullScreen(false);
          break;

      }
    }
    editor.$toolbar.click(buttonClickHandler);
  }

  /**
   * Creates a select list of available modes.
   */
  function createModeSelect(editor, options) {
    if (!$.isEmptyObject(options.modeSelect)) {
      var selectOptions = '';
      for (var key in options.modeSelect) {
        if (options.modeSelect.hasOwnProperty(key)) {
          selectOptions += '<option value="' + key + '">' + options.modeSelect[key] + '</option>';
        }
      }
      $('<select class="cme-mode"/>')
        .append(selectOptions)
        .val(options.mode)
        .change(function () {
          var value = $(this).val();
          editor.setOption('mode', value);
          // Save the value to cookie.
          var modesEncoded = $.cookie('codeMirrorModes');
          var modes = modesEncoded ? JSON.parse(modesEncoded) : {};
          modes[editor.getTextArea().getAttribute('data-drupal-selector')] = value;
          $.cookie('codeMirrorModes', JSON.stringify(modes), { path: baseUrl });
        })
        .appendTo(editor.$toolbar);
    }
  }

}(jQuery, Drupal, drupalSettings.path.baseUrl));
