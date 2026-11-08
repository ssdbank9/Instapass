const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs'),path=require('path'),assert=require('assert/strict');
const root=path.resolve(__dirname,'..'),base='http://127.0.0.1:19062',privateFile=path.join(root,'.qa/account-allocation-private.json'),secret=JSON.parse(fs.readFileSync(privateFile,'utf8').replace(/^\uFEFF/,''));
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'}),checks=[],errors=[];
 const check=(n,ok)=>{assert.ok(ok,n);checks.push(n);console.log('PASS '+n);};
 for(const parts of [[4],[3,1],[2,2],[2,1,1],[1,1,1,1]]){
  const label=parts.join('+'),ctx=await browser.newContext({viewport:{width:390,height:950}});await ctx.route('**/*',r=>new URL(r.request().url()).hostname==='127.0.0.1'?r.continue():r.abort());const p=await ctx.newPage();p.on('pageerror',e=>errors.push(e.message));
  await p.goto(base+'/checkout/?add-to-cart='+secret.monthly+'&quantity=4',{waitUntil:'networkidle'});
  check(label+' allocation shown to guest',await p.locator('.ip-allocation').count()===1&&await p.locator('#createaccount').count()===0);
  const box=p.locator('.ip-allocation');await box.locator('.ip-allocation-mode').selectOption(parts.length===1?'one':parts.length===4?'each':'custom');
  for(let i=0;i<parts.length;i++){
   const row=box.locator('.ip-allocation-account').nth(i);if(parts.length>1&&parts.length<4)await row.locator('.ip-allocation-units').fill(String(parts[i]));await row.locator('select').selectOption(i%2?'new':'existing');await row.locator('input[type=email]').fill('browser-'+label+'-'+i+'@example.invalid');
  }
  check(label+' total allocation updates',(await box.locator('.ip-allocation-total').innerText()).startsWith('4 of 4 units allocated across '+parts.length+' account'));
  check(label+' mobile checkout has no horizontal overflow',await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));
  if(label==='2+1+1')await p.screenshot({path:path.join(root,'outputs/account-allocation-390.png'),fullPage:true});
  const receipt='browser-receipt-'+label+'@example.invalid';await p.locator('#billing_email').fill(receipt);await p.locator('#billing_email').blur();
  if(!await p.locator('#coupon_code').isVisible())await p.locator('.showcoupon').click();await p.locator('#coupon_code').fill('TOOLKIT10');await p.locator('button[name=apply_coupon]').click();await p.locator('tr.cart-discount').waitFor();
  check(label+' coupon still discounts four units',(await p.locator('tr.cart-discount').innerText()).includes('30.00'));
  check(label+' split survives checkout update',await box.locator('.ip-allocation-mode').inputValue()===(parts.length===1?'one':parts.length===4?'each':'custom'));
  const methods=await p.locator('input[name=payment_method]').evaluateAll(els=>els.map(e=>e.id));check(label+' manual payment method available',methods.length>0);await p.locator('#'+methods[0]).check();if(await p.locator('#terms').count())await p.locator('#terms').check();await p.waitForTimeout(700);await p.locator('#place_order').click();
  await p.waitForURL(/order-received/,{timeout:30000}).catch(async e=>{console.error('Checkout rejected: '+JSON.stringify(await p.locator('.woocommerce-error').allTextContents()));throw e;});
  const body=await p.locator('body').innerText(),orderId=new URL(p.url()).pathname.match(/order-received\/(\d+)/)[1];
  check(label+' private order contains saved split',body.includes(parts.map(n=>n+' month'+(n===1?'':'s')).join(' + ')+' across '+parts.length+' account'));
  check(label+' private instructions include account emails',parts.every((n,i)=>body.includes('browser-'+label+'-'+i+'@example.invalid')));
  check(label+' final price stays 270 USD',body.includes('$270.00'));
  await p.goto(base+'/track-order/',{waitUntil:'domcontentloaded'});await p.locator('#ip-order-number').fill(orderId);await p.locator('#ip-order-email').fill(receipt);await Promise.all([p.waitForNavigation({waitUntil:'domcontentloaded'}),p.getByRole('button',{name:'Track order',exact:true}).click()]);
  const result=await p.locator('.ip-track-result').innerText();check(label+' tracking has split and discount',result.includes('across '+parts.length+' account')&&result.includes('30.00')&&result.includes('270.00'));check(label+' tracking hides activation destinations',!result.includes('browser-'+label+'-0@example.invalid'));
  await ctx.close();
 }
 const owner=await browser.newContext();const p=await owner.newPage();await p.goto(base+'/wp-login.php');await p.locator('#user_login').fill(secret.username);await p.locator('#user_pass').fill(secret.password);await Promise.all([p.waitForNavigation(),p.locator('#wp-submit').click()]);await p.goto(base+'/wp-admin/admin.php?page=instapass-offers&offer='+secret.monthly);check('owner allocation setting visible and enabled',await p.locator('#ipo-allocation').inputValue()==='yes');await p.locator('#ipo-allocation').selectOption('no');await Promise.all([p.waitForNavigation(),p.getByRole('button',{name:'Save offer',exact:true}).click()]);check('owner can disable allocation without code',await p.locator('#ipo-allocation').inputValue()==='no');
 const anon=await browser.newContext();const a=await anon.newPage();await a.goto(base+'/checkout/?add-to-cart='+secret.monthly+'&quantity=4',{waitUntil:'networkidle'});check('disabled product omits allocation form',await a.locator('.ip-allocation').count()===0);await anon.close();await owner.close();
 const nojs=await browser.newContext({javaScriptEnabled:false});const n=await nojs.newPage();await n.goto(base+'/checkout/?add-to-cart=31&quantity=4');check('no JavaScript still has all account fields',await n.locator('.ip-allocation-account').count()===4);await nojs.close();
 check('browser has no JavaScript exceptions',errors.length===0);fs.writeFileSync(path.join(root,'outputs/account-allocation-browser.json'),JSON.stringify({scope:'Isolated real WordPress; synthetic orders; mail/network blocked',pass_count:checks.length,checks,errors},null,2));await browser.close();
})().catch(e=>{console.error(e.stack);process.exit(1);});
