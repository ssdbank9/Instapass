(function () {
 'use strict';
 const form = document.getElementById('ip-price-form');
 if (!form) return;
 const decimals = Number(form.dataset.decimals), status = document.getElementById('ip-pricing-status');
 function mark(row) { row.querySelector('[data-selected]').checked = true; status.textContent = 'Unsaved changes. Save changes to apply selected rows.'; }
 form.addEventListener('input', function (event) {
  const field = event.target.dataset.field, row = event.target.closest('[data-price-row]');
  if (!field || !row) return;
  const regular = row.querySelector('[data-field="regular"]'), sale = row.querySelector('[data-field="sale"]'), discount = row.querySelector('[data-field="discount"]'), mode = row.querySelector('[data-mode]');
  const base = Number(regular.value), percent = Number(discount.value);
  if (field === 'discount' || (field === 'regular' && discount.value !== '')) {
   mode.value = 'discount';
   if (discount.value === '' || percent === 0) sale.value = '';
   else if (base > 0 && percent >= 0 && percent <= 100) {
    const target = base * (1 - percent / 100), round99 = form.querySelector('#ip-round-99')?.checked && percent < 100;
    sale.value = (round99 ? Math.max(.99, Math.round(target + .01 + Number.EPSILON) - .01) : target).toFixed(decimals);
   }
  } else {
   mode.value = 'sale';
   discount.value = sale.value !== '' && base > 0 ? ((1 - Number(sale.value) / base) * 100).toFixed(2) : '';
  }
  mark(row);
 });
 form.addEventListener('change', function (event) {
  if (event.target.matches('[data-start-now]')) mark(event.target.closest('[data-price-row]'));
  if (event.target.matches('[data-enabled]')) { const row=event.target.closest('[data-price-row]'); row.querySelector('[data-availability-changed]').value='1'; mark(row); }
  if (event.target.matches('[data-stock]')) { const row=event.target.closest('[data-price-row]'); row.querySelector('[data-stock-changed]').value='1'; mark(row); }
  if (event.target.id === 'ip-round-99') form.querySelectorAll('[data-price-row]').forEach(function(row) {
   if (row.querySelector('[data-selected]')?.checked && row.querySelector('[data-mode]')?.value === 'discount') row.querySelector('[data-field="discount"]').dispatchEvent(new Event('input', {bubbles:true}));
  });
 });
 form.addEventListener('submit', function () { status.textContent = 'Saving selected prices…'; });
})();
