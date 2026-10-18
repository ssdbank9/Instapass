const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs'),path=require('path'),assert=require('assert/strict'),root=path.resolve(__dirname,'..'),base='http://127.0.0.1:19051';
const secret=JSON.parse(fs.readFileSync(path.join(root,'.qa/guest-commerce-private.json'),'utf8').replace(/^\uFEFF/,''));
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'}),ctx=await browser.newContext();await ctx.route('**/*',r=>new URL(r.request().url()).hostname==='127.0.0.1'?r.continue():r.abort());const p=await ctx.newPage(),checks=[];
 const check=(n,v)=>{assert.ok(v,n);checks.push(n);console.log('PASS '+n);};
 await p.goto(base+'/wp-login.php');await p.locator('#user_login').fill(secret.username);await p.locator('#user_pass').fill(secret.password);await Promise.all([p.waitForNavigation(),p.locator('#wp-submit').click()]);
 await p.goto(base+'/wp-admin/edit.php?post_type=shop_coupon');await p.locator('a.row-title').filter({hasText:/qa-first/i}).click();
 const panel=await p.locator('#_instapass_first_order_email').evaluate(e=>e.closest('.panel').id);await p.locator('a[href="#'+panel+'"]').click();
 check('owner can see first-order email control',await p.locator('#_instapass_first_order_email').isVisible());
 await p.locator('#_instapass_first_order_email').uncheck();await Promise.all([p.waitForNavigation(),p.locator('#publish').click()]);await p.locator('a[href="#'+panel+'"]').click();check('owner can disable first-order rule and persist',!await p.locator('#_instapass_first_order_email').isChecked());
 await p.locator('#_instapass_first_order_email').check();await Promise.all([p.waitForNavigation(),p.locator('#publish').click()]);await p.locator('a[href="#'+panel+'"]').click();check('owner can enable first-order rule and persist',await p.locator('#_instapass_first_order_email').isChecked());
 const limits=await p.locator('#usage_limit_per_user').inputValue();check('first-order coupon has native per-email use limit',limits==='1');
 fs.writeFileSync(path.join(root,'outputs/guest-commerce-coupon-editor-verification.json'),JSON.stringify({scope:'isolated fixture coupon edited through native WooCommerce UI',pass_count:checks.length,checks},null,2));await browser.close();
})().catch(e=>{console.error(e.stack);process.exit(1);});
