<?php

namespace Drupal\article_summary_display\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\node\Entity\Node;

/**
 * @QueueWorker(
 *   id = "article_summary_display.queue",
 *   title = "Generate Article Summary",
 *   cron = {
 *     "time" = 60
 *   }
 * )
 */


class ArticleSummaryQueue extends QueueWorkerBase
{
  /**
   * @inheritdoc
   */
  public function processItem($data): void
  {
    if (empty($data['nid'])) {
      return;
    }

    $node = Node::load($data['nid']);

    if (!$node || $node->getType() !== 'article') {
      return;
    }

    if (!$node->hasField('field_generated_summary')) {
      return;
    }

    if (!$node->get('field_generated_summary')->isEmpty()) {
      return;
    }

    // Extract body
    $body = $node->get('body')->value ?? '';
    if (trim($body) === '') {
      return;
    }

    // Call service
    $summary = \Drupal::service('article_summary_display.summary_generator')->generateSummary($body);
    if ($summary) {
      $node->set('field_generated_summary', $summary);
      $node->save();
    }
  }
}
