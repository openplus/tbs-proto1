<?php

namespace Drupal\moderation_dashboard\Plugin\Block;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the "Moderation Dashboard Activity" block.
 *
 * @Block(
 *   id = "moderation_dashboard_activity",
 *   admin_label = @Translation("Moderation Dashboard Activity"),
 *   category = @Translation("Moderation Dashboard")
 * )
 */
class ModerationDashboardActivity extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  /**
   * ModerationDashboardActivity constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Entity\EntityStorageInterface $user_storage
   *   The user storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database, TimeInterface $time, EntityStorageInterface $user_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
    $this->time = $time;
    $this->userStorage = $user_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
      $container->get('datetime.time'),
      $container->get('entity_type.manager')->getStorage('user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $request_time = $this->time->getCurrentTime();
    $request_time_sub_month = strtotime('-1 month', $request_time);
    $results1 = $this->database->query('select revision_uid as uid,count(*) as count from {node_revision} where revision_timestamp >= :request_time_sub_month group by revision_uid', [':request_time_sub_month' => $request_time_sub_month])
      ->fetchAllAssoc('uid', \PDO::FETCH_ASSOC);
    $results2 = $this->database->query('select n.uid,count(n.uid) as count from (select nid,uid from {node_field_data} where created >= :request_time_sub_month group by nid,uid) n group by n.uid', [':request_time_sub_month' => $request_time_sub_month])
      ->fetchAllAssoc('uid', \PDO::FETCH_ASSOC);
    $uids = array_merge(array_keys($results1), array_keys($results2));

    if (!$uids) {
      return [
        '#markup' => '<p>' . $this->t('There has been no editor activity within the last month.') . '</p>',
      ];
    }

    $users = $this->userStorage->loadMultiple($uids);

    $data = [
      'labels' => [],
      'datasets' => [
        [
          'label' => $this->t('Content edited'),
          'data' => [],
          'backgroundColor' => [],
        ],
        [
          'label' => $this->t('Content authored'),
          'data' => [],
          'backgroundColor' => [],
        ],
      ],
    ];
    foreach ($users as $uid => $user) {
      $data['labels'][] = $user->label();
      $data['datasets'][0]['data'][] = isset($results1[$uid]['count']) ? $results1[$uid]['count'] : 0;
      $data['datasets'][0]['backgroundColor'][] = 'rgba(11,56,223,.8)';
      $data['datasets'][1]['data'][] = isset($results2[$uid]['count']) ? $results2[$uid]['count'] : 0;
      $data['datasets'][1]['backgroundColor'][] = 'rgba(27,223,9,.8)';
    }
    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['moderation-dashboard-activity'],
      ],
      '#attached' => [
        'library' => ['moderation_dashboard/activity'],
        'drupalSettings' => ['moderation_dashboard_activity' => $data],
      ],
    ];
    return $build;
  }

}
