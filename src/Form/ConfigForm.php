<?php

namespace Drupal\gh_jobs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\gh_jobs\GhJobsInterface;
use Drupal\key\KeyRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConfigForm.
 *
 * @package Drupal\gh_jobs\Form
 */
class ConfigForm extends ConfigFormBase implements GhJobsInterface {

  /**
   * The key repository.
   *
   * @var \Drupal\key\KeyRepositoryInterface
   */
  protected $keyRepo;

  /**
   * Construct a ConfigForm.
   *
   * @param \Drupal\key\KeyRepositoryInterface $key_repo
   */
  public function __construct(KeyRepositoryInterface $key_repo) {
    $this->keyRepo = $key_repo;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('key.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      self::GH_JOBS_SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gh_jobs_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::GH_JOBS_SETTINGS);

    $form['api_key'] = [
      '#type' => 'key_select',
      '#title' => $this->t('APIKey'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];

    $form['board_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Board Token'),
      '#default_value' => $config->get('board_token'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve config factory editable.
    $config_settings = $this->configFactory->getEditable(self::GH_JOBS_SETTINGS);

    // Saving Form fields.
    $config_settings->set(self::API_KEY_CONFIG_NAME, $form_state->getValue('api_key'));
    $config_settings->set(self::BOARD_TOKEN_CONFIG_NAME, $form_state->getValue('board_token'));
    $config_settings->save();

    parent::submitForm($form, $form_state);
  }

}
