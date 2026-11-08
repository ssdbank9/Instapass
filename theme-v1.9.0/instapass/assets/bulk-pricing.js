(function () {
 'use strict';
 const headers = ['SKU', 'Regular price', 'Discount %', 'Sale price', 'Currency'];
 function importRows(matrix) {
  if (!Array.isArray(matrix) || matrix.length < 2 || matrix.length > 501) throw new Error('Use between 1 and 500 data rows.');
  const names = matrix[0].map(x => String(x ?? '').trim());
  if (new Set(names).size !== names.length) throw new Error('Duplicate column headings.');
  if (!names.includes('SKU')) throw new Error('The SKU column is required.');
  if (!names.some(x => ['Regular price', 'Discount %', 'Sale price'].includes(x))) throw new Error('Add a price or discount column.');
  const fields = {SKU:'sku', 'Regular price':'regular', 'Discount %':'discount', 'Sale price':'sale', Currency:'currency'};
  const seen = new Set(), result = [];
  matrix.slice(1).forEach((values, i) => {
   const row = {};
   names.forEach((name, n) => { if (fields[name]) row[fields[name]] = String(values[n] ?? '').trim(); });
   if (row.discount !== undefined && /^\d+(?:\.\d+)?%$/.test(row.discount)) row.discount=row.discount.slice(0,-1);
   if (!['regular','discount','sale'].some(k => row[k] !== undefined && row[k] !== '')) return;
   if (!row.sku) throw new Error(`Row ${i+2}: SKU is required.`);
   if (seen.has(row.sku.toLowerCase())) throw new Error(`Row ${i+2}: duplicate SKU ${row.sku}.`);
   seen.add(row.sku.toLowerCase());
   ['regular','discount','sale'].forEach(k => {
    if (row[k] !== undefined && row[k] !== '' && (!/^\d+(?:\.\d+)?$/.test(row[k]) || !Number.isFinite(Number(row[k])))) throw new Error(`Row ${i+2}: ${k} must be a plain non-negative number.`);
   });
   if (row.discount !== undefined && row.discount !== '' && Number(row.discount)>100) throw new Error(`Row ${i+2}: discount must be 0–100%.`);
   if (row.discount !== undefined && row.discount !== '' && row.sale !== undefined && row.sale !== '') throw new Error(`Row ${i+2}: enter a discount or a sale price, not both.`);
   result.push(row);
  });
  if (!result.length) throw new Error('No updated prices. Fill Regular price, Discount % or Sale price for the tools you want to change.');
  return result;
 }
 function workbookRows(book, XLSX) {
  const sheetName = book.SheetNames.includes('Prices') ? 'Prices' : book.SheetNames.length === 1 ? book.SheetNames[0] : null;
  if (!sheetName) throw new Error('Use a sheet named Prices, or a workbook with one sheet.');
  const sheet = book.Sheets[sheetName];
  if (!sheet['!ref']) throw new Error('The worksheet is empty.');
  const bounds = XLSX.utils.decode_range(sheet['!ref']);
  if(bounds.s.r!==0 || bounds.s.c!==0) throw new Error('Start the column headings in cell A1.');
  if (bounds.e.r > 500 || bounds.e.c > 30) throw new Error('Use at most 500 data rows and 31 columns.');
  Object.entries(sheet).forEach(([key, cell]) => { if (!key.startsWith('!') && cell && cell.f !== undefined) throw new Error(`Formula in ${key}. Paste values before uploading.`); });
  const matrix=XLSX.utils.sheet_to_json(sheet, {header:1, defval:'', raw:true, blankrows:true});
  const names=(matrix[0] || []).map(x=>String(x).trim());
  names.forEach((name,c)=>{
   if(!['Regular price','Discount %','Sale price'].includes(name)) return;
   for(let r=1;r<matrix.length;r++) {
    const item=sheet[XLSX.utils.encode_cell({r:bounds.s.r+r,c:bounds.s.c+c})];
    if(!item || item.v===undefined || item.v==='') continue;
    if(item.t==='d' || (item.z && XLSX.SSF.is_date(item.z))) throw new Error(`Row ${r+2}: ${name} must be a number, not a date.`);
    if(name==='Discount %' && typeof item.v==='number' && item.z && /%/.test(item.z)) matrix[r][c]=Number((item.v*100).toFixed(10));
   }
  });
  return importRows(matrix);
 }
 if (typeof module !== 'undefined' && module.exports) module.exports = {importRows, workbookRows, headers};
 if (typeof document === 'undefined') return;
 const panel = document.getElementById('ip-bulk'); if (!panel) return;
 const byId = id => document.getElementById(id), status = byId('ip-bulk-status');
 let catalog = [], currency = '', preview = null, busy = false;
 function message(text) { status.textContent = text; }
 function invalidate() { preview = null; byId('ip-bulk-preview').hidden = true; }
 async function api(route, body) {
  const response = await fetch(InstapassBulk.url + route, {method:body ? 'POST':'GET', credentials:'same-origin', headers:{'X-WP-Nonce':InstapassBulk.nonce, 'Content-Type':'application/json'}, ...(body ? {body:JSON.stringify(body)}:{})});
  const result = await response.json(); if (!response.ok) throw new Error(result.message || 'The request failed. No success was confirmed.'); return result;
 }
 async function run(work) {
  if (busy) return; busy = true;
  panel.querySelectorAll('button,input').forEach(el => { el.disabled = true; });
  try { await work(); } catch (error) { invalidate(); message(error.message); }
  finally { busy = false; panel.querySelectorAll('button,input').forEach(el => { el.disabled = false; }); }
 }
 function cell(row, text) { const td=document.createElement('td'); td.textContent=text; row.append(td); return td; }
 function renderCatalog() {
  const body=byId('ip-bulk-catalog'); body.replaceChildren();
  catalog.forEach(p => {
   const row=document.createElement('tr'); row.dataset.search=(p.name+' '+p.sku).toLowerCase();
   const box=document.createElement('input'); box.type='checkbox';box.dataset.product=p.id;box.setAttribute('aria-label','Select '+p.name);box.addEventListener('change',invalidate);
   cell(row,'').append(box); cell(row,p.name+' / '+(p.sku || 'No SKU')); cell(row,p.regular || 'Unpriced');cell(row,p.sale || '—');cell(row,p.status+' / '+p.stock); body.append(row);
  }); filter();
 }
 function filter() { const q=byId('ip-bulk-search').value.toLowerCase(); byId('ip-bulk-catalog').querySelectorAll('tr').forEach(row=>{row.hidden=!row.dataset.search.includes(q);}); }
 async function loadCatalog() { const d=await api('catalog');catalog=d.rows;currency=d.currency;renderCatalog(); }
 async function loadHistory() {
  const data=await api('history'), container=byId('ip-bulk-history');container.replaceChildren();const h=document.createElement('h3');h.textContent='Recent bulk changes';container.append(h);
  if (!data.length) { const p=document.createElement('p');p.textContent='No bulk changes yet.';container.append(p); }
  data.forEach(op => {
   const p=document.createElement('p');p.textContent=new Date(op.created*1000).toLocaleString()+' — '+op.state+' — '+op.saved+' saved. '+op.errors.join(' ');
   if (['applied','partial'].includes(op.state) && op.saved>0) {
    const button=document.createElement('button');button.type='button';button.className='button';button.textContent='Undo this batch';
    button.addEventListener('click',()=>{ if (!window.confirm('Restore this batch’s previous prices? Newer price changes will cause undo to be refused.')) return;run(async()=>{const d=await api('undo',{token:op.token});invalidate();message(d.state+': '+d.restored+' restored. '+d.errors.join(' '));await loadCatalog();await loadHistory();}); });p.append(' ',button);
   }container.append(p);
  });
 }
 function selections() { return [...byId('ip-bulk-catalog').querySelectorAll('input:checked')].map(x=>Number(x.dataset.product)); }
 async function showPreview(body) {
  invalidate();message('Validating prices…');const d=await api('preview',body);preview=d;
  const target=byId('ip-bulk-changes');target.replaceChildren();
  d.changes.forEach(c=>{const row=document.createElement('tr');cell(row,c.name+' / '+c.sku);cell(row,(c.before.regular || '—')+' / '+(c.before.sale || '—'));cell(row,c.after.regular+' / '+(c.after.sale || 'No sale'));cell(row,c.actual_discount.toFixed(2)+'%');target.append(row);});
  byId('ip-bulk-summary').textContent=d.changes.length+' tools in '+currency+'. Sales start immediately; existing sale dates will be cleared. Preview expires in 15 minutes. '+(d.skipped.length ? 'Skipped '+d.skipped.length+': '+d.skipped.join(' '):'');
  byId('ip-bulk-preview').hidden=false;message('Preview ready. Review the prices and click Apply preview to save.');
 }
 function request(mode) { return {mode,discount:byId('ip-bulk-percent').value,round99:byId('ip-bulk-round').checked,ids:selections()}; }
 byId('ip-bulk-all').addEventListener('click',()=>run(()=>showPreview(request('global'))));
 byId('ip-bulk-selected').addEventListener('click',()=>run(()=>showPreview(request('selected'))));
 byId('ip-bulk-random').addEventListener('click',()=>run(()=>showPreview(request('random'))));
 byId('ip-bulk-researched').addEventListener('click',()=>run(async()=>{
  const response=await fetch(InstapassBulk.researchUrl,{credentials:'same-origin'});
  if(!response.ok) throw new Error('The researched price file could not be loaded. Upload the provided Excel workbook instead.');
  const data=await response.json();
  if(data.currency!==currency) throw new Error('The researched prices are in '+data.currency+'. Store currency is '+currency+'. Convert and upload reviewed values first.');
  await showPreview({mode:'import',rows:data.rows,round99:false});
  byId('ip-bulk-summary').append(' Research checked '+data.checked+'; '+data.review_required+' unmatched offers retain current prices.');
 }));
 byId('ip-bulk-percent').addEventListener('input',invalidate);byId('ip-bulk-round').addEventListener('change',invalidate);byId('ip-bulk-search').addEventListener('input',filter);
 byId('ip-bulk-check').addEventListener('click',()=>{byId('ip-bulk-catalog').querySelectorAll('tr').forEach(row=>{if(!row.hidden)row.querySelector('input').checked=true;});invalidate();message(selections().length+' tools selected.');});
 byId('ip-bulk-clear').addEventListener('click',()=>{panel.querySelectorAll('[data-product]').forEach(x=>{x.checked=false;});invalidate();});
 byId('ip-bulk-cancel').addEventListener('click',()=>{invalidate();message('Preview cancelled. No prices changed.');});
 byId('ip-bulk-apply').addEventListener('click',()=>run(async()=>{
  if (!preview) throw new Error('Create a new preview first.'); const token=preview.token;invalidate();message('Saving prices…');
  const d=await api('apply',{token});message(`${d.state}: ${d.saved} of ${d.requested} tools saved. ${d.errors.join(' ')} Refresh any open pricing or storefront pages.`);
  await loadCatalog();await loadHistory();
 }));
 byId('ip-bulk-file').addEventListener('change',()=>run(async()=>{
  invalidate();const file=byId('ip-bulk-file').files[0];byId('ip-bulk-file').value='';if (!file) return;
  if (!/\.(xlsx|csv)$/i.test(file.name) || file.size>2*1024*1024) throw new Error('Choose an .xlsx or CSV file smaller than 2 MB.');
  if (typeof XLSX==='undefined') throw new Error('Excel support failed to load. Reload the page.');
  const book=XLSX.read(await file.arrayBuffer(),{type:'array',cellFormula:true,cellNF:true});const rows=workbookRows(book,XLSX);
  await showPreview({mode:'import',rows,round99:byId('ip-bulk-round').checked});byId('ip-bulk-file').value='';
 }));
 byId('ip-bulk-template').addEventListener('click',()=>{
  try {
   if (typeof XLSX==='undefined') throw new Error('Excel support failed to load. Reload the page.');
   const rows=[['SKU','Tool','Current regular price','Current sale price','Regular price','Discount %','Sale price','Currency'],...catalog.filter(p=>p.sku).map(p=>[p.sku,p.name,p.regular,p.sale,'','','',currency])];
   const book=XLSX.utils.book_new();XLSX.utils.book_append_sheet(book,XLSX.utils.aoa_to_sheet(rows),'Prices');
   XLSX.utils.book_append_sheet(book,XLSX.utils.aoa_to_sheet([['Instructions'],['Fill Regular price, Discount % OR Sale price on the Prices sheet. Leave unchanged rows blank.'],['Use one discount or one sale price per row. Zero discount removes a sale.'],['Do not use formulas. Keep the SKU and currency.'],['The preview shows exact results. Only Apply preview writes prices.'],['Visibility, stock and product descriptions remain unchanged.']]),'Instructions');
   XLSX.writeFile(book,'instapass-prices.xlsx');
  } catch(error) {message(error.message);}
 });
 run(async()=>{await loadCatalog();await loadHistory();message(catalog.length+' tools loaded in '+currency+'. Global discounts include every eligible tool, across all pricing pages.');});
})();
