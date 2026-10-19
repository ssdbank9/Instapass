const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs'),path=require('path'),assert=require('assert/strict'),root=path.resolve(__dirname,'..');
(async()=>{const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
try{const p=await browser.newPage(),checks=[];for(const width of [1440,390]){
await p.setViewportSize({width,height:1000});const r=await p.goto('https://instapass.online/shop/?ip_currency=USD',{waitUntil:'networkidle'});assert.equal(r.status(),200);
for(const [name,wanted] of [[/^Claude Pro$/,true],[/^Claude Max 5[x×]$/,true],[/^Claude Max 20[x×]$/,false]]){
 const link=p.getByRole('link',{name}).first();await link.waitFor();const card=link.locator('xpath=ancestor::li[1]');const text=await card.innerText();assert.equal(text.includes('25% additional limits'),wanted,name+' at '+width);checks.push(String(name)+' copy at '+width);
 if(wanted&&width===390){await card.screenshot({path:path.join(root,'outputs/claude-tile-'+(name.source.includes('Pro')?'pro':'max5')+'-390.png')});}
}
assert.ok(await p.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));checks.push('page fits '+width);
}fs.writeFileSync(path.join(root,'outputs/claude-tile-copy-verification.json'),JSON.stringify({checkedAt:new Date().toISOString(),scope:'public live rendered shop; no checkout or customer action',checks},null,2));console.log('PASS '+checks.length+' live tile/width checks');
}finally{await browser.close();}})().catch(e=>{console.error(e.stack);process.exit(1);});