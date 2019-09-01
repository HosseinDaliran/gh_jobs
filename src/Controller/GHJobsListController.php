<?php

namespace Drupal\gh_jobs\Controller;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\Messenger;
use Greenhouse\GreenhouseToolsPhp\Clients\Exceptions\GreenhouseAPIResponseException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Greenhouse\GreenhouseToolsPhp\GreenhouseService;

/**
 * Class GHJobsListController.
 */
class GHJobsListController extends ControllerBase {

  /**
   * The Drupal messenger service.
   *
   * @var Drupal\Core\Messenger\Messenger
   */
  protected $messenger;

  /**
   * The Greenhouse service object.
   *
   * @var Greenhouse\GreenhouseToolsPhp\GreenhouseService
   */
  private $greenhouseService;

  /**
   * The Module configurations object.
   *
   * @var Drupal\Core\Config\ImmutableConfig
   */
  private $ghConfig;

  /**
   * Drupal cache object.
   *
   * @var Drupal\Core\Cache\CacheBackendInterface
   */
  private $cache;

  /**
   * The cached array of jobs.
   *
   * @var array
   */
  private $cachedJobs;

  /**
   * Controller Constructor.
   *
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   Drupal Messenger service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Drupal Config Factory Interface.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Drupal Cache Data Service.
   */
  public function __construct(Messenger $messenger, ConfigFactoryInterface $config_factory, CacheBackendInterface $cache) {
    $this->ghConfig = $config_factory->get(SETTINGS);
    $this->messenger = $messenger;
    $this->cache = $cache;
    $keys = [
      'apiKey' => $this->ghConfig->get(API_KEY_CONFIG_NAME),
      'boardToken' => $this->ghConfig->get(BOARD_TOKEN_CONFIG_NAME),
    ];
    $this->greenhouseService = new GreenhouseService($keys);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('config.factory'),
      $container->get('cache.data')
    );
  }

  /**
   * Return a list of Jobs.
   */
  public function content() {
    $jobs = $this->fetchJobsData();

    // Return a custom theme page with the returned jobs.
    return [
      '#theme' => 'gh_jobs__career_page',
      '#data' => [
        '#theme' => 'gh_jobs__career_list',
        '#jobs' => $jobs,
      ],
    ];
  }

  /**
   * Fetch jobs from Greenhouse API.
   *
   * @return array
   *   A array of Jobs theme items.
   */
  private function fetchJobsData() {
    if ($jobs = $this->cache->get(JOBS_CACHE_CID)) {
      return $jobs->data;
    }

    try {
      $jobApiService = $this->greenhouseService->getJobApiService();
      $response = $jobApiService->getJobs(TRUE);
      $jobs = $this->jobsJsonParse($response);
      $theme_items = $this->jobsItemsTheme($jobs);

      // Save the Jobs values on cache.
      $this->cache->set(JOBS_CACHE_CID, $theme_items);
      return $theme_items;
    }
    catch (GreenhouseAPIResponseException $e) {
      $this->messenger->addMessage($this->t('We could not get your jobs from Greenhouse API.'), 'error');
    }

    return [];
  }

  /**
   * Pass the jobs list to the career_item_list theme.
   *
   * @param array $jobs
   *   The Jobs array of objects.
   *
   * @return array
   *   Array of custom career item theme.
   */
  private function jobsItemsTheme(array $jobs) {
    $items = [];
    foreach ($jobs as $job) {
      $items[] = [
        '#theme' => 'gh_jobs__career_item_list',
        '#job' => $job,
      ];
    }

    return $items;
  }

  /**
   * Parse JSON string response and get the jobs list.
   *
   * @param string $data
   *   The JSON string to be decode.
   *
   * @return array
   *   A array of jobs.
   */
  private function jobsJsonParse($data) {
    $json = json_decode($data);
    return $json->jobs;
  }

}
