<?php

namespace Drupal\gh_jobs_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Process a date from greenhouse web service.
 *
 * This plugin will transform a Greenhouse date to Drupal.
 *
 * @MigrateProcessPlugin(
 *   id = "gh_jobs_migrate_process_date"
 * )
 */
class ProcessDate extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $time = strtotime($value);

    return $time;
  }

}
