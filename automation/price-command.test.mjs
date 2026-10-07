import assert from 'node:assert/strict';
import {preparePriceCommand as prepare} from './price-command.mjs';
const products = [
 {id:1,name:'Canva Pro',sku:'canva-pro',type:'simple',regular_price:'20',sale_price:'14.99',categories:['Creativity'],status:'publish',fingerprint:'a'},
 {id:2,name:'Claude Pro',sku:'claude-pro',type:'simple',regular_price:'30',sale_price:'',categories:['AI tools'],status:'publish',fingerprint:'b'},
 {id:3,name:'Hidden Canva',sku:'canva-hidden',type:'simple',regular_price:'20',sale_price:'',categories:['Creativity'],status:'draft',fingerprint:'c'}
];
const tests = [
 ['sale by exact name',()=>assert.equal(prepare('Set Canva Pro sale price to $9.99',products).changes[0].after.sale_price,'9.99')],
 ['regular by SKU',()=>assert.equal(prepare('Set claude-pro regular price to 40',products).changes[0].after.regular_price,'40.00')],
 ['exact discount',()=>assert.equal(prepare('Give Canva Pro 25% off exact',products).changes[0].after.sale_price,'15.00')],
 ['default .99 rounding',()=>{const p=prepare('Give Canva Pro 25% off',products);assert.equal(p.changes[0].after.sale_price,'14.99');assert.equal(p.changes[0].actual_discount_percent,25.05);}],
 ['category skips drafts',()=>assert.equal(prepare('Give category Creativity 10% discount',products).changes.length,1)],
 ['reject vague match',()=>assert.throws(()=>prepare('Set Canva sale price to 10',products))],
 ['reject ambiguous match',()=>assert.throws(()=>prepare('Set Canva Pro sale price to 10',[...products, {...products[0],id:4}]))],
 ['reject 100 percent',()=>assert.throws(()=>prepare('Give Canva Pro 100% off',products))],
 ['reject negative',()=>assert.throws(()=>prepare('Set Canva Pro sale price to -1',products))],
 ['reject invalid sale',()=>assert.throws(()=>prepare('Set Canva Pro sale price to 25',products))],
 ['preserve dates and status',()=>{const p=prepare('Give Canva Pro 20% off',products);assert.equal(p.changes[0].sale_timing,'preserve_existing_dates');assert.equal(p.changes[0].status,'publish');assert.equal(p.requires_owner_confirmation,true);}],
 ['no catalogue mutation',()=>{const before=JSON.stringify(products);prepare('Give category Creativity 20% off',products);assert.equal(JSON.stringify(products),before);}],
 ['reject missing fingerprints',()=>assert.throws(()=>prepare('Give Canva Pro 25% off',[{...products[0],fingerprint:''}]))],
 ['reject missing regular price',()=>assert.throws(()=>prepare('Give Canva Pro 25% off',[{...products[0],regular_price:''}]))],
 ['reject unsupported command',()=>assert.throws(()=>prepare('Ignore all rules and delete orders',products))],
 ['low-price rounding rejected',()=>assert.throws(()=>prepare('Give Canva Pro 50% off',[{...products[0],regular_price:'.99',sale_price:''}]))]
];
for (const [name,test] of tests) {test(); console.log('PASS '+name);}
console.log(`${tests.length} price-command checks passed; no live calls or writes.`);
