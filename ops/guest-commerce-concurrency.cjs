const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs'),path=require('path'),assert=require('assert/strict'),root=path.resolve(__dirname,'..'),base='http://127.0.0.1:19051';
const secret=JSON.parse(fs.readFileSync(path.join(root,'.qa/guest-commerce-private.json'),'utf8').replace(/^\uFEFF/,''));
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'}),ctx=await browser.newContext();
 await ctx.route('**/*',r=>new URL(r.request().url()).hostname==='127.0.0.1'?r.continue():r.abort());
 const page=await ctx.newPage(),checks=[],run=Date.now().toString(36),reference='CONCURRENT-'+run;
 const check=(n,v)=>{assert.ok(v,n);checks.push(n);console.log('PASS '+n);};
 const state=async()=> (await ctx.request.get(base+'/wp-json/ip-guest-fixture/v1/state')).json();
 const create=async(n)=>{
  for(const id of secret.products){await page.goto(base+'/checkout/?add-to-cart='+id+'&ip_buy_now=1',{waitUntil:'networkidle'});}
  await page.locator('#billing_email').fill('guest-race-'+n+'-'+run+'@example.invalid');await page.locator('#billing_email').blur();await page.waitForTimeout(900);
  if(!await page.locator('#coupon_code').isVisible()){await page.locator('.showcoupon').click();}
  await page.locator('#coupon_code').fill('qa-first');await page.locator('button[name=apply_coupon]').click();await page.locator('tr.cart-discount').waitFor();
  check('new email '+n+' qualifies after earlier purchases',(await page.locator('tr.cart-discount').innerText()).includes('3.20'));
  await page.locator('#payment_method_instapass_binance').check();if(await page.locator('#terms').count()){await page.locator('#terms').check();}await page.waitForTimeout(900);await page.locator('#place_order').click();await page.waitForURL(/order-received/);
  const payload=await page.locator('.ip-payment-report form').evaluate(f=>Object.fromEntries(new FormData(f)));
  delete payload.payment_screenshot;return payload;
 };
 const a=await create('a'),b=await create('b');
 const form=p=>({...p,payment_method:'instapass_binance',payment_reference:reference});
 const responses=await Promise.all([ctx.request.post(base+'/wp-admin/admin-post.php',{form:form(a),maxRedirects:0}),ctx.request.post(base+'/wp-admin/admin-post.php',{form:{...form(b),payment_method:'instapass_crypto'},maxRedirects:0})]);
 check('simultaneous cross-order transfer has one winner',responses.filter(r=>r.status()===302).length===1&&responses.filter(r=>r.status()===400).length===1);
 let s=await state(),rows=s.orders.filter(o=>[Number(a.order_id),Number(b.order_id)].includes(o.id));
 check('only one order records shared reference',rows.filter(o=>o.reference===reference).length===1);
 check('neither submitted order is marked paid',rows.every(o=>!o.paid&&o.status==='on-hold'));
 const winner=rows.find(o=>o.reference===reference),winning=Number(a.order_id)===winner.id?a:b,losing=winning===a?b:a;
 const retry=await ctx.request.post(base+'/wp-admin/admin-post.php',{form:form(winning),maxRedirects:0});
 check('same-order retry is accepted safely',retry.status()===302);
 s=await state();check('retry does not duplicate owner email',s.mail.filter(m=>m.subject==='Payment reported — Order #'+winner.id).length===1);
 const invalid=await ctx.request.post(base+'/wp-admin/admin-post.php',{form:{...form(losing),payment_reference:'OTHER-'+run,payment_method:'instapass_easypaisa'},maxRedirects:0});
 check('PKR method cannot be forged onto USD report',invalid.status()===400);
 const privateImage=await ctx.request.get(base+'/wp-admin/admin-post.php?action=ip_payment_screenshot&order_id='+winner.id);
 check('guest cannot access owner screenshot endpoint',privateImage.status()>=400);
 fs.writeFileSync(path.join(root,'outputs/guest-commerce-concurrency-verification.json'),JSON.stringify({scope:'isolated fixture, actual parallel HTTP submissions, no funds or external emails',pass_count:checks.length,checks},null,2));
 await browser.close();
})().catch(e=>{console.error(e.stack);process.exit(1);});
