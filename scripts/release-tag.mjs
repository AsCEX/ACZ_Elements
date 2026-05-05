import { readFile } from 'node:fs/promises';
import { execFileSync } from 'node:child_process';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const pluginFile = path.join(root, 'acz-elements.php');
const pluginHeader = await readFile(pluginFile, 'utf8');
const versionMatch = pluginHeader.match(/^\s*\*\s*Version:\s*([^\s]+)\s*$/m);

if (!versionMatch) {
  console.error('Could not find Version header in acz-elements.php.');
  process.exit(1);
}

const version = versionMatch[1];
const tag = `v${version}`;

function git(args) {
  return execFileSync('git', args, {
    cwd: root,
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'pipe']
  }).trim();
}

const status = git(['status', '--porcelain']);
if (status) {
  console.error('Working tree is not clean. Commit or stash changes before tagging.');
  console.error(status);
  process.exit(1);
}

const existingTags = git(['tag', '--list', tag]);
if (existingTags.split('\n').filter(Boolean).includes(tag)) {
  console.error(`Tag ${tag} already exists locally.`);
  process.exit(1);
}

git(['tag', '-a', tag, '-m', `Release ${tag}`]);
git(['push', 'origin', tag]);

console.log(`Created and pushed ${tag}.`);
