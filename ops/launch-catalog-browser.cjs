const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs'),path=require('path'),assert=require('assert/strict');
const base=process.env.IPL_BASE||'https://instapass.online';
const out=path.resolve(__dirname,'../outputs');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 const context=await browser.newContext({viewport:{width:1440,height:1000}});const page=await context.newPage();page.on('response',r=>{if(r.url().includes('instapass-launch-catalog'))console.log('ASSET '+r.status()+' '+r.url());});
 let response=await page.goto(base+'/shop/',{waitUntil:'domcontentloaded'});assert.equal(response.status(),200);
 const close=page.getByRole('button',{name:'Dismiss this notice'});if(await close.isVisible())await close.click();
 if(process.env.IPL_INSPECT_ONLY){await page.screenshot({path:path.join(out,'launch-catalog-before-desktop.png'),fullPage:true});console.log(JSON.stringify({title:await page.title(),tiles:await page.locator('.wc-block-product').count(),grouped:await page.locator('.ipl-grid .ipl-card').count()}));await browser.close();return;}
 const expected=['Claude Pro','Claude Max 5×','Claude Ultra 20×','ChatGPT Plus','Canva Pro'];let checks=0;
 const check=(value,label)=>{assert.ok(value,label);checks++;console.log('PASS '+label);};
 await page.screenshot({path:path.join(out,'launch-catalog-preview-desktop.png'),fullPage:true});
 console.log('GROUPED_ASSETS '+JSON.stringify(await page.locator('link[href*=instapass-launch],script[src*=instapass-launch]').evaluateAll(ns=>ns.map(n=>n.href||n.src))));
 check(await page.locator('.ipl-grid').evaluate(el=>getComputedStyle(el).display==='grid'),'Grouped tile stylesheet loaded');
 check(await page.locator('.ipl-grid .ipl-card').count()===7,'Exactly seven product tiles');
 for(const name of expected){const tile=page.locator('.ipl-grid .ipl-card').filter({has:page.getByRole('heading',{name,exact:true})});check(await tile.count()===1,'One tile: '+name);check(await tile.locator('img').evaluate(img=>img.complete&&img.naturalWidth>0),'Logo: '+name);const values=name==='Canva Pro'?['12']:['1','6','12'];check(JSON.stringify(await tile.locator('input[type=radio]').evaluateAll(ns=>ns.map(n=>n.value)))===JSON.stringify(values),'Duration options: '+name);for(const m of values){await tile.locator('input[value="'+m+'"]').check();check(await tile.locator('.ipl-quote:not([hidden])').getAttribute('data-months')===m,'Selected panel '+name+' '+m);check((await tile.locator('.ipl-quote:not([hidden])').innerText()).includes('Pricing coming soon'),'Pricing hold '+name+' '+m);check(await tile.locator('.ipl-quote:not([hidden]) button').isDisabled(),'Purchase disabled '+name+' '+m);}}
 let creditDetail;
 for(const name of ['Claude Credits','ChatGPT Credits']){
  const tile=page.locator('.ipl-grid .ipl-card').filter({has:page.getByRole('heading',{name,exact:true})});check(await tile.count()===1,'One credit tile: '+name);check(await tile.locator('img').evaluate(img=>img.complete&&img.naturalWidth>0),'Credit logo '+name);
  const range=tile.locator('input[type=range]');check(await range.getAttribute('min')==='50'&&await range.getAttribute('max')==='500'&&await range.getAttribute('step')==='25','Credit range and step '+name);check(await tile.locator('.ipl-credit-expiry').innerText()==='Credits expire monthly','Monthly credit expiry '+name);
  await range.focus();await range.press('Home');
  for(let amount=50;amount<=500;amount+=25){if(amount>50)await range.press('ArrowRight');check(await range.inputValue()===String(amount),'Credit keyboard value '+name+' '+amount);check(await tile.locator('.ipl-credit-value').innerText()==='$'+amount+' credits','Credit balance display '+name+' '+amount);check(await tile.locator('.ipl-quote:not([hidden])').getAttribute('data-months')===String(amount),'Correct credit offer '+name+' '+amount);}
  check(await tile.locator('[data-credit-adjust="25"]').isDisabled(),'Maximum enforced '+name);await tile.locator('[data-credit-adjust="-25"]').click();check(await range.inputValue()==='475','Minus step '+name);await range.press('Home');check(await tile.locator('[data-credit-adjust="-25"]').isDisabled(),'Minimum enforced '+name);await tile.locator('[data-credit-adjust="25"]').click();check(await range.inputValue()==='75','Plus step '+name);
  await range.press('ArrowRight');creditDetail=await tile.locator('.ipl-quote:not([hidden]) .ipl-details').getAttribute('href');check((await tile.locator('.ipl-quote:not([hidden])').innerText()).includes('Pricing coming soon'),'Credit selling price held '+name);await range.press('Home');
 }
 for(const tile of await page.locator('.ipl-grid .ipl-card').all()){const monthly=tile.locator('input[value="1"]');if(await monthly.count())await monthly.check();}
 check(await page.locator('main').getByText('No products here yet.',{exact:false}).count()===0,'No contradictory empty catalog message');
 check(await page.locator('a[href*="add-to-cart="]').count()===0,'No old buy links');check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Desktop width');
 await page.locator('.ipl-grid').screenshot({path:path.join(out,'launch-catalog-tiles.png')});
 await page.screenshot({path:path.join(out,'launch-catalog-desktop.png'),fullPage:true});
 await page.setViewportSize({width:390,height:844});check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Phone width');await page.screenshot({path:path.join(out,'launch-catalog-phone.png'),fullPage:true});
 await page.goto(base+'/',{waitUntil:'domcontentloaded'});check(await page.locator('.ipl-grid .ipl-card').count()===7,'Homepage also shows seven grouped tiles');
 const detail=await page.locator('.ipl-card').first().locator('.ipl-visual').getAttribute('href');await page.goto(detail,{waitUntil:'domcontentloaded'});check(await page.locator('.ipl-card--detail input[type=radio]').count()===3,'Product detail has durations');check((await page.locator('main').innerText()).includes('Pricing coming soon'),'Product detail holds pricing');
 await page.goto(creditDetail,{waitUntil:'domcontentloaded'});check(await page.locator('.ipl-card--detail input[type=range]').inputValue()==='100','Selected credit amount carries to details');check((await page.locator('.ipl-card--detail').innerText()).includes('Credits expire monthly'),'Credit detail expiry visible');
 const result={result:'pass',checks,base,no_order_submitted:true};fs.writeFileSync(path.join(out,'launch-catalog-browser.json'),JSON.stringify(result,null,2));console.log(JSON.stringify(result));await browser.close();
})().catch(e=>{console.error(e.stack);process.exit(1);});
