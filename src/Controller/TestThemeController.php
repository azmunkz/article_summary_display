<?php

namespace Drupal\article_summary_display\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns a test page for theme debugging.
 */
class TestThemeController extends ControllerBase {

  public function test() {
    return [
      '#theme' => 'article_summary_block',
      '#summary_text' => 'This is a test summary loaded via custom route.',
      '#display_type' => 'list',
    ];
  }

}
