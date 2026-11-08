const { chromium } = require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const assert = require('assert/strict');

(async () => {
  const browser = await chromium.launch({ headless: true, executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe' });
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  const page = await context.newPage();
  const checks = [];
  const check = (name, ok) => { assert.ok(ok, name); checks.push(name); console.log('PASS ' + name); };

  const home = await page.goto('https://instapass.online/', { waitUntil: 'domcontentloaded' });
  check('homepage returns HTTP 200', home.status() === 200);
  const banner = page.locator('#ip-vpn-safety');
  check('floating VPN notice is visible', await banner.isVisible());
  check('warning names ChatGPT, Claude, VPN and access consequences', /ChatGPT.*Claude.*VPN/i.test(await banner.innerText()) && /suspended|terminated/i.test(await banner.innerText()));
  check('provider supported-country guidance links are present', await banner.locator('a[href*="openai.com"]').count() === 1 && await banner.locator('a[href*="anthropic.com"]').count() === 1);
  check('banner is fixed and mobile layout fits viewport', await banner.evaluate(el => getComputedStyle(el).position === 'fixed') && await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
  await banner.getByRole('button', { name: 'Dismiss this notice' }).click();
  check('banner dismisses for current browser session', await banner.evaluate(el => el.hidden));

  for (const [id, name] of [[29, 'ChatGPT'], [30, 'Claude'], [144, 'Claude Credits']]) {
    const response = await page.goto('https://instapass.online/checkout/?add-to-cart=' + id + '&ip_buy_now=1', { waitUntil: 'domcontentloaded' });
    check(name + ' checkout returns HTTP 200', response.status() === 200);
    const field = page.locator('#instapass_usage_country');
    const required = await field.count() === 1 && await field.isVisible() && await field.getAttribute('aria-required') === 'true' && /validate-required/.test(await field.evaluate(el => el.closest('.form-row')?.className || ''));
    if (!required) throw new Error(name + ' selector/cart diagnostic: ' + JSON.stringify({ count: await field.count(), visible: await field.isVisible().catch(() => false), attrs: await field.evaluate(el => ({required:el.required,ariaRequired:el.getAttribute('aria-required'),className:el.className,wrapper:el.closest('.form-row')?.className,wrapperVisible:!!el.closest('.form-row')&&getComputedStyle(el.closest('.form-row')).display!=='none'})).catch(() => null), cart: await page.locator('tr.cart_item').allInnerTexts() }));
    check(name + ' checkout requires country of use', required);
    check(name + ' country selector starts blank and offers Pakistan/US/UK', await field.inputValue() === '' && await field.locator('option[value="PK"]').count() === 1 && await field.locator('option[value="US"]').count() === 1 && await field.locator('option[value="GB"]').count() === 1);
  }
  check('no order was placed', await page.locator('form.checkout').count() === 1);
  console.log(JSON.stringify({ result: 'pass', checks: checks.length, scope: 'anonymous public browser; no checkout submission, order, payment or email' }));
  await context.close();
  await browser.close();
})().catch(error => { console.error(error.stack); process.exit(1); });
