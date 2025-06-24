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
  public function generateSummary(string $content): ?string
  {
    $settings = $this->configFactory->get('article_summary_display.settings');

    $api_key = $this->keyRepository->getKey('openai_key')->getKeyValue();
    $prompt = $settings->get('summary_display_prompt') ?? 'Summarize the article content in 3-5 concise sentences.';

    $max_tokens_raw = $settings->get('summary_max_tokens');
    $max_tokens = is_numeric($max_tokens_raw) && $max_tokens_raw > 0 ? (int) $max_tokens_raw : 250;

    $temperature_raw = $settings->get('summary_temperature');
    $temperature = is_numeric($temperature_raw) ? (float) $temperature_raw : 0.5;

    $frequency_penalty_raw = $settings->get('summary_frequency_penalty');
    $frequency_penalty = is_numeric($frequency_penalty_raw) ? (float) $frequency_penalty_raw : 0.0;

    $presence_penalty_raw = $settings->get('summary_presence_penalty');
    $presence_penalty = is_numeric($presence_penalty_raw) ? (float) $presence_penalty_raw : 0.0;

    // Clamp values to OpenAI allowed ranges
    $temperature = max(0.0, min($temperature, 2.0)); // Valid: 0.0 – 2.0
    $frequency_penalty = max(-2.0, min($frequency_penalty, 2.0)); // Valid: -2.0 – 2.0
    $presence_penalty = max(-2.0, min($presence_penalty, 2.0));   // Valid: -2.0 – 2.0


    try {
      $client = \Drupal::httpClient();

      $response = $client->post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
          'Authorization' => 'Bearer ' . $api_key,
          'Content-Type' => 'application/json',
        ],
        'json' => [
          'model' => 'gpt-4',
          'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => $prompt . "\n\n" . $content],
          ],
          'temperature' => $temperature,
          'max_tokens' => $max_tokens,
          'frequency_penalty' => $frequency_penalty,
          'presence_penalty' => $presence_penalty,
        ],
        'timeout' => 15,
      ]);

      $data = json_decode($response->getBody()->getContents(), true);
      return $data['choices'][0]['message']['content'] ?? NULL;

    } catch (RequestException $e) {
      $this->logger->error('OpenAI request failed: @error', ['@error' => $e->getMessage()]);
      return NULL;
    }
  }
}
