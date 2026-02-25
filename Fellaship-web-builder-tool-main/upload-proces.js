/**
 * Upload all 5 proces images to WordPress Media Library.
 * Images must be in: {projectPath}/assets/proces-1-konzultace.png ... proces-5-rust.png
 * Usage: node upload-proces.js
 */
import fs from 'fs';
import path from 'path';
import axios from 'axios';
import FormData from 'form-data';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const configPath = path.join(__dirname, 'config.json');
const config = JSON.parse(fs.readFileSync(configPath, 'utf-8'));

const projectPath = config.local.projectPath || path.resolve(__dirname, '..');
const assetsDir = path.join(projectPath, 'assets');

const FILES = [
  { file: 'proces-1-konzultace.png', alt: 'Online konzultace s lékařem' },
  { file: 'proces-2-priprava.png', alt: 'Příprava na cestu – ubytování a transfer' },
  { file: 'proces-3-zakrok.png', alt: 'Zákrok v lokální anestezii – klinika' },
  { file: 'proces-4-hojeni.png', alt: 'Hojení a péče o pokožku po zákroku' },
  { file: 'proces-5-rust.png', alt: 'Výsledek – přirozený růst vlasů' }
];

/** Pro podstránku pro muže: obrázek kroku 5 s mužskou postavou. Upload: node upload-proces.js muzi */
const MUZI_FILE = { file: 'proces-5-rust-muzi.png', alt: 'Výsledek – přirozený růst vlasů u muže' };
/** Verze s pozadím #F7F6E9 pro sekci Proces. Upload: node upload-proces.js muzi2 */
const MUZI2_FILE = { file: 'proces-5-rust-muzi-2.png', alt: 'Výsledek – přirozený růst vlasů u muže' };

const baseURL = config.wordpress.url;
const username = config.wordpress.username;
const password = (config.wordpress.applicationPassword || '').replace(/\s+/g, '');

async function uploadOne(fileName, altText) {
  const imagePath = path.join(assetsDir, fileName);
  if (!fs.existsSync(imagePath)) {
    throw new Error(`Soubor nenalezen: ${imagePath}`);
  }
  const formData = new FormData();
  formData.append('file', fs.createReadStream(imagePath), {
    filename: fileName,
    contentType: 'image/png'
  });

  const response = await axios.post(
    `${baseURL}/wp-json/wp/v2/media`,
    formData,
    {
      auth: { username, password },
      headers: formData.getHeaders(),
      maxContentLength: Infinity,
      maxBodyLength: Infinity
    }
  );

  const mediaUrl = response.data.source_url;
  const mediaId = response.data.id;

  if (altText) {
    await axios.post(
      `${baseURL}/wp-json/wp/v2/media/${mediaId}`,
      { alt_text: altText },
      {
        auth: { username, password },
        headers: { 'Content-Type': 'application/json' }
      }
    );
  }

  return mediaUrl;
}

async function main() {
  const arg = process.argv[2];
  const onlyMuzi = arg === 'muzi';
  const onlyMuzi2 = arg === 'muzi2';
  const toUpload = onlyMuzi2 ? [MUZI2_FILE] : onlyMuzi ? [MUZI_FILE] : FILES;
  const msg = onlyMuzi2 ? '📤 Nahrávám obrázek proces-5-rust-muzi-2.png (pozadí #F7F6E9)...\n' : onlyMuzi ? '📤 Nahrávám obrázek procesu pro muže...\n' : '📤 Nahrávám obrázky procesu do WordPress Media...\n';
  console.log(msg);
  const urls = {};
  for (const { file, alt } of toUpload) {
    try {
      const url = await uploadOne(file, alt);
      urls[file] = url;
      console.log('✅', file, '→', url);
    } catch (err) {
      console.error('❌', file, err.response?.data?.message || err.message);
      process.exit(1);
    }
  }
  console.log('\n📋 URL pro kód (sekce #proces):');
  console.log(JSON.stringify(urls, null, 2));
}

main();
