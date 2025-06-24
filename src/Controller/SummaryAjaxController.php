<?php

namespace Drupal\article_summary_display\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * AJAX controller to return rendered article summary.
 */
class SummaryAjaxController extends ControllerBase {

  protected RendererInterface $renderer;
//  protected ConfigFactoryInterface $configFactory;
//  protected EntityTypeManagerInterface $entityTypeManager;

  public function __construct(ConfigFactoryInterface $config_factory, RendererInterface $renderer, EntityTypeManagerInterface $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->renderer = $renderer;
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('config.factory'),
      $container->get('renderer'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Returns the rendered summary block via AJAX.
   */
  public function getSummary(NodeInterface $node): JsonResponse {
    if ($node->getType() !== 'article' || !$node->hasField('field_generated_summary')) {
      return new JsonResponse(['summary_html' => ''], 400);
    }

    $summary = $node->get('field_generated_summary')->value ?? '';
    $display_type = $this->configFactory->get('article_summary_display.settings')->get('summary_display_type') ?? 'sentences';

    if (trim($summary) === '') {
      $summary = $this->t('Summary currently not available.');
    }

    $render = [
      '#theme' => 'article_summary_block',
      '#summary_text' => $summary,
      '#display_type' => $display_type,
      '#cache' => ['max-age' => 0],
    ];

    $html = $this->renderer->renderPlain($render);

    return new JsonResponse([
      'summary_html' => $html,
    ]);
  }

}

