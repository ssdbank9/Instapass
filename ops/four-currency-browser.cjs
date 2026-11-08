const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs'),path=require('path'),assert=require('assert/strict');
const base=process.env.IP4_BASE||'http://127.0.0.1:19101',out=path.resolve(__dirname,'../outputs'),label=base.startsWith('https:')?'live':'preview';
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});let checks=0;const check=(v,l)=>{assert.ok(v,l);checks++;};
 for(const cur of ['USD','GBP','EUR','PKR']){
  const context=await browser.newContext({viewport:{width:1440,height:1000}});await context.addCookies([{name:'instapass_currency',value:cur,url:base}]);const page=await context.newPage();
  const res=await page.goto(base+'/shop/',{waitUntil:'networkidle'});check(res.status()===200,cur+' shop200');const dismiss=page.getByRole('button',{name:'Dismiss this notice'});if(await dismiss.isVisible())await dismiss.click();
  check((await page.locator('.ip-currency-bar').innerText()).includes('Prices in '+cur),cur+' selected');check(await page.locator('.ip-currency-bar nav a').count()===4,'Four currency buttons');
  check(await page.locator('.ipl-grid .ipl-card').count()===7,'Seven tiles');check(await page.locator('.ipl-grid').evaluate(el=>getComputedStyle(el).display==='grid'),'Styles loaded');
  const body=await page.locator('main').innerText();check(body.includes('TOOLKIT5')&&!body.includes('TOOLKIT75')&&!body.includes('TOOLKIT10'),'Only single coupon promoted');check(body.includes('2 or more items'),'2+ wording');
  for(const name of ['Claude Pro','ChatGPT Plus']){
   const tile=page.locator('.ipl-card').filter({has:page.getByRole('heading',{name,exact:true})});check(await tile.locator('img').evaluate(n=>n.complete&&n.naturalWidth>0),cur+' logo '+name);
   for(const [m,usd,monthly] of [[1,15,15],[6,87,14.5],[12,168,14]]){
    await tile.locator('input[value="'+m+'"]').check();const panel=tile.locator('.ipl-quote:not([hidden])');const full=cur==='PKR'?{1:4200,6:24400,12:47000}[m]:usd;
    check(await panel.locator('.ip-term-pricing').getAttribute('data-upfront-cents')===String(full*100),cur+' upfront '+name+' '+m);
    const text=await panel.innerText();check(text.includes('paid upfront')&&text.includes('TOOLKIT5')&&!text.includes('7.5%')&&!text.includes('10%'),cur+' term bundle '+name+' '+m);
    check(await panel.locator('.ipl-buy').getAttribute('href').then(x=>x.includes('add-to-cart=')),cur+' buy '+name+' '+m);
    if(cur!=='PKR')check((await panel.locator('.ip-monthly-primary').innerText()).includes(monthly.toFixed(2)),cur+' monthly '+name+' '+m);
   }
  }
  for(const name of ['Claude Max 5×','Claude Ultra 20×','Canva Pro','Claude Credits','ChatGPT Credits']){const tile=page.locator('.ipl-card').filter({has:page.getByRole('heading',{name,exact:true})});check((await tile.locator('.ipl-quote:not([hidden])').innerText()).includes('Pricing coming soon'),'Held '+name+' '+cur);check(await tile.locator('.ipl-quote:not([hidden]) button.ipl-buy').isDisabled(),'Held buy '+name+' '+cur);}
  for(const range of await page.locator('input[type=range]').all())check(await range.getAttribute('min')==='50'&&await range.getAttribute('max')==='500'&&await range.getAttribute('step')==='25','Credit limits unchanged');
  check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),cur+' desktop width');
  if(cur==='USD'){await page.locator('.ipl-grid').screenshot({path:path.join(out,'four-currency-'+label+'-tiles.png')});await page.screenshot({path:path.join(out,'four-currency-'+label+'-desktop.png'),fullPage:true});}
  await page.setViewportSize({width:390,height:844});check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),cur+' mobile width');if(cur==='EUR'){await page.screenshot({path:path.join(out,'four-currency-'+label+'-mobile.png'),fullPage:true});await page.locator('.ipl-card').first().screenshot({path:path.join(out,'four-currency-'+label+'-mobile-card.png')});}
  if(cur==='EUR'){await page.locator('.ipl-card').first().locator('.ipl-quote:not([hidden]) .ipl-details').click();await page.locator('.ipl-card--detail').waitFor();check(await page.locator('.ipl-card--detail').count()===1,'EUR detail selector');check((await page.locator('.ipl-card--detail .ipl-quote:not([hidden])').innerText()).includes('paid upfront'),'EUR detail price');}
  await context.close();console.log('PASS '+cur+' visual and price checks');
 }
 // Session-only guest checkout; never click Place order or submit customer details.
 for(const cur of ['USD','EUR','PKR']){
  const context=await browser.newContext();await context.addCookies([{name:'instapass_currency',value:cur,url:base}]);const page=await context.newPage();await page.goto(base+'/checkout/?add-to-cart=30&quantity=2',{waitUntil:'networkidle'});
  if(!await page.locator('#coupon_code').isVisible())await page.locator('.showcoupon').click();await page.locator('#coupon_code').fill('TOOLKIT5');await page.locator('button[name=apply_coupon]').click();await page.locator('tr.cart-discount').waitFor();await page.waitForLoadState('networkidle');
  const total=await page.locator('.order-total .woocommerce-Price-amount').first().innerText();const number=Number(total.replace(/[^0-9.]/g,''));check(Math.abs(number-(cur==='PKR'?7980:28.5))<.011,cur+' guest 2 repeats coupon total');
  check(await page.locator('input[name=instapass_quote_signature]').count()===1,cur+' signed quote');await context.close();console.log('PASS '+cur+' guest coupon checkout');
 }
 const result={result:'pass',checks,base,no_order_submitted:true,no_customer_details_entered:true};fs.writeFileSync(path.join(out,'four-currency-'+label+'-browser.json'),JSON.stringify(result,null,2));console.log(JSON.stringify(result));await browser.close();
})().catch(e=>{console.error(e.stack);process.exit(1)});
