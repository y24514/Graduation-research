/*
 * Render PlantUML .puml files into SVG/PNG using the public PlantUML server.
 * - No local Graphviz required.
 * - Requires Node.js 18+ (fetch) and internet access.
 *
 * Usage (from repo root):
 *   node sportdataapp/docs/uml/render-plantuml.cjs
 */

const fs = require('fs/promises');
const path = require('path');
const { encode } = require('plantuml-encoder');

const UML_DIR = path.resolve(__dirname);
const OUT_DIR = path.resolve(__dirname, 'out');
const SERVER_BASE = 'https://www.plantuml.com/plantuml';

async function* walk(dir) {
  const entries = await fs.readdir(dir, { withFileTypes: true });
  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      if (entry.name === 'out' || entry.name.startsWith('.')) continue;
      yield* walk(fullPath);
    } else if (entry.isFile() && entry.name.toLowerCase().endsWith('.puml')) {
      yield fullPath;
    }
  }
}

async function writeFileAtomic(filePath, data) {
  const tmpPath = `${filePath}.tmp`;
  await fs.writeFile(tmpPath, data);
  await fs.rename(tmpPath, filePath);
}

async function renderOne(pumlPath) {
  const rel = path.relative(UML_DIR, pumlPath);
  const baseName = path.basename(pumlPath, path.extname(pumlPath));

  const source = await fs.readFile(pumlPath, 'utf8');
  const encoded = encode(source);

  const svgUrl = `${SERVER_BASE}/svg/${encoded}`;
  const pngUrl = `${SERVER_BASE}/png/${encoded}`;

  const svgOut = path.join(OUT_DIR, `${baseName}.svg`);
  const pngOut = path.join(OUT_DIR, `${baseName}.png`);

  const svgRes = await fetch(svgUrl);
  if (!svgRes.ok) {
    throw new Error(`SVG render failed (${svgRes.status}) for ${rel}`);
  }
  const svgText = await svgRes.text();

  const pngRes = await fetch(pngUrl);
  if (!pngRes.ok) {
    throw new Error(`PNG render failed (${pngRes.status}) for ${rel}`);
  }
  const pngBuf = Buffer.from(await pngRes.arrayBuffer());

  await writeFileAtomic(svgOut, svgText);
  await writeFileAtomic(pngOut, pngBuf);

  return { rel, svgOut, pngOut };
}

async function main() {
  await fs.mkdir(OUT_DIR, { recursive: true });

  const pumlFiles = [];
  for await (const f of walk(UML_DIR)) pumlFiles.push(f);

  if (pumlFiles.length === 0) {
    console.log('No .puml files found.');
    return;
  }

  console.log(`Found ${pumlFiles.length} .puml file(s). Rendering...`);

  const results = [];
  for (const pumlPath of pumlFiles) {
    try {
      const r = await renderOne(pumlPath);
      results.push(r);
      console.log(`OK: ${r.rel} -> out/${path.basename(r.svgOut)}, out/${path.basename(r.pngOut)}`);
    } catch (e) {
      console.error(`NG: ${path.relative(UML_DIR, pumlPath)}: ${e.message}`);
      throw e;
    }
  }

  console.log('Done.');
}

main().catch((e) => {
  console.error(e);
  process.exitCode = 1;
});
