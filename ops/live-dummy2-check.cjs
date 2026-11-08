const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs'),path=require('path'),assert=require('assert/strict'),crypto=require('crypto'),{execFileSync}=require('child_process');
const root=path.resolve(__dirname,'..'),out=path.join(root,'outputs/live-dummy-order-20261108.json'),base='https://instapass.online';
if(fs.existsSync(out))throw new Error('A dummy-test receipt for 2026-11-08 already exists. Review it before placing another order.');
const run='dummy-'+new Date().toISOString().replace(/\D/g,'').slice(0,14)+'-'+crypto.randomBytes(3).toString('hex'),receipt='instapass0408+'+run+'@gmail.com',addresses=[1,2].map(i=>'instapass0408+'+run+'-a'+i+'@gmail.com');
function audit(mode,id=0){const cmd='sudo -n docker compose --env-file /opt/instapass/.env -f /opt/instapass/maintenance.yaml run --rm -v /home/ubuntu/live-dummy2-check.php:/tmp/dummy2.php:ro wpcli eval-file /tmp/dummy2.php '+mode+' '+id+' '+run;return JSON.parse(execFileSync('ssh',['-i','C:/Users/Aly Jafferani/.ssh/rdi-tracker-oracle','-o','BatchMode=yes','-o','ConnectTimeout=15','ubuntu@145.241.115.28',cmd],{encoding:'utf8',timeout:90000,stdio:['ignore','pipe','pipe']}));}
(async()=>{
  const result={run,scope:'Owner-authorized single live dummy order (2026-11-08 full site review)',checks:[]};let browser,submitted=false,id=0,failure;
  const check=(n,v)=>{assert.ok(v,n);result.checks.push(n);console.log('PASS '+n);};
  try{
    result.before=audit('before');check('owner inbox, toolkit5 and product preflight',result.before.ready&&result.before.admin_recipient_owner);
    browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
    const ctx=await browser.newContext({viewport:{width:390,height:950}}),p=await ctx.newPage();
    await p.goto(base+'/checkout/?add-to-cart=31&quantity=2',{waitUntil:'domcontentloaded'});
    await p.waitForTimeout(1500);
    result.vpn_banner_over_place_order=await p.evaluate(()=>{const v=document.getElementById('ip-vpn-safety'),b=document.getElementById('place_order');if(!v||!b)return 'missing';const r1=v.getBoundingClientRect(),r2=b.getBoundingClientRect();return r1.bottom>r2.top&&r1.top<r2.bottom?'overlaps':'clear';});
    await p.locator('#ip-vpn-safety [data-ip-vpn-dismiss]').click().catch(()=>{});
    const box=p.locator('.ip-allocation');
    await box.locator('.ip-allocation-mode').selectOption('custom');
    await box.locator('.ip-allocation-count').selectOption('2');
    check('two selected accounts have two email inputs',await box.locator('.ip-allocation-account:visible input[type=email]').count()===2);
    for(let i=0;i<2;i++){const row=box.locator('.ip-allocation-account').nth(i);await row.locator('.ip-allocation-units').fill('1');await row.locator('select').selectOption(i?'new':'existing');await row.locator('input[type=email]').fill(addresses[i]);}
    await p.locator('#billing_email').fill(receipt);await p.locator('#billing_email').blur();
    await p.locator('#instapass_usage_country').selectOption('US');
    if(!await p.locator('#coupon_code').isVisible())await p.locator('.showcoupon').click();
    await p.locator('#coupon_code').fill('TOOLKIT5');await p.locator('button[name=apply_coupon]').click();await p.locator('tr.cart-discount').waitFor();
    check('7.50USD discount and142.50USD payable total',(await p.locator('tr.cart-discount').innerText()).includes('7.50')&&(await p.locator('tr.order-total').innerText()).includes('142.50'));
    await p.screenshot({path:path.join(root,'outputs/site-review-20261108/dummy-checkout-mobile.png'),fullPage:true});
    await p.locator('#payment_method_instapass_binance').check();if(await p.locator('#terms').count())await p.locator('#terms').check();await p.waitForTimeout(700);
    submitted=true;await p.locator('#place_order').click();await p.waitForURL(/order-received\/\d+/,{timeout:60000});id=Number(new URL(p.url()).pathname.match(/order-received\/(\d+)/)[1]);
    console.log('DUMMY ORDER CREATED #'+id);
    await p.waitForTimeout(1500);
    await p.screenshot({path:path.join(root,'outputs/site-review-20261108/dummy-order-received.png'),fullPage:true});
    result.verify=audit('verify',id);
    check('saved order is guest, unpaid, unreported, unverified',result.verify.guest&&!result.verify.paid&&!result.verify.payment_reported&&!result.verify.payment_verified);
    check('both activation emails and1+1months persisted',result.verify.all_activation_emails_saved&&result.verify.accounts===2&&JSON.stringify(result.verify.units)==='[1,1]');
    check('country of use saved as US',result.verify.usage_country==='US');
    const text=await p.locator('body').innerText();
    check('private receipt shows both activation emails',addresses.every(a=>text.includes(a)));
    check('private receipt shows chosen duration split',text.includes('1 month + 1 month across 2 accounts'));
    check('private receipt shows Binance payment instructions',/Binance/i.test(text));
    await p.goto(base+'/track-order/',{waitUntil:'domcontentloaded'});await p.locator('#ip-order-number').fill(String(id));await p.locator('#ip-order-email').fill(receipt);await Promise.all([p.waitForNavigation({waitUntil:'domcontentloaded'}),p.getByRole('button',{name:'Track order',exact:true}).click()]);
    const tracking=await p.locator('.ip-track-result').innerText();
    check('tracking shows split, discount and total',tracking.includes('across 2 accounts')&&tracking.includes('7.50')&&tracking.includes('142.50'));
    check('tracking hides activation destinations',addresses.every(a=>!tracking.includes(a)));
    await p.screenshot({path:path.join(root,'outputs/site-review-20261108/dummy-tracking-mobile.png'),fullPage:true});
    await p.locator('#ip-order-email').fill('wrong.address@example.com');await Promise.all([p.waitForNavigation({waitUntil:'domcontentloaded'}),p.getByRole('button',{name:'Track order',exact:true}).click()]);
    const wrongResult=await p.locator('.ip-track-result').innerText().catch(()=>'');
    check('wrong email reveals no order details',!wrongResult.includes('142.50')&&!wrongResult.includes('across 2 accounts')&&!addresses.some(a=>wrongResult.includes(a)));
  }catch(e){failure=e;result.error=e.message;console.error(e.message);}
  finally{
    if(submitted){try{result.cancel=audit('cancel',id);if(!result.cancel.not_found){id=result.cancel.id;check('dummy clearly marked and cancelled without payment',result.cancel.test_marked&&result.cancel.status==='cancelled'&&!result.cancel.paid&&!result.cancel.payment_verified);const before=result.before.state,after=result.cancel.state;check('original orders preserved',Object.entries(before.orders).every(([k,v])=>after.orders[k]===v));check('only one new order created',Object.keys(after.orders).length===Object.keys(before.orders).length+1);check('stock and coupon usage restored',before.coupon_usage===after.coupon_usage&&before.stock_quantity===after.stock_quantity&&before.stock_status===after.stock_status);}}catch(e){result.cleanup_error=e.message;failure=failure||e;console.error('Dummy cancellation requires attention: '+e.message);}}
    if(browser)await browser.close();result.order_id=id;result.pass_count=result.checks.length;fs.writeFileSync(out,JSON.stringify(result,null,2));console.log(JSON.stringify({order_id:id,run,pass_count:result.pass_count,cancelled:result.cancel?.status==='cancelled',error:result.error||result.cleanup_error||null}));if(failure)process.exitCode=1;
  }
})().catch(e=>{console.error(e.message);process.exitCode=1;});
