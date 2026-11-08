const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs'),path=require('path');
const out=path.resolve(__dirname,'../outputs/site-review-20261108');
const base='https://instapass.online';
const R={};
(async()=>{
  const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
  const ctx=await browser.newContext({viewport:{width:1440,height:1000}});
  const p=await ctx.newPage();
  const dismiss=async()=>{const d=p.getByRole('button',{name:'Dismiss this notice'});if(await d.isVisible().catch(()=>false))await d.click().catch(()=>{});};

  // 1. Claude Pro product detail
  await p.goto(base+'/product/claude-pro/',{waitUntil:'domcontentloaded'});await dismiss();await p.waitForTimeout(800);
  R.vpn_banner=await p.locator('[class*="vpn"],[id*="vpn"],[class*="safety"]').first().isVisible().catch(()=>false);
  R.pro_detail={url:p.url(),body:(await p.locator('main').innerText()).replace(/\s+/g,' ').slice(0,900)};
  const dur=await p.locator('input[type=radio], .ip-option input, label input').count().catch(()=>0);
  R.pro_detail.radio_count=dur;
  await p.screenshot({path:path.join(out,'product-claude-pro.png')});

  // 2. credits slider product
  await p.goto(base+'/product/claude-credits/',{waitUntil:'domcontentloaded'});await dismiss();await p.waitForTimeout(800);
  const slider=p.locator('input[type=range]');
  R.credits={url:p.url(),has_slider:await slider.count()>0};
  if(R.credits.has_slider){R.credits.min=await slider.first().getAttribute('min');R.credits.max=await slider.first().getAttribute('max');R.credits.step=await slider.first().getAttribute('step');
    await slider.first().fill('150');await p.waitForTimeout(400);R.credits.after_150=(await p.locator('main').innerText()).replace(/\s+/g,' ').slice(0,500);}
  else R.credits.body=(await p.locator('main').innerText()).replace(/\s+/g,' ').slice(0,500);
  await p.screenshot({path:path.join(out,'product-claude-credits.png')});

  // 3. currency switching on shop
  await p.goto(base+'/shop/',{waitUntil:'domcontentloaded'});await dismiss();await p.waitForTimeout(600);
  const pkr=p.locator('.ip-currency-bar button:has-text("PKR"), .ip-currency-bar a:has-text("PKR")').first();
  if(await pkr.count()){await pkr.click();await p.waitForTimeout(1500);}
  R.after_pkr=(await p.locator('.ipl-card').first().innerText()).replace(/\s+/g,' ').slice(0,400);
  const gbp=p.locator('.ip-currency-bar button:has-text("GBP"), .ip-currency-bar a:has-text("GBP")').first();
  if(await gbp.count()){await gbp.click();await p.waitForTimeout(1500);}
  R.after_gbp=(await p.locator('.ipl-card').first().innerText()).replace(/\s+/g,' ').slice(0,400);
  const eur=p.locator('.ip-currency-bar button:has-text("EUR"), .ip-currency-bar a:has-text("EUR")').first();
  if(await eur.count()){await eur.click();await p.waitForTimeout(1500);}
  R.after_eur=(await p.locator('.ipl-card').first().innerText()).replace(/\s+/g,' ').slice(0,400);

  // 4. checkout exploration: 2x Max5x monthly
  await p.goto(base+'/checkout/?add-to-cart=31&quantity=2',{waitUntil:'domcontentloaded'});await dismiss();await p.waitForTimeout(1500);
  R.checkout_url=p.url();
  const main=await p.locator('main').innerText().catch(()=>p.locator('body').innerText());
  R.checkout_body=main.replace(/\s+/g,' ').slice(0,2200);
  R.country_selector=await p.locator('select[name*="country"], [class*="country-of-use"]').count();
  await p.screenshot({path:path.join(out,'checkout-explore.png'),fullPage:true});
  // coupon UI
  R.coupon_field=await p.locator('#coupon_code').count();
  // payment methods
  R.payment_methods=await p.locator('input[name^="payment_method"]').evaluateAll(els=>els.map(e=>e.value)).catch(()=>[]);
  // allocation UI
  R.allocation=await p.locator('.ip-allocation').count();

  fs.writeFileSync(path.join(out,'interaction-report.json'),JSON.stringify(R,null,2));
  console.log(JSON.stringify(R,null,1).slice(0,7000));
  await browser.close();
})().catch(e=>{console.error(e.stack);process.exit(1);});
