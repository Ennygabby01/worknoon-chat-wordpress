(function () {
  const config = window.WorknoonChat;
  const scrollLock = {
    count: 0,
    previousOverflow: '',
  };

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

    function lockScroll() {
      if (scrollLock.count === 0) {
        scrollLock.previousOverflow = document.body.style.overflow;
      }

      scrollLock.count += 1;
      document.body.style.overflow = 'hidden';
    }

    function unlockScroll() {
      scrollLock.count = Math.max(0, scrollLock.count - 1);

      if (scrollLock.count === 0) {
        document.body.style.overflow = scrollLock.previousOverflow;
        scrollLock.previousOverflow = '';
      }
    }

    function openPanel() {
      panel.removeAttribute('hidden');
      widget.classList.add('is-open');
      toggle.setAttribute('aria-expanded', 'true');
      lockScroll();
      void recordSession();
    }

    function closePanel() {
      widget.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      unlockScroll();
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
