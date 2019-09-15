<?php

namespace Drupal\gh_jobs_migrate\Plugin\migrate_plus\data_parser;

use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json;
use Drupal\gh_jobs\GhJobsInterface;

/**
 * Obtain JSON data for departments taxonomy.
 *
 * @DataParser(
 *   id = "greenhouse_job_json_parser",
 *   title = @Translation("Greenhouse Job Json Parser")
 * )
 */
class GreenhouseJobJsonParser extends Json implements GhJobsInterface {

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

    // Convert retrieved data to the format that is more suitable for migration.
    $jobs = [];
    foreach ($source_data as $entry) {
      $departments = [];
      foreach ($entry['departments'] as $department) {
        $departments[] = $department['id'];
      }

      $offices = [];
      foreach ($entry['offices'] as $office) {
        $offices[] = $office['id'];
      }

      $jobs[] = [
        'id' => $entry['id'],
        'title' => $entry['title'],
        'location' => $entry['location']['name'],
        'content' => $entry['content'],
        'departments' => $departments,
        'offices' => $offices,
        'updated_at' => $entry['updated_at'],
      ];
    }

    return $jobs;
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
    $config = \Drupal::config(self::GH_JOBS_SETTINGS);
    $board = $config->get(self::BOARD_TOKEN_CONFIG_NAME);

    return str_replace("{board_token}", $board, $url);
  }

}
