<?php

namespace Drupal\gh_jobs\Routing;

use Drupal\Core\Extension\ModuleHandler;
use Symfony\Component\Routing\Route;

/**
 * Define list and details Job pages.
 *
 * @package Drupal\gh_jobs\Routing
 */
class JobsRoutes {

  /**
   * The module handler service.
   *
   * @var Drupal\Core\Extension\ModuleHandler
   */
  private $moduleHandler;

  /**
   * JobsRoutes constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The Module Handler dependency injection service.
   */
  public function __construct(ModuleHandler $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * Return the dynamic routes.
   *
   * @return array
   *   A array of Routes.
   */
  public function routes() {
    if ($this->moduleHandler->moduleExists('gh_jobs_migrate')) {
      return [];
    }

    $routes = [];

    // List page route.
    $routes['gh_jobs.open_jobs'] = new Route(
      '/career',
      [
        '_controller' => '\Drupal\gh_jobs\Controller\JobsListController::content',
        '_title' => 'Career Opportunities',
      ],
      [
        '_permission'  => 'access content',
      ]
    );

    // Detail page route.
    $routes['gh_jobs.job_details'] = new Route(
      '/career/{id}',
      [
        '_controller' => '\Drupal\gh_jobs\Controller\JobDetailsController::content',
        '_title_callback' => '\Drupal\gh_jobs\Controller\JobDetailsController::getTitle',
      ],
      [
        '_permission'  => 'access content',
      ]
    );

    return $routes;
  }

}
