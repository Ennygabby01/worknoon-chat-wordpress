/* globals wnAdminSettings */
(function () {
  var form = document.getElementById('wn-settings-form');
  var btn  = document.getElementById('wn-submit');

  if (!form || !btn) return;

  var originalLabel = btn.textContent.trim();
  var timer;

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    clearTimeout(timer);
    btn.disabled = true;
    btn.textContent = 'Saving…';
    btn.classList.remove('wn-submit--saved', 'wn-submit--error');

    var data = new FormData(form);
    data.append('action', 'worknoon_save_settings');
    data.append('nonce', wnAdminSettings.nonce);

    fetch(wnAdminSettings.ajaxUrl, {
      method: 'POST',
      body: data,
      credentials: 'same-origin'
    })
      .then(function (res) { return res.json(); })
      .then(function (json) {
        if (json && json.success) {
          btn.textContent = 'Saved';
          btn.classList.add('wn-submit--saved');
        } else {
          btn.textContent = 'Failed — try again';
          btn.classList.add('wn-submit--error');
        }
        reset(json && json.success ? 2200 : 3500);
      })
      .catch(function () {
        btn.textContent = 'Failed — try again';
        btn.classList.add('wn-submit--error');
        reset(3500);
      });
  });

  function reset(delay) {
    timer = setTimeout(function () {
      btn.textContent = originalLabel;
      btn.classList.remove('wn-submit--saved', 'wn-submit--error');
      btn.disabled = false;
    }, delay);
  }
})();
