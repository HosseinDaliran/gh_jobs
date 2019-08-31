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
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      SETTINGS,
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
    $config = $this->config(SETTINGS);

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
    $config_settings = $this->configFactory->getEditable(SETTINGS);

    // Saving Form fields.
    $config_settings->set(API_KEY_CONFIG_NAME, $form_state->getValue('api_key'));
    $config_settings->set(BOARD_TOKEN_CONFIG_NAME, $form_state->getValue('board_token'));
    $config_settings->save();

    parent::submitForm($form, $form_state);
  }

}
