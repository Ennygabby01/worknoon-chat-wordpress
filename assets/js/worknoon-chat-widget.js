(function () {
  const config = window.WorknoonChat;

  document.querySelectorAll('[data-worknoon-chat]').forEach((widget) => {
    const toggle = widget.querySelector('[data-worknoon-chat-toggle]');
    const close = widget.querySelector('[data-worknoon-chat-close]');
    const panel = widget.querySelector('[data-worknoon-chat-panel]');

    if (!toggle || !panel) {
      return;
    }

    let sessionRecorded = false;

    async function recordSession() {
      if (sessionRecorded || !config || !config.restUrl || !config.isLoggedIn) {
        return;
      }

      sessionRecorded = true;

      try {
        await fetch(`${config.restUrl}/sessions`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': config.nonce,
          },
          body: JSON.stringify({
            context: widget.getAttribute('data-context') || 'support',
            sourceUrl: window.location.href,
          }),
        });
      } catch (error) {
        sessionRecorded = false;
      }
    }

    function openPanel() {
      widget.classList.add('is-open');
      panel.hidden = false;
      toggle.setAttribute('aria-expanded', 'true');
      void recordSession();
    }

    function closePanel() {
      widget.classList.remove('is-open');
      panel.hidden = true;
      toggle.setAttribute('aria-expanded', 'false');
      toggle.focus();
    }

    toggle.addEventListener('click', () => {
      if (widget.classList.contains('is-open')) {
        closePanel();
        return;
      }

      openPanel();
    });

    close?.addEventListener('click', closePanel);

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && widget.classList.contains('is-open')) {
        closePanel();
      }
    });
  });
})();
