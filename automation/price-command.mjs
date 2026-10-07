/** Pure preview engine: no network, credentials, database writes or model charges. */
export function preparePriceCommand(text, products, currency = 'USD') {
  if (typeof text !== 'string' || text.length > 500) throw new Error('Enter a command of at most 500 characters.');
  const command = text.trim();
  let match = command.match(/^set (.+?) (regular|sale) price to (?:\$)?(\d+(?:\.\d{1,2})?)$/i);
  let target, operation, value;
  if (match) {
    [, target, operation, value] = match;
  } else {
    match = command.match(/^give (.+?) (\d+(?:\.\d{1,2})?)% (?:off|discount)(?: (exact|rounded))?$/i);
    if (!match) throw new Error('Use: Set PRODUCT regular/sale price to AMOUNT; or Give PRODUCT/CATEGORY N% off exact/rounded.');
    target = match[1]; value = match[2]; operation = match[3] === 'exact' ? 'discount_exact' : 'discount_rounded';
  }
  value = Number(value);
  if (!Number.isFinite(value) || value <= 0 || value > 100000 || (operation.startsWith('discount') && value >= 100)) throw new Error('Value is outside the permitted range.');
  const normalized = s => String(s).trim().toLowerCase();
  target = normalized(target);
  const category = target.match(/^category (.+)$/);
  const selected = products.filter(p => category
    ? Array.isArray(p.categories) && p.categories.some(c => normalized(c) === category[1]) && p.status === 'publish'
    : normalized(p.sku) === target || normalized(p.name) === target);
  if (!selected.length) throw new Error('No exact product or published category match. Use the full name or SKU.');
  if (!category && selected.length !== 1) throw new Error('Multiple products match. Use a unique SKU.');
  if (selected.length > 50) throw new Error('At most 50 products per preview.');
  const money = n => (Math.round((n + Number.EPSILON) * 100) / 100).toFixed(2);
  const changes = selected.map(p => {
    if (!['simple','external','variation'].includes(p.type)) throw new Error('Edit a variable product using its individual variation SKU.');
    if (!Number.isInteger(p.id) || p.id <= 0 || typeof p.fingerprint !== 'string' || !p.fingerprint) throw new Error('Catalogue must contain product IDs and current pricing fingerprints.');
    const regular = Number(p.regular_price);
    const sale = p.sale_price === '' || p.sale_price == null ? null : Number(p.sale_price);
    if (p.regular_price === '' || p.regular_price == null || !Number.isFinite(regular) || regular <= 0 || (sale !== null && (!Number.isFinite(sale) || sale < 0))) throw new Error('Product needs valid current prices before automation.');
    let nextRegular = regular, nextSale = sale;
    if (operation === 'regular') nextRegular = value;
    else if (operation === 'sale') nextSale = value;
    else {
      const exact = regular * (1 - value / 100);
      nextSale = operation === 'discount_exact' ? Number(money(exact)) : Math.max(0.99, Math.floor(exact + 0.51) - 0.01);
    }
    if (nextSale !== null && (nextSale <= 0 || nextSale >= nextRegular)) throw new Error('Sale price must be positive and below regular price; choose exact rounding for low prices.');
    return {
      product_id:p.id, sku:p.sku, name:p.name, fingerprint:p.fingerprint,
      before:{regular_price:money(regular), sale_price:sale === null ? '' : money(sale)},
      after:{regular_price:money(nextRegular), sale_price:nextSale === null ? '' : money(nextSale)},
      actual_discount_percent:nextSale === null ? 0 : Number(((1 - nextSale / nextRegular)*100).toFixed(2)),
      sale_timing:'preserve_existing_dates', status:p.status
    };
  });
  return {schema_version:1, state:'preview_only', currency, command, requested_percent:operation.startsWith('discount') ? value : null, rounding:operation === 'discount_rounded' ? 'nearest_.99' : 'exact', requires_owner_confirmation:true, changes};
}
