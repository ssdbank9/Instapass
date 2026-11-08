(() => {
 'use strict';
 document.querySelectorAll('.ip-allocation').forEach(box => {
  const mode=box.querySelector('.ip-allocation-mode'),qty=Number(box.dataset.quantity),months=Number(box.dataset.months),rows=[...box.querySelectorAll('.ip-allocation-account')],total=box.querySelector('.ip-allocation-total'),countField=box.querySelector('.ip-allocation-count'),countWrap=box.querySelector('.ip-allocation-count-wrap');
  if(!countField||!countWrap)return;
  let customCount=Math.min(2,rows.length);
  function update(reset=false){
   const selected=mode.value==='one'?1:mode.value==='each'?qty:mode.value==='custom'?customCount:0;
   countWrap.hidden=mode.value!=='custom';countField.disabled=mode.value!=='custom';countField.required=mode.value==='custom';countField.value=selected?String(selected):'';
   let sum=0,count=0;
   rows.forEach((row,i)=>{
    const units=row.querySelector('.ip-allocation-units'),email=row.querySelector('input[type=email]'),show=i<selected;
    row.hidden=!show;row.querySelectorAll('input,select').forEach(e=>e.disabled=!show);
    if(mode.value==='one'&&i===0)units.value=qty;
    if(mode.value==='each')units.value=1;
    if(mode.value==='custom'&&reset&&show)units.value=Math.floor(qty/selected)+(i<qty%selected?1:0);
    units.readOnly=mode.value!=='custom';
    units.required=show;const n=show?Number(units.value):0;email.required=show&&selected>1;
    row.querySelector('.ip-allocation-term').textContent=n>0?`${n*months} month${n*months===1?'':'s'} on this account`:'';
    if(n>0){sum+=n;count++;}
   });
   total.textContent=mode.value?`${sum} of ${qty} units allocated across ${count} account${count===1?'':'s'}. ${sum===qty?'All units allocated.':'Please allocate exactly '+qty+' units.'}`:'';
  }
  mode.addEventListener('change',()=>update(true));box.addEventListener('input',e=>{if(e.target.classList.contains('ip-allocation-units'))update();});update();
  countField.addEventListener('change',()=>{customCount=Number(countField.value)||Math.min(2,rows.length);update(true);});
 });
})();
