# Article Summary Display

A Drupal module to automatically generate and display AI-powered summaries for `article` content types using OpenAI.

## Features

- Automatically generates summaries using OpenAI Chat Completion API.
- Summaries are written in the same language as the article (supports BM/EN).
- Display options:
  - Paragraph view
  - Bullet list view
  - Lazy reveal after 30% scroll
- Admin configurable settings via UI.
- Prevents double-rendering of summary field on frontend.
- Twig template theming supported.
- Works with both paragraph and sentence list presentation.
- Summary is regenerated only if field is empty.

---

## Installation

1. Enable the module:
   ```bash
   drush en article_summary_display
   ```

2. Ensure `field_generated_summary` exists on the `article` content type.
  - Field type: `Text (plain, long)`

3. Add your OpenAI API Key using the **Key** module:
  - Key name: `openai_key`

4. Set configuration at:
   ```
   /admin/config/content/article-summary
   ```

---

## Summary Placement Options

| Placement        | Description                              |
|------------------|------------------------------------------|
| Above article    | Injects summary above article content.   |
| Scroll 30%       | Summary appears after user scrolls 30%.  |

---

## Usage

- Upon saving or updating an article, if `field_generated_summary` is empty, the node will be queued for summary generation.
- Run the queue manually or via cron:
  ```bash
  drush queue:run article_summary_display.queue
  ```

---

## Theming

Custom Twig template is used for the summary block:

```
modules/custom/article_summary_display/templates/article-summary-block.html.twig
```

Supports two display types:
- `sentences`: bullet list
- `paragraph`: single paragraph

---

## CSS & JS

- Summary block includes built-in styling (`summary_display.css`)
- Scroll-based display handled via `summary_display.js`
- JS behavior is only attached when `scroll_30` is selected

---

## Developer Notes

- Uses `hook_preprocess_node()` to inject summary block and remove raw field display.
- Queue processing handled via `QueueWorker` plugin.
- Compatible with Drupal 10 & 11.

---

## Troubleshooting

- Make sure cache is cleared after changing display settings:
  ```bash
  drush cr
  ```
- To ensure updated JS/CSS is loaded, use:
  ```bash
  drush cache:rebuild
  ```

---

## TODO

- [ ] Support JSON sentence output (upcoming enhancement)
- [ ] Store raw JSON in separate field for flexibility
- [ ] Automated queue processor via cron

---

© 2025 Astro Awani Internal Tools
