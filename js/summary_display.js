(function ($, Drupal, drupalSettings, once) {
  Drupal.behaviors.summaryDisplay = {
    attach: function (context, settings) {
      const config = drupalSettings.articleSummary || {};
      const placement = config.placement || '';

      if (placement === 'scroll_30') {
        const summaryElements = once('summaryVisible', '.article-summary.scroll-mode', context);

        if (summaryElements.length) {
          const $summaryBlock = $(summaryElements); // wrap with jQuery for compatibility

          $(window).on('scroll', function () {
            const scrollPosition = $(window).scrollTop();
            const windowHeight = $(window).height();
            const docHeight = $(document).height();

            const scrollPercent = (scrollPosition / (docHeight - windowHeight)) * 100;

            if (scrollPercent >= 30) {
              $summaryBlock.addClass('visible');
            }
          });
        }
      }
    }
  };
})(jQuery, Drupal, drupalSettings, once);
