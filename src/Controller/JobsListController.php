<?php

namespace Drupal\gh_jobs\Controller;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Greenhouse\GreenhouseToolsPhp\Clients\Exceptions\GreenhouseAPIResponseException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Greenhouse\GreenhouseToolsPhp\GreenhouseService;
use Drupal\gh_jobs\GhJobsInterface;
use Drupal\key\KeyRepositoryInterface;

/**
 * Class GHJobsListController.
 */
class JobsListController extends ControllerBase implements GhJobsInterface {

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
   * Drupal URL service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Controller Constructor.
   *
   * @param \Drupal\Core\Messenger\Messenger $messenger
   *   Drupal Messenger service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Drupal Config Factory Interface.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   Drupal Cache Data Service.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   Drupal URL service.
   */
  public function __construct(Messenger $messenger, ConfigFactoryInterface $config_factory, CacheBackendInterface $cache, UrlGeneratorInterface $url_generator, KeyRepositoryInterface $key_repo) {
    $this->ghConfig = $config_factory->get(self::GH_JOBS_SETTINGS);
    $this->messenger = $messenger;
    $this->cache = $cache;
    $this->urlGenerator = $url_generator;
    $key_id = $this->ghConfig->get(self::API_KEY_CONFIG_NAME);
    $keys = [
      'apiKey' => $key_repo->getKey($key_id),
      'boardToken' => $this->ghConfig->get(self::BOARD_TOKEN_CONFIG_NAME),
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
      $container->get('cache.data'),
      $container->get('url_generator'),
      $container->get('key.repository')
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
    if ($jobs = $this->cache->get(self::JOBS_CACHE_CID)) {
      return $jobs->data;
    }

    try {
      $jobApiService = $this->greenhouseService->getJobApiService();
      $response = $jobApiService->getJobs(TRUE);
      $jobs = $this->jobsJsonParse($response);
      $theme_items = $this->jobsItemsTheme($jobs);

      // Save the Jobs values on cache.
      $this->cache->set(self::JOBS_CACHE_CID, $theme_items);
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
      $link = $this->urlGenerator->generateFromRoute('gh_jobs.job_details', ['id' => $job->id]);
      $items[] = [
        '#theme' => 'gh_jobs__career_item_list',
        '#job' => $job,
        '#link' => $link,
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
