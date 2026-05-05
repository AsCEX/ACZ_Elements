import { readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const bumpType = process.argv[2] || 'patch';
const allowedBumps = new Set(['patch', 'minor', 'major']);

function parseVersion(version) {
  const match = String(version).match(/^(\d+)\.(\d+)\.(\d+)$/);
  if (!match) {
    throw new Error(`Unsupported version format: ${version}. Expected X.Y.Z.`);
  }

  return match.slice(1).map(Number);
}

function formatVersion(parts) {
  return parts.join('.');
}

function getNextVersion(current, requested) {
  if (/^\d+\.\d+\.\d+$/.test(requested)) {
    return requested;
  }

  if (!allowedBumps.has(requested)) {
    throw new Error('Usage: npm run version:bump -- patch|minor|major|X.Y.Z');
  }

  const parts = parseVersion(current);

  if (requested === 'major') {
    parts[0] += 1;
    parts[1] = 0;
    parts[2] = 0;
  } else if (requested === 'minor') {
    parts[1] += 1;
    parts[2] = 0;
  } else {
    parts[2] += 1;
  }

  return formatVersion(parts);
}

function replaceRequired(content, pattern, replacement, label) {
  if (!pattern.test(content)) {
    throw new Error(`Could not update ${label}.`);
  }

  return content.replace(pattern, replacement);
}

const pluginPath = path.join(root, 'acz-elements.php');
const readmePath = path.join(root, 'readme.txt');
const packagePath = path.join(root, 'package.json');
const lockPath = path.join(root, 'package-lock.json');

let plugin = await readFile(pluginPath, 'utf8');
const currentMatch = plugin.match(/^\s*\*\s*Version:\s*([^\s]+)\s*$/m);

if (!currentMatch) {
  throw new Error('Could not find Version header in acz-elements.php.');
}

const currentVersion = currentMatch[1];
const nextVersion = getNextVersion(currentVersion, bumpType);

if (nextVersion === currentVersion) {
  throw new Error(`Version is already ${nextVersion}.`);
}

plugin = replaceRequired(plugin, /^(\s*\*\s*Version:\s*)[^\s]+(\s*)$/m, `$1${nextVersion}$2`, 'plugin header version');
plugin = replaceRequired(plugin, /define\(\s*'ACZ_ELEMENTS_VERSION'\s*,\s*'[^']+'\s*\);/, `define( 'ACZ_ELEMENTS_VERSION', '${nextVersion}' );`, 'ACZ_ELEMENTS_VERSION');
await writeFile(pluginPath, plugin, 'utf8');

let readme = await readFile(readmePath, 'utf8');
readme = replaceRequired(readme, /^Stable tag:\s*.+$/m, `Stable tag: ${nextVersion}`, 'readme stable tag');
readme = readme.replace(/^= \d+\.\d+\.\d+ =$/m, `= ${nextVersion} =`);
await writeFile(readmePath, readme, 'utf8');

const pkg = JSON.parse(await readFile(packagePath, 'utf8'));
pkg.version = nextVersion;
await writeFile(packagePath, `${JSON.stringify(pkg, null, 2)}\n`, 'utf8');

const lock = JSON.parse(await readFile(lockPath, 'utf8'));
lock.version = nextVersion;
if (lock.packages && lock.packages['']) {
  lock.packages[''].version = nextVersion;
}
await writeFile(lockPath, `${JSON.stringify(lock, null, 2)}\n`, 'utf8');

console.log(`Bumped version: ${currentVersion} -> ${nextVersion}`);
