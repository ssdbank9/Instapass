const{chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright'),fs=require('fs'),path=require('path'),assert=require('assert/strict'),root=path.resolve(__dirname,'..');
(async()=>{const b=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'}),checks=[];const check=(n,v)=>{assert.ok(v,n);checks.push(n);console.log('PASS '+n)};
try{const p=await b.newPage();await p.goto('https://instapass.online/shop/?ip_currency=USD',{waitUntil:'domcontentloaded'});
for(const width of[390,1440]){await p.setViewportSize({width,height:1000});await p.goto('https://instapass.online/shop/',{waitUntil:'networkidle'});
for(const [slug,wanted]of[['claude-pro',true],['claude-max-5x',true],['claude-max-20x',false]]){
const card=p.locator('li').filter({has:p.locator('a[href="https://instapass.online/product/'+slug+'/"]')}).filter({has:p.locator('.ip-tool-brief')}).last();
const brief=card.locator('.ip-tool-brief').first();await brief.waitFor();check(slug+' copy scope '+width,(await brief.innerText()).startsWith('25% additional limits')===wanted);
if(wanted){check(slug+' first phrase unclipped '+width,await brief.evaluate(el=>{const r=document.createRange();r.setStart(el.firstChild,0);r.setEnd(el.firstChild,'25% additional limits'.length);return r.getBoundingClientRect().bottom<=el.getBoundingClientRect().bottom+1}));if(width===390)await card.screenshot({path:path.join(root,'outputs/approved-'+slug+'-tile-390.png')});}
}
check('shop fits '+width,await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));}
for(const[slug,usd]of[['chatgpt-plus-1-year','173.33'],['claude-max-5x-6-months','432'],['claude-max-5x-1-year','840']]){
await p.goto('https://instapass.online/product/'+slug+'/',{waitUntil:'networkidle'});const text=await p.locator('#tab-description').innerText();check(slug+' confirmed upfront copy',text.includes(usd)&&text.includes('paid upfront')&&!text.includes('7-day')&&!text.includes('60 seconds'));check(slug+' refund policy link',await p.locator('#tab-description a[href="https://instapass.online/refund-policy/"]').count()===1);
if(slug==='claude-max-5x-1-year'){await p.setViewportSize({width:390,height:1000});await p.screenshot({path:path.join(root,'outputs/approved-annual-max-final-390.png'),fullPage:true});}}
fs.writeFileSync(path.join(root,'outputs/approved-offer-copy-verification.json'),JSON.stringify({at:new Date().toISOString(),count:checks.length,checks,scope:'public rendered copy and visible first-phrase checks; no customer action'},null,2));console.log('COPY CHECKS PASSED '+checks.length);
}finally{await b.close()}})().catch(e=>{console.error(e.stack);process.exit(1)});