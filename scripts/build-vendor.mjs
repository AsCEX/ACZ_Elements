import { cp, mkdir, readFile, rm, writeFile } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const vendorDir = path.join(root, 'assets', 'vendor');
const nodeModulesDir = path.join(root, 'node_modules');

async function ensureFile(source, label) {
  if (!existsSync(source)) {
    throw new Error(`${label} not found at ${path.relative(root, source)}. Run npm install first.`);
  }
}

async function copyFile(source, destination, label) {
  await ensureFile(source, label);
  await mkdir(path.dirname(destination), { recursive: true });
  await cp(source, destination);
}

async function copyDir(source, destination, label) {
  if (!existsSync(source)) {
    throw new Error(`${label} not found at ${path.relative(root, source)}. Run npm install first.`);
  }

  await rm(destination, { recursive: true, force: true });
  await mkdir(path.dirname(destination), { recursive: true });
  await cp(source, destination, { recursive: true });
}

async function buildSwiper() {
  const sourceDir = path.join(nodeModulesDir, 'swiper');
  const targetDir = path.join(vendorDir, 'swiper');

  await mkdir(targetDir, { recursive: true });
  await copyFile(path.join(sourceDir, 'swiper-bundle.css'), path.join(targetDir, 'swiper-bundle.css'), 'Swiper source CSS');
  await copyFile(path.join(sourceDir, 'swiper-bundle.min.css'), path.join(targetDir, 'swiper-bundle.min.css'), 'Swiper minified CSS');
  await copyFile(path.join(sourceDir, 'swiper-bundle.js'), path.join(targetDir, 'swiper-bundle.js'), 'Swiper source JS');
  await copyFile(path.join(sourceDir, 'swiper-bundle.min.js'), path.join(targetDir, 'swiper-bundle.min.js'), 'Swiper minified JS');
  await copyFile(path.join(sourceDir, 'LICENSE'), path.join(targetDir, 'LICENSE'), 'Swiper license');
}

async function buildColouredIcons() {
  const sourceDir = path.join(nodeModulesDir, 'coloured-icons');
  const targetDir = path.join(vendorDir, 'coloured-icons');
  const publicTarget = path.join(vendorDir, 'public');

  await mkdir(targetDir, { recursive: true });
  await copyFile(path.join(sourceDir, 'app', 'ci.css'), path.join(targetDir, 'ci.css'), 'Coloured Icons source CSS');
  await copyFile(path.join(sourceDir, 'app', 'ci.min.css'), path.join(targetDir, 'ci.min.css'), 'Coloured Icons minified CSS');
  await copyFile(path.join(sourceDir, 'package.json'), path.join(targetDir, 'package.json'), 'Coloured Icons package metadata');
  await copyDir(path.join(sourceDir, 'public', 'logos'), path.join(publicTarget, 'logos'), 'Coloured Icons logos');

  const packageJson = JSON.parse(await readFile(path.join(sourceDir, 'package.json'), 'utf8'));
  const license = packageJson.license || 'ISC';

  await writeFile(
    path.join(targetDir, 'README.md'),
    '# Coloured Icons\n\n' +
      'This directory contains release assets copied from the coloured-icons package.\n\n' +
      'The runtime CSS is loaded locally by ACZ Elements from `assets/vendor/coloured-icons/ci.min.css`.\n' +
      'Referenced logo assets are bundled locally under `assets/vendor/public/logos/`.\n\n' +
      'Upstream source: https://github.com/dheereshag/coloured-icons\n',
    'utf8'
  );

  await writeFile(
    path.join(targetDir, 'LICENSE'),
    `${license} License\n\n` +
      'The coloured-icons package metadata declares this library license. ' +
      'See package.json and README.md in this directory for upstream details.\n',
    'utf8'
  );
}

async function main() {
  await buildSwiper();
  await buildColouredIcons();
  console.log('Vendor assets built in assets/vendor.');
}

main().catch((error) => {
  console.error(error.message);
  process.exitCode = 1;
});
