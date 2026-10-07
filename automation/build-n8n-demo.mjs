import {readFile,writeFile} from 'node:fs/promises';
const source=(await readFile(new URL('./price-command.mjs',import.meta.url),'utf8')).replace('export function','function');
const catalogue=[{id:1,name:'Canva Pro',sku:'canva-pro',type:'simple',regular_price:'20',sale_price:'14.99',categories:['Creativity'],status:'publish',fingerprint:'demo-only'}];
const workflow={
 name:'Instapass — price command preview DEMO (no live writes)',active:false,
 nodes:[
  {id:'manual',name:'Run demo',type:'n8n-nodes-base.manualTrigger',typeVersion:1,position:[0,0],parameters:{}},
  {id:'input',name:'Demo input — edit command here',type:'n8n-nodes-base.code',typeVersion:2,position:[240,0],parameters:{jsCode:`return [{json:{command:'Give Canva Pro 25% off',currency:'USD',products:${JSON.stringify(catalogue)}}}];`}},
  {id:'preview',name:'Validate and prepare preview',type:'n8n-nodes-base.code',typeVersion:2,position:[500,0],parameters:{jsCode:source+"\nconst input=$input.first().json; return [{json:preparePriceCommand(input.command,input.products,input.currency)}];"}}
 ],
 connections:{'Run demo':{main:[[{node:'Demo input — edit command here',type:'main',index:0}]]},'Demo input — edit command here':{main:[[{node:'Validate and prepare preview',type:'main',index:0}]]}},
 settings:{executionOrder:'v1',saveDataSuccessExecution:'none',saveDataErrorExecution:'none',saveManualExecutions:false},
 pinData:{},tags:[]
};
await writeFile(new URL('./instapass-price-preview.n8n.json',import.meta.url),JSON.stringify(workflow,null,2)+'\n');
console.log('Created inactive n8n manual preview workflow with sample catalogue only.');
