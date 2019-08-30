<?php

namespace Drupal\gh_jobs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class GHConfigForm.
 *
 * @package Drupal\gh_jobs\Form
 */
class GHConfigForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'gh_jobs.settings';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
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
    $config = $this->config(static::SETTINGS);

    $form['api_key'] = [
      '#type' => 'textfield',
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
    $config_settings = $this->configFactory->getEditable(static::SETTINGS);

    // Saving Form fields.
    $config_settings->set('api_key', $form_state->getValue('api_key'));
    $config_settings->set('board_token', $form_state->getValue('board_token'));
    $config_settings->save();

    parent::submitForm($form, $form_state);
  }

}
