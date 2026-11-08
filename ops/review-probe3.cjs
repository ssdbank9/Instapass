const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
(async()=>{
  const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
  const ctx=await browser.newContext({viewport:{width:1440,height:1000}});
  const p=await ctx.newPage();
  await p.goto('https://instapass.online/shop/',{waitUntil:'networkidle'});
  await p.waitForTimeout(1000);
  const out={};
  out.vpn_visible=await p.locator('#ip-vpn-safety').isVisible();
  out.rect=await p.evaluate(()=>{const r=document.getElementById('ip-vpn-safety').getBoundingClientRect();return {w:r.width,h:r.height,top:r.top};});
  await p.screenshot({path:'C:/Users/Aly Jafferani/Documents/ChatGPT/Website creation/outputs/site-review-20261108/vpn-visible.png'});
  out.ov=await p.evaluate(()=>document.documentElement.scrollWidth>document.documentElement.clientWidth+1);
  console.log(JSON.stringify(out));
  await browser.close();
})().catch(e=>{console.error(e.stack);process.exit(1);});
