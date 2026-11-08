const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
(async()=>{
  const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
  const page=await browser.newContext({viewport:{width:1440,height:1000}}).then(c=>c.newPage());
  const failed=[];
  page.on('response',r=>{if(r.status()>=400)failed.push(r.status()+' '+r.url());});
  page.on('requestfailed',r=>failed.push('REQFAIL '+r.url()+' '+(r.failure()?.errorText||'')));
  for(const route of ['/','/shop/','/faq/','/contact/','/track-order/']){
    await page.goto('https://instapass.online'+route,{waitUntil:'networkidle',timeout:45000}).catch(e=>failed.push('NAV '+route+' '+e.message));
  }
  console.log(JSON.stringify([...new Set(failed)],null,1));
  await browser.close();
})().catch(e=>{console.error(e.stack);process.exit(1);});
