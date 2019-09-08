<?php

namespace Drupal\gh_jobs_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Process a content from greenhouse web service.
 *
 * This plugin will fix problems with HTML on the content.
 *
 * @MigrateProcessPlugin(
 *   id = "gh_jobs_migrate_process_content"
 * )
 */
class ProcessContent extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return html_entity_decode($value);
  }

}
