/**
 * Upload image to WordPress Media Library via REST API
 * Usage: node upload-media.js <path-to-image> [alt-text]
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

const imagePath = process.argv[2];
const altText = process.argv[3] || 'Spokojený klient MaxHair po transplantaci vlasů';

if (!imagePath || !fs.existsSync(imagePath)) {
  console.error('❌ Soubor nenalezen:', imagePath);
  process.exit(1);
}

const baseURL = config.wordpress.url;
const username = config.wordpress.username;
const password = (config.wordpress.applicationPassword || '').replace(/\s+/g, '');

async function uploadMedia() {
  const formData = new FormData();
  formData.append('file', fs.createReadStream(imagePath), {
    filename: path.basename(imagePath),
    contentType: 'image/png'
  });

  try {
    const response = await axios.post(
      `${baseURL}/wp-json/wp/v2/media`,
      formData,
      {
        auth: {
          username,
          password
        },
        headers: {
          ...formData.getHeaders()
        },
        maxContentLength: Infinity,
        maxBodyLength: Infinity
      }
    );

    const mediaUrl = response.data.source_url;
    const mediaId = response.data.id;

    // Update alt text if provided
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

    console.log('✅ Obrázek nahrán do WordPressu');
    console.log('   URL:', mediaUrl);
    console.log('   ID:', mediaId);
    return mediaUrl;
  } catch (error) {
    console.error('❌ Chyba při nahrávání:', error.response?.data || error.message);
    process.exit(1);
  }
}

uploadMedia();
