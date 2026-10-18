const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs'),path=require('path'),assert=require('assert/strict');
const root=path.resolve(__dirname,'..');
(async()=>{
 const browser=await chromium.connectOverCDP('http://127.0.0.1:9227');
 const ctx=browser.contexts()[0];const page=await ctx.newPage();const result={};
 try{
 const response=await page.goto('https://instapass.online/wp-admin/admin.php?page=instapass-email-alerts',{waitUntil:'domcontentloaded'});
 result.owner_http=response.status();
 result.owner_heading=await page.getByRole('heading',{name:'Email & Stock Alerts',exact:true}).count()===1;
 const text=await page.locator('body').innerText();result.paused=text.includes('Paused / inbox confirmation required');
 result.test_control=await page.getByRole('button',{name:'Send test to site administrator',exact:true}).count()===1;
 result.budget=await page.locator('input[name=daily_limit]').inputValue();
 assert.ok(result.owner_heading&&result.paused&&result.test_control&&result.budget==='100');
 await page.setViewportSize({width:1440,height:1000});
 await page.screenshot({path:path.join(root,'outputs','restock-live-owner.png'),fullPage:true});
 const home=await page.goto('https://instapass.online/',{waitUntil:'domcontentloaded'});result.home_http=home.status();
 result.telegram_links=await page.locator('footer a[href*="t.me"]').evaluateAll(xs=>xs.map(x=>({text:x.textContent,href:x.href})));
 assert.ok(result.telegram_links.length && result.telegram_links.every(x=>x.href==='https://t.me/Instapass0408'&&x.text.includes('@Instapass0408')));
 await page.goto('https://instapass.online/contact/',{waitUntil:'domcontentloaded'});
 result.contact_links=await page.locator('a[href*="t.me/"]').evaluateAll(xs=>xs.map(x=>x.href));
 assert.ok(result.contact_links.length && result.contact_links.every(x=>x==='https://t.me/Instapass0408'));
 fs.writeFileSync(path.join(root,'outputs','restock-live-browser.json'),JSON.stringify(result,null,2));
 console.log(JSON.stringify(result));
 }finally{await page.close();await browser.close();}
})().catch(e=>{console.error(e.message);process.exit(1)});
