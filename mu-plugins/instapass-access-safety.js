(function () {
  'use strict';
  function init() {
  var notice = document.getElementById('ip-vpn-safety');
  if (!notice) return;
  var key = 'ip-vpn-safety-dismissed-v1';
  try {
    if (window.sessionStorage.getItem(key) === '1') notice.hidden = true;
  } catch (error) {
    // Keep the notice visible when browser storage is unavailable.
  }
  var close = notice.querySelector('[data-ip-vpn-dismiss]');
  if (!close) return;
  close.addEventListener('click', function () {
    notice.hidden = true;
    try {
      window.sessionStorage.setItem(key, '1');
    } catch (error) {
      // Dismissing this page view still works without browser storage.
    }
  });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
