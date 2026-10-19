const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright'),fs=require('fs'),path=require('path'),assert=require('assert/strict'),root=path.resolve(__dirname,'..');
(async()=>{const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
try{const ctx=await browser.newContext({viewport:{width:390,height:844}}),p=await ctx.newPage(),checks=[];let cohost;
const check=(name,ok)=>{assert.ok(ok,name);checks.push(name);console.log('PASS '+name);};
const add=async id=>p.goto('https://instapass.online/checkout/?add-to-cart='+id+'&ip_buy_now=1',{waitUntil:'networkidle'});
const coupon=async code=>{if(!await p.locator('#coupon_code').isVisible())await p.locator('.showcoupon').click();await p.locator('#coupon_code').fill(code);await p.locator('button[name=apply_coupon]').click();await p.waitForTimeout(1500);};
await p.goto('https://instapass.online/shop/?ip_currency=USD',{waitUntil:'domcontentloaded'});await add(29);await add(30);await coupon('TOOLKIT75');
check('two selections reject middle coupon',await p.locator('tr.cart-discount').count()===0);
await add(31);await coupon('TOOLKIT75');
check('three selections receive 7.5% discount of 7.42',(await p.locator('tr.cart-discount').innerText()).includes('7.42'));
check('three-selection total is 91.55',(await p.locator('tr.order-total').innerText()).includes('91.55'));
const codes=await p.locator('.ip-promo-note code').allTextContents();check('all three offers displayed in order',JSON.stringify(codes)===JSON.stringify(['TOOLKIT5','TOOLKIT75','TOOLKIT10']));
check('guest checkout remains account optional',await p.locator('#createaccount').count()===0);
check('checkout fits phone',await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));
await p.screenshot({path:path.join(root,'outputs/three-product-live-390.png'),fullPage:true});
await add(32);await coupon('TOOLKIT10');check('four-selection coupon replaces middle without stacking',await p.locator('tr.cart-discount').count()===1&&(await p.locator('tr.cart-discount').innerText()).includes('24.90'));
check('Instapass storefront HTTPS returns 200',(await p.goto('https://instapass.online/',{waitUntil:'domcontentloaded'})).status()===200);
const hp=await ctx.newPage();try{const response=await hp.goto('https://wasooli.duckdns.org/',{waitUntil:'domcontentloaded'});cohost={verified:response.status()===200,status:response.status()};}catch(e){cohost={verified:false,error:String(e.message).split('\n')[0]};}finally{await hp.close();}
fs.writeFileSync(path.join(root,'outputs/three-product-live-verification.json'),JSON.stringify({checkedAt:new Date().toISOString(),scope:'anonymous live Instapass carts only; no order/payment/email; cohost check separately classified',pass_count:checks.length,checks,cohost_health:cohost},null,2));
console.log('Instapass primary checks passed: '+checks.length);if(!cohost.verified){console.log('SEPARATE COHOST HEALTH UNVERIFIED: '+cohost.error);process.exitCode=1;}
}finally{await browser.close();}})().catch(e=>{console.error(e.stack);process.exit(1)});