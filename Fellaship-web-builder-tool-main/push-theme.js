/**
 * Push theme files (functions.php) to WordPress via SFTP
 * Usage: node push-theme.js
 */
import SftpClient from 'ssh2-sftp-client';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const configPath = path.join(__dirname, 'config.json');
const config = JSON.parse(fs.readFileSync(configPath, 'utf-8'));

const localFunctions = path.join(config.local.projectPath, 'wp-content', 'themes', 'bricks', 'functions.php');
const remotePath = `/home/${config.ssh.username}/domains/maxhair.cz/public_html/wp-content/themes/bricks/functions.php`;

async function pushTheme() {
  console.log('📤 Push theme: Nahrávám functions.php...\n');

  if (!fs.existsSync(localFunctions)) {
    console.error('❌ Soubor nenalezen:', localFunctions);
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

    await sftp.put(localFunctions, remotePath);
    console.log('✅ functions.php nahrán na:', remotePath);
  } catch (err) {
    console.error('❌ Chyba:', err.message);
    if (err.message.includes('No such file')) {
      console.log('\n💡 Zkuste alternativní cestu: domains/maxhair.cz/public_html/...');
    }
    process.exit(1);
  } finally {
    await sftp.end();
  }
}

pushTheme();
