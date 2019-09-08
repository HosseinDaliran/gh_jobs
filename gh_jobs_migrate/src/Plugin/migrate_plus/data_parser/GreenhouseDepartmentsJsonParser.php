<?php

namespace Drupal\gh_jobs_migrate\Plugin\migrate_plus\data_parser;

use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json;

/**
 * Obtain JSON data for departments taxonomy.
 *
 * @DataParser(
 *   id = "greenhouse_departments_json_parser",
 *   title = @Translation("Greenhouse Departments Json Parser")
 * )
 */
class GreenhouseDepartmentsJsonParser extends Json {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Build and set the request URL.
    $this->urls = [
      $this->prepareGhBoardUrl($this->urls[0]),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceData($url) {
    // Call the parent function to retrieve the data.
    $source_data = parent::getSourceData($url);
    $values = [];

    foreach ($source_data as $entry) {
      if (empty($entry['departments'])) {
        continue;
      }

      $departments = $entry['departments'];
      foreach ($departments as $department) {
        $parent = is_numeric($department['parent_id']) ? $department['parent_id'] : 0;
        $values[$department['id']] = [
          'id' => $department['id'],
          'name' => $department['name'],
          'parent_id' => $parent,
        ];
      }
    }

    return $values;
  }

  /**
   * Prepares a given URL for an Jobs Boards web service call.
   *
   * @param string $url
   *   The Jobs Board endpoint URL with board placeholder value.
   *
   * @return string
   *   The URL with placeholder values replaced.
   */
  private function prepareGhBoardUrl($url) {
    $config = \Drupal::config(GH_JOBS_SETTINGS);
    $board = $config->get(BOARD_TOKEN_CONFIG_NAME);

    return str_replace("{board_token}", $board, $url);
  }

}
