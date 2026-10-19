const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs'),path=require('path'),assert=require('assert/strict'),root=path.resolve(__dirname,'..');
const live=process.argv.includes('--live'),base=live?'https://instapass.online':'http://127.0.0.1:19052';
const fixture=live?null:JSON.parse(fs.readFileSync(path.join(root,'.qa/bundle-private.json'),'utf8'));
(async()=>{
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
try{
const ctx=await browser.newContext({viewport:{width:390,height:844}}),p=await ctx.newPage(),checks=[],errors=[];
if(!live){await ctx.route('**/*',r=>new URL(r.request().url()).hostname==='127.0.0.1'?r.continue():r.abort());}
p.on('pageerror',e=>errors.push(e.message));
const check=(n,v)=>{assert.ok(v,n);checks.push(n);console.log('PASS '+n);};
const add=async id=>p.goto(base+'/checkout/?add-to-cart='+id+'&ip_buy_now=1',{waitUntil:'networkidle'});
const coupon=async code=>{if(!await p.locator('#coupon_code').isVisible()){await p.locator('.showcoupon').click();}await p.locator('#coupon_code').fill(code);await p.locator('button[name=apply_coupon]').click();await p.waitForTimeout(2000);};
const discount=async()=>p.locator('tr.cart-discount').allTextContents();
await p.goto(base+'/shop/?ip_currency=USD',{waitUntil:'domcontentloaded'});
const ids=live?[29,30,31,32]:fixture.products;
if(live){await add(ids[0]);await add(ids[1]);}else{
await p.goto(base+'/?post_type=product&p='+ids[0],{waitUntil:'networkidle'});
check('other tenors visible on product page',await p.locator('.ip-tenor-offers li').count()===6);
check('only priced in-stock tenors have add buttons',await p.locator('.ip-tenor-action a.button').count()===4);
check('draft and hidden tenor listings stay private',!(await p.locator('.ip-tenor-offers').innerText()).includes('48 months')&&!(await p.locator('.ip-tenor-offers').innerText()).includes('1 year'));
check('duration choices fit phone',await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));
await p.screenshot({path:path.join(root,'outputs/tenor-offers-isolated-390.png'),fullPage:true});
await p.locator('.ip-tenor-offers a.button').getByText('Add 1 month',{exact:true}).click();await p.waitForURL(/checkout/);await p.waitForLoadState('networkidle');
await p.goto(base+'/?post_type=product&p='+ids[0],{waitUntil:'networkidle'});await p.locator('.ip-tenor-offers a.button').getByText('Add 3 months',{exact:true}).click();await p.waitForURL(/checkout/);await p.waitForLoadState('networkidle');
check('two tenors add separately without replacing basket',await p.locator('.woocommerce-checkout-review-order-table .cart_item').count()===2&&(await p.locator('#order_review').innerText()).includes('1 month')&&(await p.locator('#order_review').innerText()).includes('3 months'));
}
check('both offers advertised',(await p.locator('.ip-promo-note').allTextContents()).join(' ').includes('TOOLKIT5')&&(await p.locator('.ip-promo-note').allTextContents()).join(' ').includes('TOOLKIT10'));
check('checkout remains optional account',await p.locator('#createaccount').count()===0);
await coupon('TOOLKIT5');
check('two products receive 5%',(await discount()).join('').includes(live?'1.20':'1.10'));
await coupon('TOOLKIT10');
check('second code does not stack',(await discount()).length===1&&(await discount()).join('').toLowerCase().includes('toolkit5'));
await p.locator('.woocommerce-remove-coupon').click();await p.locator('tr.cart-discount').waitFor({state:'detached'});
await add(ids[2]);await coupon('TOOLKIT10');
check('three products reject four product code',(await discount()).length===0&&(await p.locator('body').innerText()).includes('4 different product/tenor offers'));
await add(ids[3]);await coupon('TOOLKIT10');
check('four products receive 10%',(await discount()).length===1&&(await discount()).join('').includes(live?'24.90':'4.40'));
check('four product total correct',(await p.locator('tr.order-total').innerText()).includes(live?'224.06':'39.56'));
check('checkout fits phone',await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));
await p.screenshot({path:path.join(root,'outputs/bundle-offers-'+(live?'live':'isolated')+'-390.png'),fullPage:true});
if(!live){
const ap=await ctx.newPage();await ap.goto(base+'/wp-login.php');await ap.locator('#user_login').fill(fixture.username);await ap.locator('#user_pass').fill(fixture.password);await Promise.all([ap.waitForNavigation(),ap.locator('#wp-submit').click()]);
const adminUrl=base+'/wp-admin/edit.php?post_type=product&page=instapass-promotions';
await ap.goto(adminUrl,{waitUntil:'domcontentloaded'});
check('two independently editable owner forms',await ap.locator('input[name=offer_slot]').count()===2&&(await ap.locator('#ip-promo-0-code').inputValue()).toLowerCase()==='toolkit5'&&(await ap.locator('#ip-promo-1-code').inputValue()).toLowerCase()==='toolkit10');
const second=ap.locator('form').filter({has:ap.locator('input[name=offer_slot][value="1"]')});
await second.locator('input[name=enabled]').uncheck();await Promise.all([ap.waitForNavigation(),second.getByRole('button',{name:'Save bundle offer 2',exact:true}).click()]);
check('owner can disable second without changing first',!await second.locator('input[name=enabled]').isChecked()&&await ap.locator('#ip-promo-0-percent').inputValue()==='5');
await p.reload({waitUntil:'networkidle'});
check('disabled offer removed from advertising',(await p.locator('.ip-promo-note').allTextContents()).join('').includes('TOOLKIT10')===false);
await second.locator('input[name=enabled]').check();await Promise.all([ap.waitForNavigation(),second.getByRole('button',{name:'Save bundle offer 2',exact:true}).click()]);
check('owner re-enables second offer',await second.locator('input[name=enabled]').isChecked()&&await ap.locator('#ip-promo-1-percent').inputValue()==='10');
await ap.goto(base+'/wp-admin/admin.php?page=instapass-guest-orders',{waitUntil:'domcontentloaded'});
check('first-order controls removed',!(await ap.locator('body').innerText()).includes('First-order promo codes'));
await ap.goto(base+'/wp-admin/post.php?post='+ids[0]+'&action=edit',{waitUntil:'domcontentloaded'});
await ap.locator('#_instapass_tenor_family').fill('Isolated TENOR product');
await Promise.all([ap.waitForNavigation({waitUntil:'domcontentloaded'}),ap.locator('#publish').click()]);
check('owner can save linked tenor family in native editor',await ap.locator('#_instapass_tenor_family').inputValue()==='Isolated TENOR product');
await p.goto(base+'/?post_type=product&p='+ids[0],{waitUntil:'networkidle'});
check('saved family keeps customer duration choices linked',await p.locator('.ip-tenor-offers li').count()===6);
await ap.goto(base+'/wp-admin/edit.php?post_type=product&page=instapass-tenor-offers',{waitUntil:'domcontentloaded'});
check('owner tenor guide is available',(await ap.locator('h1').innerText())==='Subscription tenors');
}else{
check('storefront healthy',(await ctx.request.get(base+'/')).status()===200);
check('Wasooli healthy',(await ctx.request.get('https://wasooli.duckdns.org/')).status()===200);
}
check('no browser execution errors',errors.length===0);
fs.writeFileSync(path.join(root,'outputs/bundle-offers-'+(live?'live':'isolated')+'-verification.json'),JSON.stringify({checkedAt:new Date().toISOString(),scope:live?'anonymous live carts only; no order/payment submitted':'isolated clone with intercepted mail',pass_count:checks.length,checks},null,2));
}finally{await browser.close();}
})().catch(e=>{console.error(e.stack);process.exit(1);});