<?php

namespace Drupal\library_manager\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\library_manager\LibraryDiscoveryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Builds the form to create a library definition from existing library.
 *
 * @property \Drupal\library_manager\LibraryDefinitionInterface $entity
 */
class LibraryDefinitionBuildForm extends EntityForm {

  /**
   * The library discovery service.
   *
   * @var \Drupal\library_manager\LibraryDiscoveryInterface
   */
  protected $libraryDiscovery;

  /**
   * Constructs the form object.
   *
   * @param \Drupal\library_manager\LibraryDiscoveryInterface $library_discovery
   *   The discovery service.
   */
  public function __construct(LibraryDiscoveryInterface $library_discovery) {
    $this->libraryDiscovery = $library_discovery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('library_manager.library_discovery')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $extension = $this->getRequest()->get('extension');
    $library = $this->getRequest()->get('library');
    $library_info = $this->libraryDiscovery->getLibraryByName($extension, urldecode($library));
    if (!$library_info) {
      throw new NotFoundHttpException();
    }

    $form = parent::form($form, $form_state);

    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Library definition ID'),
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\library_manager\Entity\LibraryDefinition::load',
      ],
    ];

    $url = Url::fromRoute(
      'library_manager.library_canonical',
      ['extension' => $extension, 'library' => $library]
    );

    $form['source'] = [
      '#type' => 'item',
      '#title' => $this->t('Source library'),
      '#markup' => Link::fromTextAndUrl($extension . '/' . $library, $url)->toString(),
    ];

    $form['extension'] = [
      '#type' => 'value',
      '#value' => $extension,
    ];

    $form['library'] = [
      '#type' => 'value',
      '#value' => $library,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = $this->t('Create');
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->entity->set('id', $values['id']);
    $this->updateLibraryDefinition($values['extension'], $values['library']);
    $form_state->setRedirectUrl($this->entity->toUrl('edit-form'));
    $this->messenger()->addStatus($this->t('Library definition has been created.'));
    return $this->entity->save();
  }

  /**
   * Updates library definition according to library info.
   *
   * @param string $extension
   *   The name of the extension that registered a library.
   * @param string $library
   *   The name of a registered library to retrieve information.
   */
  protected function updateLibraryDefinition($extension, $library) {
    $library_info = $this->libraryDiscovery->exportLibraryByName($extension, $library);
    $extension_path = $this->libraryDiscovery->getExtensionPath($extension);

    $this->entity->set('target', $extension . '/' . $library);
    if (isset($library_info['remote'])) {
      $this->entity->set('remote', $library_info['remote']);
    }
    if (isset($library_info['version'])) {
      $version = $this->libraryDiscovery->processLibraryVersion($library_info['version']);
      $this->entity->set('version', $version);
    }
    if (isset($library_info['license'])) {
      $this->entity->set('license', $library_info['license']);
    }

    // Define JS files.
    if (isset($library_info['js'])) {
      $js_files = $this->createFileDefinitions($library_info['js'], $extension_path);
      // Make sure file IDs start with 1.
      array_unshift($js_files, NULL);
      unset($js_files[0]);
      $this->entity->set('js', $js_files);
    }

    // Define CSS files.
    if (isset($library_info['css'])) {
      $css_files = [];
      foreach ($library_info['css'] as $group => $group_files) {
        $css_files = array_merge($css_files, $this->createFileDefinitions($group_files, $extension_path, $group));
      }
      array_unshift($css_files, NULL);
      unset($css_files[0]);
      $this->entity->set('css', $css_files);
    }

    if (isset($library_info['dependencies'])) {
      $this->entity->set('library_dependencies', $library_info['dependencies']);
    }
  }

  /**
   * Create definition structure for a file set.
   */
  protected function createFileDefinitions(array $files, $extension_path, $group = FALSE) {
    $file_definitions = [];
    foreach ($files as $file_name => $file) {
      $file_definition = [];
      $file_definition['file_name'] = basename($file_name);
      $file_definition['preprocess'] = !empty($file['preprocess']);
      $file_definition['minified'] = !empty($file['minified']);
      $file_definition['external'] = !empty($file['external']);
      $file_definition['weight'] = isset($file['weight']) ? $file['weight'] : 0;
      if ($group) {
        $file_definition['group'] = $group;
      }
      $local_path = FALSE;
      if ($file_definition['external'] == 'external') {
        $file_definition['code'] = '';
        $file_definition['url'] = $file[$file_name];
      }
      // Determine the file asset URI.
      else {
        if ($file_name[0] === '/') {
          // An absolute path maps to DRUPAL_ROOT / base_path().
          if ($file_name[1] !== '/') {
            $local_path = substr($file_name, 1);
          }
          // A protocol-free URI (e.g., //cdn.com/example.js) is external.
          else {
            $file_definition['external'] = 'external';
            $file_definition['url'] = $file[$file_name];
          }
        }
        // A stream wrapper URI (e.g., public://generated_js/example.js).
        elseif (file_valid_uri($file_name)) {
          $local_path = $file_name;
        }
        // A regular URI (e.g., http://example.com/example.js) without
        // 'external' explicitly specified, which may happen if, e.g.
        // libraries-override is used.
        elseif (count(explode('://', $file_name)) === 2) {
          $file_definition['external'] = 'external';
          $file_definition['url'] = $file_name;
        }
        // By default, file paths are relative to the registering extension.
        else {
          $local_path = $extension_path . '/' . $file_name;
          $file_definition['file_name'] = $file_name;
        }
      }

      if ($local_path) {
        $file_definition['code'] = file_get_contents(DRUPAL_ROOT . '/' . $local_path);
        $file_definition['url'] = '';
      }

      $file_definitions[] = $file_definition;
    }
    return $file_definitions;
  }

}
