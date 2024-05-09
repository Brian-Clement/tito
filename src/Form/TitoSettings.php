<?php

namespace Drupal\tito\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Tito settings form.
 */
class TitoSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'tito.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tito_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('tito.settings');

    $form['tito_api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tito API Endpoint'),
      '#description' => $this->t('Enter the Tito API endpoint you wish to connect to.'),
      '#default_value' => $config->get('tito_api_url') ?: 'https://api.tito.io/v3/',
    ];

    $form['tito_api_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Tito API Key'),
      '#description' => $this->t('Enter your Tito API token'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('tito_api_token'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('tito.settings')
      ->set('tito_api_url', $form_state->getValue('tito_api_url'))
      ->set('tito_api_token', $form_state->getValue('tito_api_token'))
      ->save();
  }

}
