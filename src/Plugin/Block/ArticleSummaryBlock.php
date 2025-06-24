<?php
namespace Drupal\article_summary_display\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a block to display the AI-generated article summary.
 *
 * @Block(
 *   id = "article_summary_block",
 *   admin_label = @Translation("Article Summary Block")
 * )
 */
class ArticleSummaryBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected ConfigFactoryInterface $configFactory;
  protected RouteMatchInterface $routeMatch;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->routeMatch = $route_match;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('current_route_match'),
    );
  }

  public function build(): array {
    $config = $this->configFactory->get('article_summary_display.settings');

    $node = $this->routeMatch->getParameter('node');
    if (!$node || $node->getType() !== 'article' || !$node->hasField('field_generated_summary')) {
      return [];
    }

    $summary = $node->get('field_generated_summary')->value ?? '';
    if (trim($summary) === '') {
      return [];
    }

    $placement = $config->get('summary_display_placement') ?? 'above';
    $display_type = $config->get('summary_display_type') ?? 'sentences';

    $render = [
      '#theme' => 'article_summary_block',
      '#summary_text' => $summary,
      '#display_type' => $display_type,
      '#scroll_mode' => ($placement === 'scroll_30'),
      '#attributes' => [
        'class' => [
          'article-summary',
          'placement-' . $placement,
        ],
      ],
    ];

    if ($placement === 'scroll_30') {
      $render['#attached']['library'][] = 'article_summary_display/summary_display';
      $render['#attached']['drupalSettings']['articleSummary'] = [
        'nid' => $node->id(),
        'placement' => 'scroll_30',
      ];
      $render['#attributes']['class'][] = 'scroll-mode';
    }

    return $render;
  }

}
