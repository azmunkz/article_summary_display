(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.summaryHistoryPopup = {
    attach: function (context, settings) {
      console.log('🔁 Binding popup links...');
      $('.summary-history-link', context).on('click', function (e) {
        e.preventDefault();

        let raw = $(this).data('summary') || 'No summary data.';
        let content = '';

        try {
          const parsed = typeof raw === 'string' ? JSON.parse(raw) : raw;
          if (parsed.summary_sentences && Array.isArray(parsed.summary_sentences)) {
            content = '<ul class="summary-history-list">' + parsed.summary_sentences.map(line => `<li>${line}</li>`).join('') + '</ul>';
          } else {
            content = `<pre>${JSON.stringify(parsed, null, 2)}</pre>`;
          }
        } catch (e) {
          content = `<pre>${raw}</pre>`;
        }

        if (typeof Swal === 'undefined') {
          alert('❌ Swal is not available');
          return;
        }

        Swal.fire({
          title: 'Generated Summary',
          html: `${content}`,
          width: '50em',
          icon: 'info',
          confirmButtonText: 'Close'
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
