const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs'),path=require('path');
const root=path.resolve(__dirname,'..'),outDir=path.join(root,'outputs/site-review-20261108');
fs.mkdirSync(outDir,{recursive:true});
const base='https://instapass.online';
const routes=['/','/shop/','/faq/','/refund-policy/','/contact/','/track-order/','/cart/','/checkout/'];
const report={captured:new Date().toISOString(),pages:[],console:{},screenshots:[]};
(async()=>{
  const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
  for(const vp of [{name:'desktop',width:1440,height:1000},{name:'mobile',width:390,height:844}]){
    const ctx=await browser.newContext({viewport:vp,isMobile:vp.name==='mobile',hasTouch:vp.name==='mobile'});
    const page=await ctx.newPage();
    const consoleKey=vp.name;report.console[consoleKey]=[];
    page.on('console',m=>{if(['error','warning'].includes(m.type()))report.console[consoleKey].push(m.type()+': '+m.text().slice(0,200));});
    page.on('pageerror',e=>report.console[consoleKey].push('pageerror: '+String(e).slice(0,200)));
    for(const route of routes){
      const entry={route,viewport:vp.name};
      try{
        const r=await page.goto(base+route,{waitUntil:'domcontentloaded',timeout:45000});
        await page.waitForTimeout(1200);
        entry.status=r.status();
        entry.title=await page.title();
        const overflow=await page.evaluate(()=>document.documentElement.scrollWidth>document.documentElement.clientWidth+1);
        entry.horizontal_overflow=overflow;
        if(route==='/shop/'){
          const dismiss=page.getByRole('button',{name:'Dismiss this notice'});
          if(await dismiss.isVisible().catch(()=>false))await dismiss.click().catch(()=>{});
          entry.tiles=[];
          const cards=page.locator('.ipl-card');
          const n=await cards.count();
          for(let i=0;i<Math.min(n,12);i++){const c=cards.nth(i);entry.tiles.push({family:await c.getAttribute('data-launch-family'),text:(await c.innerText()).replace(/\s+/g,' ').slice(0,320)});}
          entry.currency_bar=(await page.locator('.ip-currency-bar').innerText().catch(()=>'')).replace(/\s+/g,' ');
        }
        if(route==='/'){
          const dismiss=page.getByRole('button',{name:'Dismiss this notice'});
          if(await dismiss.isVisible().catch(()=>false))await dismiss.click().catch(()=>{});
          entry.heading=(await page.locator('h1').first().innerText().catch(()=>''));
          const links=await page.locator('footer a').allInnerTexts().catch(()=>[]);
          entry.footer_links=links.slice(0,25);
        }
        if(route==='/checkout/'){
          const dismiss=page.getByRole('button',{name:'Dismiss this notice'});
          if(await dismiss.isVisible().catch(()=>false))await dismiss.click().catch(()=>{});
          const mainText=await page.locator('main').innerText().catch(()=>page.locator('body').innerText().then(t=>t));
          entry.body_excerpt=mainText.replace(/\s+/g,' ').slice(0,600);
        }
        const shot=path.join(outDir,vp.name+'-'+route.replace(/\W+/g,'-')+'.png');
        await page.screenshot({path:shot,fullPage:false}).catch(()=>{});
        report.screenshots.push(shot);
      }catch(e){entry.error=String(e).slice(0,300);}
      report.pages.push(entry);
    }
    // currency switch probe on shop (desktop only)
    if(vp.name==='desktop'){
      try{
        await page.goto(base+'/shop/',{waitUntil:'domcontentloaded'});
        const dismiss=page.getByRole('button',{name:'Dismiss this notice'});
        if(await dismiss.isVisible().catch(()=>false))await dismiss.click().catch(()=>{});
        const bar=await page.locator('.ip-currency-bar').innerText().catch(()=>null);
        const links=await page.locator('.ip-currency-bar a, .ip-currency-bar button, .ip-currency-bar select').all().catch(()=>[]);
        report.currency_probe={bar:(bar||'').replace(/\s+/g,' '),controls:[]};
        for(const l of links.slice(0,8)){const t=(await l.innerText().catch(()=>'' )).trim();if(t)report.currency_probe.controls.push(t);}
      }catch(e){report.currency_probe={error:String(e).slice(0,200)};}
    }
    await ctx.close();
  }
  await browser.close();
  fs.writeFileSync(path.join(outDir,'report.json'),JSON.stringify(report,null,2));
  console.log(JSON.stringify(report,null,1).slice(0,6000));
})().catch(e=>{console.error(e.stack);process.exit(1);});
