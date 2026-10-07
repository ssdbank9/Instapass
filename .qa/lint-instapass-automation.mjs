import { PHP } from '@php-wasm/universal';
import { loadNodeRuntime } from '@php-wasm/node';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const sourcePath = path.join(projectRoot, 'plugins/instapass-automation/instapass-automation.php');
const source = fs.readFileSync(sourcePath, 'utf8').replace(/^<\?php\s*/, '');
const php = new PHP(await loadNodeRuntime('8.3', { emscriptenOptions: { processId: 1 } }));

// Compile the complete source while avoiding execution of WordPress hooks.
const result = await php.run({ code: '<?php if (false) {' + source + '} echo "PHP_PARSE_OK";' });
php.exit();

if (!result.text.includes('PHP_PARSE_OK')) {
  process.stderr.write(result.text + result.errors);
  process.exit(1);
}
console.log('PASS PHP syntax: instapass-automation.php');
