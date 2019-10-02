CodeMirror editor
=================

Installation
------------
By default CodeMirror library is loaded from CDN. If you prefer to install it
locally download and unpack the library to the libraries directory. Make sure
the path to the library becomes: libraries/codemirror. Use
`drush codemirror:download` command for quick installation.

If you are using Composer for downloading third-party libraries turn off the
'minified' setting as asset-packagist.org does not provide minified files.

See https://www.drupal.org/node/2718229/#third-party-libraries

Configuring
-----------
The path to settings form: /admin/config/content/codemirror

Attaching the CodeMirror editor to a custom textarea
----------------------------------------------------

$form['example'] = [
  '#type' => 'codemirror',
  '#label' => t('Example'),
  // Optionally provide CodeMirror options.
  '#codemirror' => [],
];

Check out the codemirror_editor.libraries.yml for the full list of supported
CodeMirror options.

Known issues
------------
The editor may not work correctly when Big pipe module is enabled and parent
form is rendered within cache placeholder.
