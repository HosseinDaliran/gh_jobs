<?php

namespace Drupal\gh_jobs;

/**
 * Interface GhJobsInterface.
 */
interface GhJobsInterface {
  /**
   * Config settings.
   *
   * @var string
   */
  const GH_JOBS_SETTINGS = 'gh_jobs.settings';

  /**
   * APIKey config name.
   *
   * @var string
   */
  const API_KEY_CONFIG_NAME = 'api_key';

  /**
   * Board Token config name.
   *
   * @var string
   */
  const BOARD_TOKEN_CONFIG_NAME = 'board_token';

  /**
   * Jobs list cache CID.
   *
   * @var string
   */
  const JOBS_CACHE_CID = 'gh_jobs.cached_list';

  /**
   * Jobs item cache CID.
   *
   * @var string
   */
  const JOB_CACHE_CID = 'gh_jobs.cached_job';

  /**
   * Greenhouse embed board script URL.
   *
   * @var string
   */
  const GH_SCRIPT_URL = 'https://app.greenhouse.io/embed/job_board/js?for=';

}
