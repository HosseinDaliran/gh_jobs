<?php

namespace Drupal\gh_jobs_migrate\Service;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\MigrationPluginManager;
use Drupal\migrate_tools\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Psr\Log\LoggerInterface;

/**
 * Class MigrateJobsUpdate.
 *
 * @package Drupal\gh_jobs_migrate\Service
 */
class MigrateJobsUpdate {

  /**
   * The Migration Plugin service.
   *
   * @var Drupal\migrate\Plugin\MigrationPluginManager
   */
  private $migration;

  /**
   * The custom Logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * MigrateJobsUpdate constructor.
   *
   * @param \Drupal\migrate\Plugin\MigrationPluginManager $migration
   *   The Migration Plugin by dependency injection.
   * @param \Psr\Log\LoggerInterface $logger
   *   The Custom Logger service.
   */
  public function __construct(MigrationPluginManager $migration, LoggerInterface $logger) {
    $this->migration = $migration;
    $this->logger = $logger;
  }

  /**
   * Run a migration update.
   *
   * @throws \Drupal\migrate\MigrateException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function execute() {
    $migration_ids = [
      'gh_departments_terms',
      'gh_offices_terms',
      'gh_jobs_nodes',
    ];

    try {
      $migrations = $this->migration->createInstances($migration_ids);
      $key = 0;

      foreach ($migrations as $migration) {
        $migration->getIdMap()->prepareUpdate();
        $executable = new MigrateExecutable($migration, new MigrateMessage());
        $executable->import();

        if ($count = $executable->getFailedCount()) {
          $this->logger->alert("{$migration_ids[$key]} Migration - {$count} failed.");
        }

        $key++;
      }

      $this->logger->info("Greenhouse migrations was executed.");
    }
    catch (MigrateException $e) {
      $this->logger->error("Error trying revoke Zoho CRM API Refresh Token. Exception message: {$e->getMessage()}");
    }
  }

}
