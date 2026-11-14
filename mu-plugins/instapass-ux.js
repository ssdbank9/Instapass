(function () {
  'use strict';
  function ready(fn) { if (document.readyState !== 'loading') { fn(); } else { document.addEventListener('DOMContentLoaded', fn); } }

  /* A1: autofocus the receipt email on checkout arrival.
     WooCommerce focuses the add-to-cart notice container at load, so retry briefly;
     yield immediately to any real user interaction. */
  function setupAutoFocus() {
    var stop = false;
    var stopIfUser = function (e) {
      if (e.target && e.target.closest && e.target.closest('input, select, textarea, button, a, summary, label')) { stop = true; }
    };
    document.addEventListener('keydown', stopIfUser, true);
    document.addEventListener('pointerdown', stopIfUser, true);
    var tryFocus = function () {
      if (stop) { return; }
      var email = document.getElementById('billing_email');
      if (!email || email.value) { return; }
      var a = document.activeElement;
      if (a && (a.tagName === 'INPUT' || a.tagName === 'TEXTAREA' || a.tagName === 'SELECT')) { return; }
      try { email.focus(); } catch (e) {}
    };
    [0, 300, 800, 1500].forEach(function (t) { setTimeout(tryFocus, t); });
  }

  /* A2: one-tap coupon apply chip inside the checkout promo box */
  function couponChip() {
    var note = document.querySelector('.ip-promo-note');
    if (!note || document.getElementById('coupon_code') === null && !document.querySelector('.woocommerce-form-coupon-toggle')) { return; }
    var code = note.querySelector('code');
    if (!code || code.dataset.uxChip) { return; }
    code.dataset.uxChip = '1';
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'ip-ux-coupon-chip';
    btn.textContent = 'Apply ' + code.textContent.trim();
    code.parentNode.insertBefore(btn, code.nextSibling);
    btn.addEventListener('click', function () {
      var field = document.getElementById('coupon_code');
      if (!field) {
        var toggle = document.querySelector('.showcoupon');
        if (toggle) { toggle.click(); }
        field = document.getElementById('coupon_code');
      }
      if (!field) { return; }
      field.value = code.textContent.trim();
      var apply = document.querySelector('button[name="apply_coupon"]');
      if (apply) { apply.click(); }
    });
  }

  /* A3: collapse optional checkout fields behind one toggle */
  function collapseOptional() {
    if (document.querySelector('.ip-ux-optional')) { return; }
    var ids = ['instapass_activation_email', 'billing_phone', 'instapass_renewal_contact'];
    var rows = [];
    ids.forEach(function (id) {
      var el = document.getElementById(id);
      if (!el) { return; }
      var row = el.closest('.form-row') || el.closest('p');
      if (row && rows.indexOf(row) === -1) { rows.push(row); }
    });
    if (!rows.length) { return; }
    var details = document.createElement('details');
    details.className = 'ip-ux-optional';
    var summary = document.createElement('summary');
    summary.textContent = 'Optional details — different activation email, phone, renewal contact';
    details.appendChild(summary);
    var anchor = rows[0];
    anchor.parentNode.insertBefore(details, anchor);
    rows.forEach(function (row) { details.appendChild(row); });
    var form = document.querySelector('form.checkout');
    if (form) {
      var openIfInvalid = function () {
        if (details.querySelector('.woocommerce-invalid')) { details.open = true; }
      };
      form.addEventListener('submit', function () { [300, 800, 1500, 2500, 4000].forEach(function (ms) { setTimeout(openIfInvalid, ms); }); });
    }
  }

  /* B1: tap-to-copy for Binance ID and exact amount */
  function wrapPattern(root, re) {
    var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
      acceptNode: function (n) {
        return n.parentElement && n.parentElement.closest('button, a, input, textarea, script, style, label') ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT;
      }
    });
    var nodes = [];
    while (walker.nextNode()) { nodes.push(walker.currentNode); }
    nodes.forEach(function (node) {
      var text = node.nodeValue;
      re.lastIndex = 0;
      if (!re.test(text)) { return; }
      re.lastIndex = 0;
      var frag = document.createDocumentFragment();
      var last = 0, m;
      while ((m = re.exec(text)) !== null) {
        frag.appendChild(document.createTextNode(text.slice(last, m.index)));
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ip-ux-copy';
        btn.dataset.copy = m[1];
        var value = document.createElement('span');
        value.className = 'ip-ux-copy-value';
        value.textContent = m[0];
        btn.appendChild(value);
        var hint = document.createElement('span');
        hint.className = 'ip-ux-copy-hint';
        hint.textContent = 'copy';
        btn.appendChild(hint);
        frag.appendChild(btn);
        last = m.index + m[0].length;
      }
      frag.appendChild(document.createTextNode(text.slice(last)));
      node.parentNode.replaceChild(frag, node);
    });
  }
  function copyables() {
    var body = document.querySelector('.ip-payment-instructions__body');
    if (body && !body.dataset.uxCopy) {
      body.dataset.uxCopy = '1';
      wrapPattern(body, /Binance ID:\s*([A-Za-z0-9]+)/g);
    }
    var panel = document.querySelector('.ip-payment-report');
    if (panel && !panel.dataset.uxCopy) {
      panel.dataset.uxCopy = '1';
      wrapPattern(panel, /Send exactly\s+([\d.,]+)\s*USDT/g);
      wrapPattern(panel, /Binance ID:\s*([A-Za-z0-9]+)/g);
    }
  }
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.ip-ux-copy');
    if (!btn) { return; }
    var text = btn.dataset.copy || '';
    var mark = function () {
      var hint = btn.querySelector('.ip-ux-copy-hint');
      if (hint) { hint.textContent = 'copied ✓'; setTimeout(function () { hint.textContent = 'copy'; }, 2000); }
    };
    if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(text).then(mark, mark); } else { mark(); }
  });

  /* B3: compact payment step strip; hide the duplicated trailing card paragraph */
  function paymentSteps() {
    var panel = document.querySelector('.ip-payment-report');
    if (!panel || panel.querySelector('.ip-ux-steps')) { return; }
    var card = document.querySelector('.ip-payment-instructions');
    if (card) {
      card.querySelectorAll('p').forEach(function (p) {
        if (/Payment reference: order #/.test(p.textContent)) { p.hidden = true; }
      });
    }
    var steps = document.createElement('ol');
    steps.className = 'ip-ux-steps';
    var items = [
      ['Pay', 'Send the exact amount using the receiving details above.'],
      ['Submit', 'Enter your transfer reference in the form below and press Payment made.'],
      ['Verified', 'We check the transfer, then activate and email you.']
    ];
    items.forEach(function (it) {
      var li = document.createElement('li');
      var strong = document.createElement('strong');
      strong.textContent = it[0];
      li.appendChild(strong);
      li.appendChild(document.createTextNode(' ' + it[1]));
      steps.appendChild(li);
    });
    var heading = panel.querySelector('h2');
    if (heading && heading.nextSibling) { panel.insertBefore(steps, heading.nextSibling); } else { panel.insertBefore(steps, panel.firstChild); }
  }

  /* B4: payment date hint */
  function dateHint() {
    var form = document.querySelector('.ip-payment-report form');
    if (!form) { return; }
    form.querySelectorAll('label').forEach(function (label) {
      if (/Payment date/.test(label.textContent) && !label.dataset.ux) {
        label.dataset.ux = '1';
        var hint = document.createElement('span');
        hint.className = 'ip-ux-hint';
        hint.textContent = 'Pick the date you sent the payment';
        label.appendChild(hint);
      }
    });
  }

  /* C2: tier badges on the three Claude tiles (identical logos otherwise) */
  function tierBadges() {
    var tiers = { 'claude-pro': 'PRO', 'claude-max': 'MAX 5×', 'claude-ultra': 'ULTRA' };
    document.querySelectorAll('.ipl-card').forEach(function (card) {
      var fam = card.getAttribute('data-launch-family');
      if (!tiers[fam] || card.querySelector('.ip-ux-tier')) { return; }
      var visual = card.querySelector('.ipl-visual');
      if (!visual) { return; }
      var badge = document.createElement('span');
      badge.className = 'ip-ux-tier';
      badge.textContent = tiers[fam];
      visual.appendChild(badge);
    });
  }

  /* C1: compact from-price under the tile title — true minimum across all terms (not the selected one) */
  function tileFrom() {
    document.querySelectorAll('.ipl-card').forEach(function (card) {
      var h2 = card.querySelector('.ipl-body h2');
      if (!h2) { return; }
      var best = null;
      card.querySelectorAll('.ipl-quote').forEach(function (q) {
        var t = q.querySelector('.ip-term-pricing') || q.querySelector('.ipl-credit-pricing');
        if (!t) { return; }
        var txt = t.innerText.replace(/\s+/g, ' ');
        var mm = txt.match(/([$₨£€])\s?([\d.,]+)\s*\/month/i);
        var amount, per = false, label = null, sym;
        if (mm) { amount = mm[2].replace(/,/g, ''); per = true; label = mm[0].replace(/\s+/g, ' '); sym = mm[1]; }
        else {
          var m2 = txt.match(/[$₨£€]\s?[\d.,]+/);
          if (!m2) { return; }
          amount = m2[0].replace(/[^\d.,]/g, '').replace(/,/g, '');
          sym = (m2[0].match(/[$₨£€]/) || ['$'])[0];
        }
        var num = parseFloat(amount);
        if (!isFinite(num)) { return; }
        if (best === null || num < best.num) { best = { num: num, sym: sym, per: per, label: label }; }
      });
      if (!best) { return; }
      var chip = card.querySelector('.ip-ux-from');
      if (!chip) {
        chip = document.createElement('p');
        chip.className = 'ip-ux-from';
        h2.parentNode.insertBefore(chip, h2.nextSibling);
      }
      var text = best.per && best.label ? best.label : (best.sym + best.num.toFixed(2));
      chip.textContent = 'From ' + text;
    });
  }
  /* Highlight: 25% additional usage on Claude Pro and Max 5× tiles (owner request 13 Nov) */
  function perkBadges() {
    var perks = { 'claude-pro': '✦ 25% more usage than standard plans', 'claude-max': '✦ 25% more usage than standard plans' };
    document.querySelectorAll('.ipl-card').forEach(function (card) {
      var fam = card.getAttribute('data-launch-family');
      if (!perks[fam] || card.querySelector('.ip-ux-perk')) { return; }
      var h2 = card.querySelector('.ipl-body h2');
      if (!h2) { return; }
      var pill = document.createElement('p');
      pill.className = 'ip-ux-perk';
      pill.textContent = perks[fam];
      var chip = card.querySelector('.ip-ux-from');
      if (chip && chip.parentNode === h2.parentNode) { chip.after(pill); } else { h2.after(pill); }
    });
  }
  function tiles() { tierBadges(); tileFrom(); perkBadges(); }

  /* Checkout journey strip (adopted from competitor review): 1. details 2. pay 3. get access */
  function checkoutSteps() {
    var form = document.querySelector('form.checkout');
    if (!form || document.querySelector('.ip-ux-checkout-steps')) { return; }
    var steps = document.createElement('ol');
    steps.className = 'ip-ux-steps ip-ux-checkout-steps';
    var items = [
      ['1. Your details', 'Just your email — no account needed.'],
      ['2. Pay', 'Follow the receiving instructions for your chosen method.'],
      ['3. Get access', 'We verify, activate, and email you.']
    ];
    items.forEach(function (it) {
      var li = document.createElement('li');
      var strong = document.createElement('strong');
      strong.textContent = it[0];
      li.appendChild(strong);
      li.appendChild(document.createTextNode(' ' + it[1]));
      steps.appendChild(li);
    });
    form.parentNode.insertBefore(steps, form);
  }

  /* Hide the empty "Reviews (0)" tab until real reviews exist */
  function hideEmptyReviews() {
    document.querySelectorAll('.reviews_tab a, a.reviews_tab, #tab-title-reviews').forEach(function (el) {
      if (/Reviews?\s*\(\s*0\s*\)/i.test(el.textContent)) {
        var li = el.closest('li') || el;
        li.style.display = 'none';
      }
    });
  }

  /* Tracking page: drop the duplicate heading (page H1 and form H2 have the same text) */
  function trackPageFix() {
    if (!document.querySelector('#ip-order-number')) { return; }
    var h1 = document.querySelector('h1');
    if (!h1) { return; }
    var t = h1.textContent.trim().toLowerCase();
    document.querySelectorAll('h2').forEach(function (h) {
      if (h.textContent.trim().toLowerCase() === t) { h.style.display = 'none'; }
    });
  }

  /* Contextual TOOLKIT5 messaging inside the checkout promo box */
  function promoContext() {
    var note = document.querySelector('.ip-promo-note');
    if (!note) { return; }
    var units = 0;
    document.querySelectorAll('.cart_item').forEach(function (row) {
      var q = row.querySelector('input.qty');
      if (q) { units += parseInt(q.value, 10) || 0; return; }
      var m = row.innerText.replace(/\s+/g, ' ').match(/×\s*(\d+)/);
      units += m ? (parseInt(m[1], 10) || 0) : 1;
    });
    var line = note.querySelector('.ip-promo-status');
    if (!line) {
      line = document.createElement('div');
      line.className = 'ip-promo-status';
      note.appendChild(line);
    }
    var disc = document.querySelector('tr.cart-discount');
    if (disc) {
      var amounts = disc.innerText.replace(/\s+/g, ' ').match(/[$₨£€]\s?[\d.,]+/g) || [];
      line.textContent = 'TOOLKIT5 applied' + (amounts.length ? ' — you saved ' + amounts[amounts.length - 1] : '') + '.';
    } else if (units < 2) {
      line.textContent = 'Add one more eligible item to unlock 5% off with TOOLKIT5.';
    } else {
      line.textContent = 'TOOLKIT5 is available for this basket — apply it above in one tap.';
    }
  }

  /* Credit pages: quick balance selects + receive/pay/expiry summary */
  function creditSummary() {
    if (!document.body.classList.contains('single-product')) { return; }
    var range = document.querySelector('[data-credit-range]');
    if (!range) { return; }
    var card = range.closest('.ipl-card') || document;
    var quote = card.querySelector('.ipl-quote:not([hidden])');
    if (!quote) { return; }
    var priceEl = quote.querySelector('.ipl-price');
    var price = priceEl ? priceEl.innerText.replace(/\s+/g, ' ') : '';
    var mm = price.match(/[$₨£€]\s?[\d.,]+/);
    var pay = mm ? mm[0].replace(/\s+/g, ' ') : '';
    var exp = quote.innerText.replace(/\s+/g, ' ').match(/Expires?\s+\d[^·\n]*/i);
    var box = card.querySelector('.ip-credit-summary');
    if (!box) {
      box = document.createElement('p');
      box.className = 'ip-credit-summary';
      var anchor = quote.querySelector('.ipl-buy') || quote;
      anchor.parentNode.insertBefore(box, anchor);
    }
    box.textContent = 'You receive $' + range.value + ' credits · You pay ' + pay + (exp ? ' · ' + exp[0].trim() : '');
  }
  function creditQuickSelects() {
    if (!document.body.classList.contains('single-product')) { return; }
    var range = document.querySelector('[data-credit-range]');
    var wrap = document.querySelector('.ipl-credit-controls');
    if (!range || !wrap || document.querySelector('.ip-credit-quick')) { return; }
    var bar = document.createElement('div');
    bar.className = 'ip-credit-quick';
    [50, 100, 250, 500].forEach(function (v) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'ip-credit-quick-btn';
      b.textContent = '$' + v;
      b.addEventListener('click', function () {
        range.value = String(v);
        range.dispatchEvent(new Event('input', { bubbles: true }));
        range.dispatchEvent(new Event('change', { bubbles: true }));
        creditSummary();
      });
      bar.appendChild(b);
    });
    wrap.parentNode.insertBefore(bar, wrap.nextSibling);
    creditSummary();
  }

  /* Accessibility: focusable elements inside aria-hidden containers are removed from the tab order */
  function a11yFix() {
    document.querySelectorAll('[aria-hidden="true"]').forEach(function (box) {
      box.querySelectorAll('a, button, input, select, textarea, [tabindex]').forEach(function (el) {
        if (el.getAttribute('tabindex') !== '0') { el.setAttribute('tabindex', '-1'); }
      });
    });
  }

  /* Amount-on-the-button CTA: "Continue to payment — $142.50 →" (adopted from competitor review) */
  function payCta() {
    var btn = document.getElementById('place_order');
    if (!btn) { return; }
    if (document.querySelectorAll('input[name^="payment_method"]').length === 0) {
      var none = 'No payment method in this currency yet';
      if (btn.tagName === 'INPUT') { btn.value = none; } else { btn.textContent = none; }
      btn.disabled = true;
      btn.setAttribute('aria-disabled', 'true');
      return;
    }
    btn.disabled = false;
    btn.removeAttribute('aria-disabled');
    var total = document.querySelector('tr.order-total');
    if (!total) { return; }
    var text = total.innerText.replace(/\s+/g, ' ').trim();
    var amount = text.split(' ').slice(1).join(' ') || text.replace(/^total/i, '').trim();
    if (!/[\d.,]/.test(amount)) { return; }
    var next = 'Continue to payment — ' + amount + ' →';
    if (btn.tagName === 'INPUT') { btn.value = next; } else { btn.textContent = next; }
  }

  /* keep the CTA applied across WooCommerce fragment replacements (order-review updates swap the button node) */
  function watchPayCta() {
    var target = document.getElementById('payment') || document.body;
    if (!target || !window.MutationObserver) { return; }
    var pending = null;
    new MutationObserver(function () {
      if (pending) { clearTimeout(pending); }
      pending = setTimeout(payCta, 60);
    }).observe(target, { childList: true, subtree: true });
  }

  /* Credit product pages only: keep the H1 in step with the selected balance (was stale, e.g. "— $50" while $100 selected) */
  function creditHeading() {
    if (!document.body.classList.contains('single-product')) { return; } // never touch shop/grid pages
    var range = document.querySelector('[data-credit-range]');
    var h1 = document.querySelector('h1');
    if (!range || !h1) { return; }
    var value = '$' + parseInt(range.value, 10);
    var base = h1.getAttribute('data-ip-base-title') || h1.textContent.trim();
    if (!h1.getAttribute('data-ip-base-title')) { h1.setAttribute('data-ip-base-title', base); }
    var clean = base.replace(/\s*[—–-]+\s*\$\d+[^\d]*$/i, '').replace(/\$\d+/g, '');
    clean = clean.replace(/[\s—–-]+$/, '');
    var next = (clean ? clean + ' — ' : '') + value + ' credits';
    if (h1.textContent.trim() !== next) { h1.textContent = next; }
  }

  ready(function () {
    setupAutoFocus();
    checkoutSteps();
    payCta();
    watchPayCta();
    hideEmptyReviews();
    trackPageFix();
    promoContext();
    creditQuickSelects();
    a11yFix();
    /* WooCommerce's button script can overwrite the label via the value property, which fires no
       DOM mutations — re-apply on a short schedule to win that race deterministically. */
    [0, 300, 800, 1500, 2500, 4000].forEach(function (t) { setTimeout(payCta, t); });
    couponChip();
    collapseOptional();
    copyables();
    paymentSteps();
    dateHint();
    tiles();
    creditHeading();
    document.addEventListener('change', function (e) {
      if (e.target && e.target.matches && e.target.matches('.ipl-duration-options input[type=radio]')) { tileFrom(); }
    });
    document.addEventListener('input', function (e) {
      if (e.target && e.target.matches && e.target.matches('[data-credit-range]')) { tileFrom(); creditHeading(); creditSummary(); }
    });
    /* re-apply after WooCommerce checkout updates its own fragments */
    if (window.jQuery) {
      window.jQuery(document.body).on('updated_checkout updated_wc_div', function () { couponChip(); collapseOptional(); payCta(); promoContext(); });
    }
  });
})();
