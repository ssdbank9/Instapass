(function () {
  'use strict';
  function init() {
    document.querySelectorAll('.ipl-card').forEach(function (card) {
      function select(value) {
        card.querySelectorAll('.ipl-quote').forEach(function (panel) {
          panel.hidden = panel.dataset.months !== String(value);
        });
      }
      card.addEventListener('change', function (event) {
        if (event.target.matches('.ipl-durations input[type="radio"]')) select(event.target.value);
      });
      var range = card.querySelector('[data-credit-range]');
      if (range) {
        function update() {
          select(range.value);
          card.querySelector('.ipl-credit-value').textContent = '$' + range.value + ' credits';
          range.setAttribute('aria-valuetext', '$' + range.value + ' credit balance');
          card.querySelector('[data-credit-adjust="-25"]').disabled = Number(range.value) <= 50;
          card.querySelector('[data-credit-adjust="25"]').disabled = Number(range.value) >= 500;
        }
        range.addEventListener('input', update);
        card.querySelectorAll('[data-credit-adjust]').forEach(function (button) {
          button.addEventListener('click', function () {
            range.value = String(Math.max(50, Math.min(500, Number(range.value) + Number(button.dataset.creditAdjust))));
            update();
          });
        });
        update();
      }
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
