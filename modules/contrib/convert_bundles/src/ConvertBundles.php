<?php

namespace Drupal\convert_bundles;

use Drupal\Core\Render\Markup;
use Drupal\Core\Database\Database;

/**
 * ConvertBundles.
 */
class ConvertBundles {

  /**
   * {@inheritdoc}
   */
  public static function getEntities($type, $bundles) {
    // Get the entity IDs to update.
    $column = 'type';
    // TODO: Find columns for all entity types.
    if ($type == 'taxonomy_term') {
      $column = 'vid';
    }
    $query = \Drupal::service('entity.query')->get($type);
    $query->condition($column, $bundles);
    $ids = $query->execute();
    $entities = [];
    foreach ($ids as $id) {
      $entities[$id] = \Drupal::entityTypeManager()->getStorage($type)->load($id);
    }
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public static function getFromFields($fields_from_bundle, $fields_to_names, $fields_to_types) {
    $fields_from_names = [];
    $form = [];
    foreach ($fields_from_bundle as $fields_from) {
      foreach ($fields_from as $field) {
        $options = $fields_to_names;
        foreach ($options as $option => $label) {
          // Because might be target_id.
          $val_name = $field->getFieldStorageDefinition()->getMainPropertyName();
          // Because some data types are more complex.
          $data_type = '';
          $type = $field->getType();
          if (empty($field->getFieldStorageDefinition()->getPropertyDefinition($val_name))) {
            $data_type = $type;
          }
          else {
            $data_type = $field->getFieldStorageDefinition()->getPropertyDefinition($val_name)->getDataType();
          }
          if (!in_array($option, ['remove', 'append_to_body']) &&
              $fields_to_types[$option] != $data_type) {
            unset($options[$option]);
          }
        }
        if ($field->getFieldStorageDefinition()->isBaseField() == FALSE) {
          $fields_from_names[] = $field->getName();
          $form[$field->getName()] = [
            '#type' => 'select',
            '#title' => t('From Field [@field_name] @field_label:<br/> To Field',
              [
                '@field_name' => $field->getName(),
                '@field_label' => (is_object($field->getLabel()) ? $field->getLabel()->render() : $field->getLabel()),
              ]),
            '#options' => $options,
            '#default_value' => (array_key_exists($field->getName(), $fields_to_names) ? $field->getName() : NULL),
          ];
        }
      }
    }
    return ['fields_from_names' => $fields_from_names, 'fields_from_form' => $form];
  }

  /**
   * {@inheritdoc}
   */
  public static function getToFields($fields_to) {
    $fields_to_names = [];
    $fields_to_types = [];
    // Add some extra options for the form.
    $fields_to_names['remove'] = 'remove';
    $fields_to_names['append_to_body'] = 'append_to_body';
    // Get the to fields in an array.
    foreach ($fields_to as $field) {
      if ($field->getFieldStorageDefinition()->isBaseField() == FALSE) {
        // Because might be target_id.
        $val_name = $field->getFieldStorageDefinition()->getMainPropertyName();
        // Because some data types are more complex.
        $data_type = '';
        $type = $field->getType();
        if (empty($field->getFieldStorageDefinition()->getPropertyDefinition($val_name))) {
          $data_type = $type;
        }
        else {
          $data_type = $field->getFieldStorageDefinition()->getPropertyDefinition($val_name)->getDataType();
        }
        $fields_to_names[$field->getName()] = '[' . $field->getName() . '] ' . (is_object($field->getLabel()) ? $field->getLabel()->render() : $field->getLabel());
        $fields_to_types[$field->getName()] = $data_type;
      }
    }
    return ['fields_to_names' => $fields_to_names, 'fields_to_types' => $fields_to_types];
  }

  /**
   * {@inheritdoc}
   */
  public static function getBaseTableNames($entity_type) {
    $storage = \Drupal::service('entity_type.manager')->getStorage($entity_type);
    // Get the names of the base tables.
    $base_table_names = [];
    $base_table_names[] = $storage->getBaseTable();
    if (!empty($storage->getDataTable())) {
      $base_table_names[] = $storage->getDataTable();
    }
    return $base_table_names;
  }

  /**
   * {@inheritdoc}
   */
  public static function sortUserInput($userInput, $fields_new_to, $fields_from) {
    // Get user input and set up vars.
    $map_fields = [];
    $update_fields = [];
    // Remove stuff we don't need.
    $unset_data = ['op', 'form_build_id', 'form_token', 'form_id'];
    foreach ($userInput as $from => $to) {
      if (in_array($from, $unset_data)) {
        continue;
      }
      if ($from == $to) {
        $update_fields[] = $from;
      }
      elseif (in_array($from, $fields_new_to) && !in_array($from, $userInput)) {
        $map_fields['create_new'][] = [
          'field' => $from,
          'value' => $to,
        ];
      }
      else {
        foreach ($fields_from as $field_def) {
          if (isset($field_def[$from])) {
            $map_fields[$from] = [
              'field' => $to,
              'from_label' => $field_def[$from]->getLabel(),
            // This will come in later.
              'value' => [],
            ];
          }
        }
      }
    }
    return ['map_fields' => $map_fields, 'update_fields' => $update_fields];
  }

  /**
   * {@inheritdoc}
   */
  public static function getFieldTableNames($entity_type, $fields_from) {
    $table_mapping = \Drupal::service('entity_type.manager')->getStorage($entity_type)->getTableMapping();
    $field_table_names = [];
    foreach ($fields_from as $field_key) {
      foreach ($field_key as $field) {
        if ($field->getFieldStorageDefinition()->isBaseField() == FALSE) {
          $field_name = $field->getName();
          $field_table = $table_mapping->getFieldTableName($field_name);
          $field_table_names[$field_name] = $field_table;
          $field_storage_definition = $field->getFieldStorageDefinition();
          $field_revision_table = $table_mapping->getDedicatedRevisionTableName($field_storage_definition);
          // Field revision tables DO have the bundle!
          $field_table_names[$field_name . '_revision'] = $field_revision_table;
        }
      }
    }
    return $field_table_names;
  }

  /**
   * {@inheritdoc}
   */
  public static function convertBaseTables($entity_type, $base_table_names, $ids, $to_type, &$context) {
    $message = 'Converting Base Tables...';
    $results = [];
    $db = Database::getConnection();
    // Base tables have 'nid' and 'type' columns.
    // TODO switch column names based on entity type!!!
    $id = 'id';
    $type = 'type';
    if ($entity_type == 'node') {
      $id = 'nid';
    }
    elseif ($entity_type == 'taxonomy_term') {
      $id = 'tid';
      $type = 'vid';
    }
    foreach ($base_table_names as $table_name) {
      $results[] = $db->update($table_name)
        ->fields([$type => $to_type])
        ->condition($id, $ids, 'IN')
        ->execute();
    }
    $context['message'] = $message;
    $context['results'] = $results;
  }

  /**
   * {@inheritdoc}
   */
  public static function convertFieldTables($field_table_names, $ids, $to_type, $update_fields, &$context) {
    $message = 'Converting Field Tables...';
    $results = [];
    $db = Database::getConnection();
    // Field tables have 'entity_id' and 'bundle' columns.
    foreach ($field_table_names as $field_name => $table_name) {
      if ($db->schema()->tableExists($table_name)) {
        // Only do this when from and to fields are the same.
        if (in_array(str_replace('_revision', '', $field_name), $update_fields)) {
          $results[] = $db->update($table_name)
            ->fields(['bundle' => $to_type])
            ->condition('entity_id', $ids, 'IN')
            ->execute();
        }
      }
    }
    $context['message'] = $message;
    $context['results'] = $results;
  }

  /**
   * {@inheritdoc}
   */
  public static function addNewFields($entity_type, $ids, $limit, $map_fields, $fields_to, $entities, &$context) {
    if (empty($context['sandbox'])) {
      // Flush cache so we recognize new bundle type before updates.
      drupal_flush_all_caches();
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = 0;
      $context['sandbox']['max'] = count($ids);
    }

    $current_ids = array_slice($ids, $context['sandbox']['current_id'], $limit, TRUE);
    foreach ($current_ids as $key => $id) {
      $old_entity = $entities[$id];
      $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($id);
      foreach ($map_fields as $map_from => $map_to) {
        if (isset($map_to['field']) && $map_to['field'] == 'remove') {
          continue;
        }

        $value = '';
        // TODO Need to get multiple values.
        if ($old_entity->hasField($map_from)) {
          // Because might be target_id.
          $val_name = $old_entity->get($map_from)->getFieldDefinition()->getFieldStorageDefinition()->getMainPropertyName();
          $value = $old_entity->get($map_from)->$val_name;
          if ($map_to['field'] != 'append_to_body') {
            // Because datetime/date may need converting
            // TODO date with time did not insert into date only fields
            // need to test if date without time will insert into date with time
            // or better yet, find a better way to do this.
            $from_type = $old_entity->get($map_from)->getFieldDefinition()->getFieldStorageDefinition()->getType();
            $to_type = $fields_to[$map_to['field']];
            if (!empty($to_type) && in_array('datetime', [$to_type, $from_type])) {
              $date = new \DateTime($value);
              $value = $date->format('Y-m-d');
            }
          }
        }

        if ($map_from == 'create_new') {
          foreach ($map_to as $field) {
            if (isset($field['value']['target_id'])) {
              $entity->get($field['field'])->setValue($field['value']['target_id'][0]);
              if (count($field['value']['target_id']) > 1) {
                $first_value = array_shift($field['value']['target_id']);
                foreach ($field['value']['target_id'] as $value) {
                  $entity->get($field['field'])->appendItem($value);
                }
                array_unshift($field['value']['target_id'], $first_value);
              }
            }
            else {
              $entity->get($field['field'])->setValue($field['value']);
            }
          }
        }
        elseif ($map_to['field'] == 'append_to_body') {
          $body = $entity->get('body')->getValue()[0];
          $markup = Markup::create($body['value'] . '<strong>' . $map_to['from_label'] . '</strong><p>' . $value . '</p>');
          $entity->get('body')->setValue([
            [
              'value' => $markup,
              'summary' => $body['summary'],
              'format' => $body['format'],
            ],
          ]);
        }
        elseif (!empty($value)) {
          $val_name = $entity->get($map_to['field'])->getFieldDefinition()->getFieldStorageDefinition()->getMainPropertyName();
          $entity->get($map_to['field'])->setValue([[$val_name => $value]]);
        }
      }
      $entity->save();

      $context['results'][] = $id;
      $context['sandbox']['progress']++;
      $context['sandbox']['current_id'] = $key;
      $context['message'] = t('Adding fields for entity @entity of @total.', ['@entity' => $key + 1, '@total' => $context['sandbox']['max']]);
    }
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function convertBundlesFinishedCallback($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One operation processed.', '@count operations processed.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    drupal_set_message($message);
  }

}
