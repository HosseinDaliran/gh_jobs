<?php

namespace Drupal\gh_jobs\Controller;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Greenhouse\GreenhouseToolsPhp\Clients\Exceptions\GreenhouseAPIResponseException;
use Greenhouse\GreenhouseToolsPhp\GreenhouseService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GHJobDetailsController.
 */
class GHJobDetailsController extends ControllerBase {

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
   * The Job JSON object.
   *
   * @var object
   */
  private $job;

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
  public function __construct(Messenger $messenger, ConfigFactoryInterface $config_factory, CacheBackendInterface $cache, UrlGeneratorInterface $url_generator) {
    $this->ghConfig = $config_factory->get(SETTINGS);
    $this->messenger = $messenger;
    $this->cache = $cache;
    $this->job = NULL;
    $this->urlGenerator = $url_generator;
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
      $container->get('cache.data'),
      $container->get('url_generator')
    );
  }

  /**
   * Return the page content.
   *
   * @param int $id
   *   The Job ID to load.
   *
   * @return array
   *   The page render array.
   */
  public function content($id) {
    $job = $this->getJobById($id);

    return [
      '#theme' => 'gh_jobs__job_details',
      '#job' => $job,
      '#departments' => $this->getDepartments(),
      '#offices' => $this->getOffices(),
      '#content' => $this->getContent(),
      '#link' => $this->urlGenerator->generateFromRoute('gh_jobs.open_jobs'),
      '#attached' => [
        'library' => [
          'gh_jobs/embed_gh_board_scrip' => 'gh_jobs/embed_gh_board_scrip',
        ],
        'drupalSettings' => [
          'GHJobs' => [
            'jid' => $id,
          ],
        ],
      ],
    ];
  }

  /**
   * Return the page title.
   *
   * @return string
   *   Return the Job title value.
   */
  public function getTitle($id) {
    $job = $this->getJobById($id);
    if ($job != NULL) {
      return $job->title;
    }

    return $this->t('Job Description');
  }

  /**
   * Load a Job From API or Cache based on ID.
   *
   * @param int $id
   *   The Greenhouse job ID.
   *
   * @return mixed
   *   A Job object or NULL.
   */
  private function getJobById($id) {
    if ($job = $this->cache->get(JOB_CACHE_CID . "_{$id}")) {
      $this->setJob($job->data);
      return $this->job;
    }

    $job = NULL;
    try {
      $jobApiService = $this->greenhouseService->getJobApiService();
      $job = $jobApiService->getJob($id, TRUE);
      $this->setJob($job);

      // Save the Jobs values on cache.
      $this->cache->set(JOB_CACHE_CID . "_{$id}", $this->job);
    }
    catch (GreenhouseAPIResponseException $e) {
      $this->messenger->addMessage($this->t('We could not get this job from Greenhouse API.'), 'error');
    }

    return $this->job;
  }

  /**
   * Set the JSON job as a Object.
   *
   * @param string $job
   *   The Job JSON string.
   */
  private function setJob($job) {
    $this->job = (!is_object($job)) ? json_decode($job) : $job;
  }

  /**
   * Get the departments names.
   *
   * @return array|null
   *   A array of departments names or NULL.
   */
  private function getDepartments() {
    if (is_null($this->job)) {
      return NULL;
    }

    $departments = [];
    foreach ($this->job->departments as $department) {
      $departments[] = $department->name;
    }

    return $departments;
  }

  /**
   * Get the offices names.
   *
   * @return array|null
   *   A array of offices names or NULL.
   */
  private function getOffices() {
    if (is_null($this->job)) {
      return NULL;
    }

    $offices = [];
    foreach ($this->job->offices as $office) {
      $offices[] = $office->name;
    }

    return $offices;
  }

  /**
   * Get the content HTML value.
   *
   * @return array|null
   *   A render array with the content.
   */
  private function getContent() {
    if (is_null($this->job)) {
      return NULL;
    }

    return [
      '#markup' => html_entity_decode($this->job->content),
    ];
  }

}
