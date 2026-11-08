const {chromium}=require('C:/Users/Aly Jafferani/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
(async()=>{
  const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
  const ctx=await browser.newContext({viewport:{width:1440,height:1000}});
  const p=await ctx.newPage();
  await p.goto('https://instapass.online/shop/',{waitUntil:'networkidle'});
  const d=p.getByRole('button',{name:'Dismiss this notice'});if(await d.isVisible().catch(()=>false))await d.click().catch(()=>{});
  await p.waitForTimeout(2000);
  const out={};
  out.vpn_exists=await p.locator('#ip-vpn-safety').count();
  out.vpn_html=(await p.locator('#ip-vpn-safety').innerHTML().catch(()=>'')).slice(0,200);
  out.vpn_visible=await p.locator('#ip-vpn-safety').isVisible().catch(()=>false);
  out.vpn_style=await p.evaluate(()=>{const e=document.getElementById('ip-vpn-safety');return e?getComputedStyle(e).cssText.slice(0,200)+'| rect:'+JSON.stringify(e.getBoundingClientRect()):'absent';});
  await p.screenshot({path:'C:/Users/Aly Jafferani/Documents/ChatGPT/Website creation/outputs/site-review-20261108/vpn-probe.png'});
  // checkout payment methods
  await p.goto('https://instapass.online/checkout/?add-to-cart=31&quantity=2',{waitUntil:'networkidle'});
  await p.waitForTimeout(2000);
  out.pm_count=await p.locator('input[name^="payment_method"]').count();
  out.pm=await p.locator('input[name^="payment_method"]').evaluateAll(els=>els.map(e=>e.value)).catch(()=>[]);
  out.pm_labels=await p.locator('label[for^="payment_method_"]').evaluateAll(els=>els.map(e=>e.innerText.trim())).catch(()=>[]);
  out.total_text=await p.locator('.order-total').innerText().catch(()=>null);
  out.cart_items=await p.locator('.woocommerce-checkout-review-order-table .cart_item').count();
  console.log(JSON.stringify(out,null,1));
  await browser.close();
})().catch(e=>{console.error(e.stack);process.exit(1);});
