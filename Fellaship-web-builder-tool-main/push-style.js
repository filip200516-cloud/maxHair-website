/**
 * Push theme style.css to WordPress via SFTP
 * Usage: node push-style.js
 */
import SftpClient from 'ssh2-sftp-client';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const configPath = path.join(__dirname, 'config.json');
const config = JSON.parse(fs.readFileSync(configPath, 'utf-8'));

const localStyle = path.join(config.local.projectPath, 'wp-content', 'themes', 'bricks', 'style.css');
const remotePath = `/home/${config.ssh.username}/domains/maxhair.cz/public_html/wp-content/themes/bricks/style.css`;

async function pushStyle() {
  console.log('📤 Push style.css: Nahrávám...\n');

  if (!fs.existsSync(localStyle)) {
    console.error('❌ Soubor nenalezen:', localStyle);
    process.exit(1);
  }

  const sftp = new SftpClient();
  try {
    await sftp.connect({
      host: config.ssh.host,
      port: config.ssh.port || 22,
      username: config.ssh.username,
      password: config.ssh.password,
    });
    console.log('✅ SFTP připojeno\n');

    await sftp.put(localStyle, remotePath);
    console.log('✅ style.css nahrán na:', remotePath);
  } catch (err) {
    console.error('❌ Chyba:', err.message);
    process.exit(1);
  } finally {
    await sftp.end();
  }
}

pushStyle();
