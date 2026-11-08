import { PHP } from '@php-wasm/universal';
import { loadNodeRuntime } from '@php-wasm/node';
import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';
import { fileURLToPath } from 'node:url';
const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const source=fs.readFileSync(path.join(root,'plugins/instapass-control-center/includes/money.php'),'utf8').replace(/^<\?php\s*/,'');
const main=fs.readFileSync(path.join(root,'plugins/instapass-control-center/instapass-control-center.php'),'utf8').replace(/^<\?php\s*/,'');
const php=new PHP(await loadNodeRuntime('8.3',{emscriptenOptions:{processId:1}}));
try {
 const lint=await php.run({code:'<?php if(false){'+main+'} echo "LINT_OK";'});
 assert.equal(lint.errors,''); assert.ok(lint.text.includes('LINT_OK'));
 const result=await php.run({code:'<?php '+source+`
 $cases=[['4.99',1],['9.99',1],['24.99',1],['49.99',1],['1.24',1],['1.25',1],['1.26',1],['4.99',3],['1.15',3],['0',1],['999999.99',2500],['4.99',1,'290.58'],['4.99',1,'290.59']];
 $quotes=array_map(fn($c)=>Instapass_Control_Money::quote(...$c),$cases);
 $bad=['-1','1.001','1e2','NaN','INF','01.99','1,000','<script>','',[],1.99,'1000000'];
 $rejected=0; foreach($bad as $v){try{Instapass_Control_Money::quote($v);}catch(InvalidArgumentException $e){$rejected++;}}
 $qtyRejected=0;foreach([0,-1,2501,1.5,'2'] as $v){try{Instapass_Control_Money::quote('4.99',$v);}catch(InvalidArgumentException $e){$qtyRejected++;}}
 echo json_encode(['quotes'=>$quotes,'rejected'=>$rejected,'qtyRejected'=>$qtyRejected]);`});
 assert.equal(result.errors,''); const data=JSON.parse(result.text);
 assert.deepEqual(data.quotes.map(q=>q.pkr_unit_rupees),[1400,2800,7000,14000,300,400,400,1400,300,0,280000000,1400,1500]);
 assert.equal(data.quotes[7].pkr_total_rupees,4200);
 // Detect accidentally rounding the aggregate: 1.15 USD x 3 would round 966 to 1000; correct is 300 x 3 = 900.
 assert.equal(data.quotes[8].pkr_total_rupees,900);
 assert.equal(data.quotes[10].pkr_total_rupees,700000000000);
 assert.equal(data.rejected,12);assert.equal(data.qtyRejected,5);
 assert.equal(data.quotes[0].policy,'usd-pkr-unit-nearest100-v2');
 console.log('PASS: PHP syntax; 13 money cases; 12 malformed prices; 5 invalid quantities; policy identity.');
}finally{php.exit();}
