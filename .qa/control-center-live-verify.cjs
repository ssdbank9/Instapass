const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs'),path=require('path'),assert=require('assert/strict');
(async()=>{const b=await chromium.connectOverCDP('http://127.0.0.1:9227');const c=await b.newContext();const page=await c.newPage();const results=[];
 for(const [url,expected] of [['https://instapass.online/','Instapass'],['https://wasooli.duckdns.org/','Wasooli']]){const r=await page.goto(url,{waitUntil:'domcontentloaded'});assert.equal(r.status(),200);assert.ok((await page.title()).includes(expected));results.push({url,status:r.status(),title:await page.title()});}
 const admin='https://instapass.online/wp-admin/admin.php?page=instapass-control';await page.goto(admin,{waitUntil:'domcontentloaded'});assert.ok(new URL(page.url()).pathname.endsWith('/wp-login.php'));results.push({url:admin,anonymous_redirect:'wp-login.php'});
 const css=await c.request.get('https://instapass.online/wp-content/plugins/instapass-control-center/assets/admin.css');assert.equal(css.status(),200);assert.ok((await css.text()).includes('.ipc-hero'));results.push({asset:'control-center CSS',status:css.status()});
 fs.writeFileSync(path.resolve(__dirname,'../migration-transfer-20261018/control-center-live-verification.json'),JSON.stringify({time:new Date().toISOString(),results,authenticated_admin_browser:'not verified; no signed-in WordPress tab'},null,2));console.log(JSON.stringify(results));await c.close();process.exit(0);
})().catch(e=>{console.error(e.message);process.exit(1)});
