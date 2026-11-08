(function($){'use strict';
 const field=k=>document.getElementById('ipo-'+k),logos=document.getElementById('ipo-logo-preview');
 const radioValue=k=>document.querySelector('input[name="ip_offer['+k+']"]:checked')?.value||'';
 const radioGroup=k=>document.querySelectorAll('input[name="ip_offer['+k+']"]');

 // Catalog actions are independent of the offer editor and remain available
 // anywhere this admin page renders the catalog table.
 const selectAll=document.querySelector('.ipo-select-all');
 if(selectAll)selectAll.addEventListener('change',()=>document.querySelectorAll('.ipo-offer-select').forEach(box=>box.checked=selectAll.checked));
 document.querySelectorAll('.ipo-quick-state-form').forEach(form=>form.addEventListener('submit',event=>{
  const message=form.querySelector('[data-confirm]')?.dataset.confirm;
  if(message&&!window.confirm(message))event.preventDefault();
 }));
 const bulk=document.getElementById('ipo-bulk-status');
 if(bulk)bulk.addEventListener('submit',event=>{
  const selected=Array.from(document.querySelectorAll('.ipo-offer-select:checked'));
  if(!selected.length){event.preventDefault();window.alert('Select at least one offer first.');return;}
  const target=bulk.querySelector('input[name="target_status"]:checked')?.value;
  if(target==='draft'&&!window.confirm('Deactivate '+selected.length+' selected offer(s)? They will be hidden from the shop. Existing orders remain unchanged.')){event.preventDefault();return;}
  bulk.querySelectorAll('.ipo-bulk-generated').forEach(input=>input.remove());
  selected.forEach(box=>{
   const id=document.createElement('input');id.type='hidden';id.name='offer_ids[]';id.value=box.value;id.className='ipo-bulk-generated';bulk.appendChild(id);
   const fp=document.createElement('input');fp.type='hidden';fp.name='fingerprints['+box.value+']';fp.value=box.dataset.fingerprint||'';fp.className='ipo-bulk-generated';bulk.appendChild(fp);
  });
 });

 // The editor-specific controls are absent on a catalog-only browser, so no
 // early return can disable the catalog actions above.
 if(!logos||!field('image_id'))return;
 const files=JSON.parse(logos.dataset.files||'[]');
 function visual(){logos.replaceChildren();if(Number(field('image_id').value)>0)return;for(const k of ['brand','brand_secondary']){const key=field(k).value,file=files.find(f=>f===key+'.svg'||f===key+'.png');if(!file)continue;const img=document.createElement('img');img.src=logos.dataset.base+encodeURIComponent(file);img.alt=field(k).selectedOptions[0].textContent;logos.append(img);}}
 for(const k of ['brand','brand_secondary'])field(k).addEventListener('change',visual);visual();
 document.getElementById('ipo-image-choose').addEventListener('click',()=>{const picker=wp.media({title:'Choose the product image',button:{text:'Use this image'},library:{type:'image'},multiple:false});picker.on('select',()=>{const a=picker.state().get('selection').first().toJSON();field('image_id').value=a.id;const img=document.createElement('img');img.src=(a.sizes&&a.sizes.thumbnail?a.sizes.thumbnail.url:a.url);img.alt=a.alt||'';document.getElementById('ipo-image-preview').replaceChildren(img);visual();});picker.open();});
 document.getElementById('ipo-image-clear').addEventListener('click',()=>{field('image_id').value='0';document.getElementById('ipo-image-preview').replaceChildren();visual();});
 function prices(){const bits=[];for(const c of ['usd','pkr','gbp','eur']){const auto=c==='pkr'&&radioValue('pkr_mode')==='auto';for(const k of ['regular','sale','discount'])field(c+'_'+k).disabled=auto;if(auto){bits.push('PKR follows USD and the saved rate');continue;}const r=Number(field(c+'_regular').value),pct=field(c+'_discount').value,s=field(c+'_sale').value;if(field(c+'_regular').value==='')continue;const end=pct!==''?r*(1-Number(pct)/100):(s!==''?Number(s):r);if(Number.isFinite(end))bits.push(c.toUpperCase()+': '+end.toFixed(2)+' full term'+(r>0&&end<r?' · '+((r-end)/r*100).toFixed(2)+'% saving':''));}document.getElementById('ipo-price-preview').textContent='Price preview: '+(bits.join(' | ')||'Enter prices above.');}
 function quantityMode(){const tracked=radioValue('manage_stock')==='yes',input=field('stock_quantity'),label=input.closest('.ipo-field');label.hidden=!tracked;input.required=tracked;}
 radioGroup('pkr_mode').forEach(radio=>radio.addEventListener('change',prices));
 radioGroup('manage_stock').forEach(radio=>radio.addEventListener('change',quantityMode));quantityMode();
 const duration=field('duration');document.querySelectorAll('input[name="ipo_duration_preset"]').forEach(radio=>radio.addEventListener('change',()=>{if(radio.value==='custom'){duration.value='';duration.focus();}else duration.value=radio.value;}));
 duration.addEventListener('input',()=>{const match=Array.from(document.querySelectorAll('input[name="ipo_duration_preset"]')).find(radio=>radio.value===duration.value);if(match)match.checked=true;else{const custom=document.querySelector('input[name="ipo_duration_preset"][value="custom"]');if(custom)custom.checked=true;}});
 document.getElementById('ipo-editor').addEventListener('submit',()=>{for(const c of ['regular','sale','discount'])field('pkr_'+c).disabled=false;});
 document.getElementById('ipo-editor').addEventListener('input',prices);document.getElementById('ipo-editor').addEventListener('change',prices);prices();
})(jQuery);
