<?php
namespace Drupal\article_summary_display\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\key\KeyRepositoryInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Service to generate article summaries using OpenAI.
 */
class SummaryGenerator
{
  protected KeyRepositoryInterface $keyRepository;
  protected LoggerChannelInterface $logger;
  protected ConfigFactoryInterface $configFactory;

  public function __construct(KeyRepositoryInterface $key_repository, LoggerChannelInterface $logger, ConfigFactoryInterface $config_factory)
  {
    $this->keyRepository = $key_repository;
    $this->logger = $logger;
    $this->configFactory = $config_factory;
  }

  /**
   * Generates a summary using OpenAI's GPOT model.
   *
   * @param string $content
   * The full carticle body content.
   *
   * @returns string|null
   * The generated summary or null if error.
   */
  public function generateSummary(string $content): ?string {
    $settings = $this->configFactory->get('article_summary_display.settings');

    $api_key = $this->keyRepository->getKey('openai_key')->getKeyValue();
    $prompt = $settings->get('summary_display_prompt') ?? 'Summarize the article content in 3-5 concise sentences.';

    $max_tokens_raw = $settings->get('summary_max_tokens');
    $max_tokens = is_numeric($max_tokens_raw) && $max_tokens_raw > 0 ? (int) $max_tokens_raw : 500;

    $temperature_raw = $settings->get('summary_temperature');
    $temperature = is_numeric($temperature_raw) ? (float) $temperature_raw : 0.5;

    $frequency_penalty_raw = $settings->get('summary_frequency_penalty');
    $frequency_penalty = is_numeric($frequency_penalty_raw) ? (float) $frequency_penalty_raw : 0.0;

    $presence_penalty_raw = $settings->get('summary_presence_penalty');
    $presence_penalty = is_numeric($presence_penalty_raw) ? (float) $presence_penalty_raw : 0.0;

    // Clamp values
    $temperature = max(0.0, min($temperature, 2.0));
    $frequency_penalty = max(-2.0, min($frequency_penalty, 2.0));
    $presence_penalty = max(-2.0, min($presence_penalty, 2.0));

    try {
      $client = \Drupal::httpClient();

      $payload = [
        'model' => 'gpt-4',
        'messages' => [
          ['role' => 'system', 'content' => 'You are a helpful assistant.'],
          ['role' => 'user', 'content' => $prompt . "\n\n" . $content],
        ],
        'temperature' => $temperature,
        'max_tokens' => $max_tokens,
        'frequency_penalty' => $frequency_penalty,
        'presence_penalty' => $presence_penalty,
      ];

      // Log request details
      \Drupal::logger('article_summary_display')->debug('OpenAI request payload: <pre>@payload</pre>', [
        '@payload' => print_r($payload, TRUE),
      ]);

      $response = $client->post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
          'Authorization' => 'Bearer ' . $api_key,
          'Content-Type' => 'application/json',
        ],
        'json' => $payload,
        'timeout' => 15,
      ]);

      $body = $response->getBody()->getContents();
      $data = json_decode($body, true);

      // Optional: Log usage stats
      if (isset($data['usage'])) {
        \Drupal::logger('article_summary_display')->info('OpenAI usage: prompt_tokens=@p, completion_tokens=@c, total_tokens=@t', [
          '@p' => $data['usage']['prompt_tokens'] ?? 'N/A',
          '@c' => $data['usage']['completion_tokens'] ?? 'N/A',
          '@t' => $data['usage']['total_tokens'] ?? 'N/A',
        ]);
      }

      // Log full response (optional, for debugging)
      \Drupal::logger('article_summary_display')->debug('OpenAI raw response: <pre>@res</pre>', [
        '@res' => print_r($data, TRUE),
      ]);

      return $data['choices'][0]['message']['content'] ?? NULL;

    } catch (RequestException $e) {
      $this->logger->error('OpenAI request failed: @error', ['@error' => $e->getMessage()]);
      return NULL;
    }
  }
}
