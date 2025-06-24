<?php
namespace Drupal\article_summary_display\Form;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a settinss from for the Article Summary Display module.
 */
class SettingsForm extends ConfigFormBase
{
  /**
   * @inheritdoc
   */
  public function getFormId(): string
  {
    return 'article_summary_display_settings_form';
  }

  /**
   * @inheritdoc
   */
  protected function getEditableConfigNames(): array
  {
    return ['article_summary_display.settings'];
  }

  /**
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('article_summary_display.settings');

    $form['summary_display_placement'] = [
      '#type' => 'radios',
      '#title' => $this->t('Summary Placement'),
      '#description' => $this->t('Choose where the summary will appear in the article.'),
      '#options' => [
        'above' => $this->t('Above the article content'),
        'scroll_30' => $this->t('After 30% scroll'),
      ],
      '#default_value' => $config->get('summary_display_placement') ?? 'above',
    ];

    $form['summary_display_prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('AI Prompt'),
      '#description' => $this->t('Custom prompt for generating the summary'),
      '#default_value' => $config->get('summary_display_prompt') ?? 'Summarize the article content in 3–5 concise sentences.',
      '#rows' => 4,
    ];

    $form['summary_display_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Summary Display Format'),
      '#description' => $this->t('Choose how the summary should be displayed on the frontend.'),
      '#options' => [
        'sentences' => $this->t('Paragraph (sentences)'),
        'list' => $this->t('List Format'),
      ],
      '#default_value' => $config->get('summary_display_type') ?? 'sentences',
    ];

    $form['advanced_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced AI Settings'),
      '#open' => TRUE
    ];

    $form['advanced_settings']['summary_max_tokens'] = [
      '#type' => 'number',
      '#title' => $this->t('Max Tokens'),
      '#description' => $this->t('The maximum number of tokens to generate (e.g., 250.'),
      '#default_value' => $config->get('summary_max_tokens') ?? 250,
      '#min' => 50,
      '#max' => 4096
    ];

    $form['advanced_settings']['summary_temperature'] = [
      '#type' => 'number',
      '#title' => $this->t('Temperature'),
      '#description' => $this->t('Controls randomness: 0.0 (focused) to 2.0 (creative).'),
      '#default_value' => $config->get('summary_temperature') ?? 0.4,
      '#step' => 0.1,
      '#min' => 0,
      '#max' => 2.0
    ];

    $form['advanced_settings']['summary_frequency_penalty'] = [
      '#type' => 'number',
      '#title' => $this->t('Frequency Penalty'),
      '#description' => $this->t('Penalize repeated terms: -2.0 to 2.0'),
      '#default_value' => $config->get('summary_frequency_penalty') ?? 0.0,
      '#step' => 0.1,
      '#min' => -2,
      '#max' => 2
    ];

    $form['advanced_settings']['summary_presence_penalty'] = [
      '#type' => 'number',
      '#title' => $this->t('Presence Penalty'),
      '#description' => $this->t('Penalize repeated topics: -2.0 to 2.0'),
      '#default_value' => $config->get('summary_presence_penalty') ?? 0.0,
      '#step' => 0.1,
      '#min' => -2,
      '#max' => 2
    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * @inheritdoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $this->config('article_summary_display.settings')
      ->set('summary_display_placement', $form_state->getValue('summary_display_placement'))
      ->set('summary_display_prompt', $form_state->getValue('summary_display_prompt'))
      ->set('summary_display_type', $form_state->getValue('summary_display_type'))
      ->set('summary_max_tokens', $form_state->getValue('summary_max_tokens'))
      ->set('summary_temperature', $form_state->getValue('summary_temperature'))
      ->set('summary_frequency_penalty', $form_state->getValue('summary_frequency_penalty'))
      ->set('summary_presence_penalty', $form_state->getValue('summary_presence_penalty'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
