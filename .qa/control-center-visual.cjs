const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs'),path=require('path'),assert=require('assert/strict');
const root=path.resolve(__dirname,'..');
(async()=>{
 const b=await chromium.connectOverCDP('http://127.0.0.1:9227');const c=await b.newContext();const page=await c.newPage();
 let html=fs.readFileSync(path.join(root,'migration-transfer-20261018/control-center-preview.html'),'utf8').replace(/(<input[^>]*name="_wpnonce"[^>]*value=")[^"]*/g,'$1REMOVED');
 const css=fs.readFileSync(path.join(root,'plugins/instapass-control-center/assets/admin.css'),'utf8');
 await page.setContent('<!doctype html><html lang="en"><head><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="https://instapass.online/wp-admin/css/common.min.css"><link rel="stylesheet" href="https://instapass.online/wp-admin/css/forms.min.css"><link rel="stylesheet" href="https://instapass.online/wp-includes/css/buttons.min.css"><style>body{margin:0;padding:16px;background:#f0f0f1;font-family:Arial,sans-serif}.wrap{margin:0 auto}'+css+'</style></head><body>'+html+'</body></html>',{waitUntil:'networkidle'});
 const checks=[];
 for(const width of [1440,390]){
  await page.setViewportSize({width,height:1000});await page.screenshot({path:path.join(root,`migration-transfer-20261018/control-center-preview-${width}.png`),fullPage:true});
  const size=await page.evaluate(()=>({view:innerWidth,scroll:document.documentElement.scrollWidth}));assert.ok(size.scroll<=size.view,JSON.stringify(size));
  assert.ok((await page.locator('.ipc-result').innerText()).includes('Rs 4,200'));checks.push({width,no_page_overflow:true,preview_total:4200});
 }
 fs.writeFileSync(path.join(root,'migration-transfer-20261018/control-center-visual.json'),JSON.stringify({source:'Actual WordPress PHP render in local browser preview; not an authenticated wp-admin browser session',checks},null,2));
 console.log(JSON.stringify(checks));await c.close();process.exit(0);
})().catch(e=>{console.error(e.message);process.exit(1)});
