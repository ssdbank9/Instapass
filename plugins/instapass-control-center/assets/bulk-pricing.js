(function(){
'use strict';
const $=id=>document.getElementById(id);
const box=$('ipcb-status'), catalogBody=$('ipcb-catalog'), previewBox=$('ipcb-preview'), changesBody=$('ipcb-changes');
let products=[],preview=null,busy=false;
function say(s){box.textContent=s}
function cell(tr,s){const td=document.createElement('td');td.textContent=s;tr.append(td);return td}
function invalidate(){preview=null;previewBox.hidden=true;say('Preview cleared. Create a new preview before applying.')}
async function api(route,method='GET',body){
 const opt={method,credentials:'same-origin',headers:{'X-WP-Nonce':InstapassBulk.nonce}};
 if(body){opt.headers['Content-Type']='application/json';opt.body=JSON.stringify(body)}
 const r=await fetch(InstapassBulk.url+route,opt);const j=await r.json();
 if(!r.ok)throw new Error(j.message||'Request failed; no success was confirmed.');
 return j
}
async function run(fn){
 if(busy)return;busy=true;document.querySelectorAll('.ipcb button,.ipcb input,.ipcb select').forEach(x=>x.disabled=true);
 try{await fn()}catch(e){invalidate();say(e.message)}
 finally{busy=false;document.querySelectorAll('.ipcb button,.ipcb input,.ipcb select').forEach(x=>x.disabled=false)}
}
function renderCatalog(){
 catalogBody.replaceChildren();
 products.forEach(p=>{
  const tr=document.createElement('tr');tr.dataset.search=(p.name+' '+p.sku).toLowerCase();
  const cb=document.createElement('input');cb.type='checkbox';cb.dataset.id=p.id;cb.setAttribute('aria-label','Select '+p.name);cb.addEventListener('change',invalidate);
  cell(tr,'').append(cb);const name=cell(tr,p.name+' / '+(p.sku||'No SKU'));if(p.edit_url){const edit=document.createElement('a');edit.href=p.edit_url;edit.textContent='Edit prices';name.append(document.createElement('br'),edit)}cell(tr,p.usd.regular||'Unpriced');cell(tr,p.usd.sale||'—');cell(tr,p.pkr.regular||'Unpriced');cell(tr,p.pkr.sale||'—');cell(tr,p.pkr_mode);cell(tr,p.gbp.regular||'Unpriced');cell(tr,p.gbp.sale||'—');cell(tr,p.eur.regular||'Unpriced');cell(tr,p.eur.sale||'—');cell(tr,p.stock);catalogBody.append(tr)
 });
 filter()
}
function priceText(c,phase){const v=c['display_'+phase]||c[phase];const mode=c['pkr_mode_'+phase];return(v.regular||'—')+' / '+(v.sale||'No sale')+(mode?' ('+mode+' PKR)':'')}
function filter(){const q=$('ipcb-search').value.trim().toLowerCase();catalogBody.querySelectorAll('tr').forEach(r=>r.hidden=!r.dataset.search.includes(q))}
function selected(){return [...catalogBody.querySelectorAll('input:checked')].map(x=>Number(x.dataset.id))}
function req(mode){return{mode,currency:$('ipcb-currency').value,discount:$('ipcb-discount').value,round99:$('ipcb-round99').checked,ids:selected()}}
async function loadCatalog(){const d=await api('catalog');products=d.rows;renderCatalog()}
async function loadHistory(){
 const list=await api('history'),host=$('ipcb-history');host.replaceChildren();const h=document.createElement('h2');h.textContent='Recent changes';host.append(h);
 if(!list.length){const p=document.createElement('p');p.textContent='No bulk price changes recorded.';host.append(p)}
 list.forEach(op=>{const card=document.createElement('article');const p=document.createElement('p');p.textContent=new Date(op.created*1000).toLocaleString()+' · '+op.state+' · '+op.saved+' saved'+(op.errors.length?' · '+op.errors.join(' '):'')+(op.undo_errors?.length?' · Undo: '+op.undo_errors.join(' '):'');card.append(p);
  if(Array.isArray(op.changes)&&op.changes.length){const details=document.createElement('details');const summary=document.createElement('summary');summary.textContent='View '+op.changes.length+' proposed price rows';details.append(summary);op.changes.forEach(c=>{const row=document.createElement('p');row.textContent=c.name+' / '+(c.sku||'No SKU')+' / '+c.currency+': '+priceText(c,'before')+' → '+priceText(c,'after');details.append(row)});card.append(details)}
  if(['applied','partial'].includes(op.state)&&op.saved){const b=document.createElement('button');b.type='button';b.className='button';b.textContent='Undo batch';b.addEventListener('click',()=>run(async()=>{if(!window.confirm('Restore the prices saved by this batch? Newer edits will prevent undo.'))return;const r=await api('undo','POST',{token:op.token});say(r.state+': '+r.restored+' prices restored.');await loadCatalog();await loadHistory()}));card.append(' ',b)}
  host.append(card)
 })
}
async function makePreview(body){
 invalidate();say('Preparing exact price preview…');preview=await api('preview','POST',body);changesBody.replaceChildren();
 preview.changes.forEach(c=>{const tr=document.createElement('tr');cell(tr,c.name+' / '+(c.sku||'No SKU'));cell(tr,c.currency);cell(tr,priceText(c,'before'));cell(tr,priceText(c,'after'));const after=c.display_after||c.after;const pct=after.sale&&Number(after.regular)>0?((1-Number(after.sale)/Number(after.regular))*100):0;cell(tr,pct.toFixed(2)+'%');changesBody.append(tr)});
 $('ipcb-summary').textContent=preview.changes.length+' proposed changes. '+preview.skipped.length+' unpriced products skipped'+(preview.skipped.length?': '+preview.skipped.slice(0,20).join('; ')+(preview.skipped.length>20?'…':''):'')+'. GBP and EUR remain independent. Fixed PKR overrides stay unchanged by USD/rate edits; Auto PKR follows USD and the saved rate. Existing sale dates are preserved; scheduled prices may not start immediately. Preview expires in 15 minutes.';
 previewBox.hidden=false;say('Preview ready. Review each row, then choose Apply preview to save.')
}
function importRows(matrix){
 if(!Array.isArray(matrix)||matrix.length<2||matrix.length>501)throw new Error('Use 1 to 500 data rows.');
 const heads=matrix[0].map(x=>String(x??'').trim());
 if(new Set(heads).size!==heads.length||!heads.includes('SKU')||!heads.includes('Currency'))throw new Error('The Prices sheet needs unique SKU and Currency columns.');
 const map={'SKU':'sku','Currency':'currency','Regular price':'regular','Discount %':'discount','Sale price':'sale','PKR mode':'pkr_mode'},seen=new Set(),out=[];
 matrix.slice(1).forEach((cells,i)=>{const row={};heads.forEach((h,n)=>{if(map[h])row[map[h]]=String(cells[n]??'').trim()});
  if(!['regular','discount','sale'].some(k=>row[k])&&row.pkr_mode?.toLowerCase()!=='auto')return;
  if(!row.sku)throw new Error('Row '+(i+2)+': SKU is required.');
  if(!['USD','PKR','GBP','EUR'].includes((row.currency||'').toUpperCase()))throw new Error('Row '+(i+2)+': currency must be USD, PKR, GBP or EUR.');
  row.pkr_mode=(row.pkr_mode||'').toLowerCase();if(!['','auto','fixed'].includes(row.pkr_mode)||(row.currency.toUpperCase()!=='PKR'&&row.pkr_mode))throw new Error('Row '+(i+2)+': PKR mode auto/fixed is only valid on PKR rows.');if(row.pkr_mode==='auto'&&['regular','discount','sale'].some(k=>row[k]))throw new Error('Row '+(i+2)+': leave prices/discount blank for automatic PKR.');
  row.currency=row.currency.toUpperCase();const key=row.currency+':'+row.sku.toLowerCase();
  if(seen.has(key))throw new Error('Row '+(i+2)+': duplicate SKU and currency.');seen.add(key);
  ['regular','sale','discount'].forEach(k=>{if(row[k]!==undefined&&row[k]!==''&&(!/^\d+(?:\.\d+)?%?$/.test(row[k])))throw new Error('Row '+(i+2)+': '+k+' must be a number.');if(k==='discount'&&row[k]?.endsWith('%'))row[k]=row[k].slice(0,-1)});
  if(row.discount&&row.sale)throw new Error('Row '+(i+2)+': enter a discount or sale price, not both.');out.push(row)
 });
 if(!out.length)throw new Error('No price rows found.');return out
}
function workbookRows(book){
 const sn=book.SheetNames.includes('Prices')?'Prices':book.SheetNames.length===1?book.SheetNames[0]:null;if(!sn)throw new Error('Use a Prices sheet or one-sheet workbook.');
 const sh=book.Sheets[sn];if(!sh['!ref'])throw new Error('The worksheet is empty.');
 const b=XLSX.utils.decode_range(sh['!ref']);if(b.s.r!==0||b.s.c!==0||b.e.r>500||b.e.c>30)throw new Error('Start at A1; use no more than 500 rows and 31 columns.');
 for(const [key,c] of Object.entries(sh))if(!key.startsWith('!')&&c&&c.f!==undefined)throw new Error('Formula in '+key+'. Paste values before uploading.');
 const m=XLSX.utils.sheet_to_json(sh,{header:1,defval:'',raw:true,blankrows:true});
 const heads=(m[0]||[]).map(String);heads.forEach((h,c)=>{if(!['Regular price','Discount %','Sale price'].includes(h))return;for(let r=1;r<m.length;r++){const cell=sh[XLSX.utils.encode_cell({r,c})];if(cell&&(cell.t==='d'||(cell.z&&XLSX.SSF.is_date(cell.z))))throw new Error('Row '+(r+1)+': '+h+' cannot be a date.');if(h==='Discount %'&&cell&&typeof cell.v==='number'&&cell.z&&/%/.test(cell.z))m[r][c]=Number((cell.v*100).toFixed(8))}});
 return importRows(m)
}
$('ipcb-global').addEventListener('click',()=>run(()=>makePreview(req('global'))));
$('ipcb-random-global').addEventListener('click',()=>run(()=>makePreview(req('random_global'))));
$('ipcb-selected').addEventListener('click',()=>run(()=>makePreview(req('selected'))));
$('ipcb-random-selected').addEventListener('click',()=>run(()=>makePreview(req('random_selected'))));
$('ipcb-pkr-auto').addEventListener('click',()=>run(()=>makePreview({...req('pkr_auto'),currency:'PKR'})));
$('ipcb-currency').addEventListener('change',invalidate);$('ipcb-discount').addEventListener('input',invalidate);$('ipcb-round99').addEventListener('change',invalidate);$('ipcb-search').addEventListener('input',filter);
$('ipcb-select-visible').addEventListener('click',()=>{catalogBody.querySelectorAll('tr:not([hidden]) input').forEach(x=>x.checked=true);invalidate()});
$('ipcb-clear').addEventListener('click',()=>{catalogBody.querySelectorAll('input').forEach(x=>x.checked=false);invalidate()});
$('ipcb-cancel').addEventListener('click',()=>{invalidate();say('Preview cancelled; nothing was changed.')});
$('ipcb-apply').addEventListener('click',()=>run(async()=>{if(!preview)throw new Error('Create a fresh preview first.');const token=preview.token;invalidate();say('Saving reviewed prices…');const d=await api('apply','POST',{token});say(d.state+': '+d.saved+' of '+d.requested+' rows saved. '+d.errors.join(' '));await loadCatalog();await loadHistory()}));
$('ipcb-file').addEventListener('change',()=>run(async()=>{const file=$('ipcb-file').files[0];$('ipcb-file').value='';if(!file)return;if(!/\.(xlsx|csv)$/i.test(file.name)||file.size>2*1024*1024)throw new Error('Choose an .xlsx or CSV file smaller than 2 MB.');const book=XLSX.read(await file.arrayBuffer(),{type:'array',cellFormula:true,cellNF:true});await makePreview({mode:'import',rows:workbookRows(book),round99:$('ipcb-round99').checked})}));
$('ipcb-template').addEventListener('click',()=>{const rows=[['SKU','Product','Currency','Current regular','Current sale','Current PKR source','Regular price','Discount %','Sale price','PKR mode'],...products.filter(p=>p.sku).flatMap(p=>[['USD',p.usd],['PKR',p.pkr],['GBP',p.gbp],['EUR',p.eur]].map(([currency,x])=>[p.sku,p.name,currency,x.regular,x.sale,currency==='PKR'?p.pkr_mode:'','','','','']))];const wb=XLSX.utils.book_new();XLSX.utils.book_append_sheet(wb,XLSX.utils.aoa_to_sheet(rows),'Prices');XLSX.utils.book_append_sheet(wb,XLSX.utils.aoa_to_sheet([['Instructions'],['Only edit Regular price, Discount % OR Sale price; PKR mode is optional.'],['USD accepts exact prices. PKR is automatic until you set a fixed price/discount. GBP and EUR are always independent.'],['Set PKR mode to auto with all price fields blank to restore conversion. Fixed or any PKR price/discount edit creates a fixed override.'],['Blank price rows preserve prices. Discount % 0 removes a sale.'],['Amounts cover the full term; monthly equivalents update automatically.'],['No formulas. Review the complete preview before Apply.']]),'Instructions');XLSX.writeFile(wb,'instapass-price-update.xlsx')});
run(async()=>{await loadCatalog();await loadHistory();say(products.length+' products loaded. Choose USD, PKR, GBP or EUR, preview changes, then apply only after review.')});
}());
