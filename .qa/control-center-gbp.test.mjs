import { PHP } from '@php-wasm/universal';import { loadNodeRuntime } from '@php-wasm/node';import fs from 'node:fs';import path from 'node:path';import assert from 'node:assert/strict';import { fileURLToPath } from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');const php=new PHP(await loadNodeRuntime('8.3',{emscriptenOptions:{processId:1}}));
try{
 const source=fs.readFileSync(path.join(root,'plugins/instapass-control-center/includes/gbp-values.php'),'utf8').replace(/^<\?php\s*/,'');
 for(const file of ['instapass-control-center.php','includes/gbp-editor.php']){const s=fs.readFileSync(path.join(root,'plugins/instapass-control-center',file),'utf8').replace(/^<\?php\s*/,'');const r=await php.run({code:'<?php if(false){'+s+'} echo "OK";'});assert.equal(r.errors,'');assert.ok(r.text.includes('OK'));}
 const r=await php.run({code:'<?php '+source+`
 $valid=[['','',''],['19.99','',''],['20','15',''],['20','','25'],['20','','30'],['19.99','','25'],['20','15','0'],['20','','100'],['20','0',''],['1.25','','50'],['20','','0.05']];
 $out=array_map(fn($c)=>Instapass_Control_GBP::values(...$c),$valid);
 $bad=[['','1',''],['','','25'],['0','',''],['-1','',''],['1.001','',''],['1e2','',''],['NaN','',''],['01.99','',''],['1,000','',''],['20','20',''],['20','21',''],['20','','100.01'],['20','','-1'],['20','','25.001'],['20',[],''],[null,'',''],['20','<script>',''],['20','','0.01']];
 $rejected=0;foreach($bad as $c){try{Instapass_Control_GBP::values(...$c);}catch(InvalidArgumentException $e){$rejected++;}}
 echo json_encode(['out'=>$out,'rejected'=>$rejected,'missing'=>Instapass_Control_GBP::effective('',''),'zero'=>Instapass_Control_GBP::effective('20','0')]);`});
 assert.equal(r.errors,'');const data=JSON.parse(r.text);assert.deepEqual(data.out.map(x=>x.sale),['','','15.00','15.00','14.00','14.99','','0.00','0.00','0.63','19.99']);
 assert.equal(data.rejected,18);assert.equal(data.missing,null);assert.equal(data.zero,'0.00');console.log('PASS GBP: independent values, 11 cases, 18 invalid inputs, blank unavailable, explicit zero sale.');
}finally{php.exit();}
