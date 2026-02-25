/**
 * Přidá SMTP konstanty do wp-config.php na serveru (bez přepsání celého souboru)
 * Usage: node push-smtp-config.js
 */
import SftpClient from 'ssh2-sftp-client';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const configPath = path.join(__dirname, 'config.json');
const config = JSON.parse(fs.readFileSync(configPath, 'utf-8'));

const remoteConfigPath = `/home/${config.ssh.username}/domains/maxhair.cz/public_html/wp-config.php`;

const SMTP_BLOCK = `
/** MaxHair formuláře - SMTP bez pluginu */
define( 'MAXHAIR_SMTP_HOST', 'smtp.hostinger.com' );
define( 'MAXHAIR_SMTP_PORT', 465 );
define( 'MAXHAIR_SMTP_SECURE', 'ssl' );
define( 'MAXHAIR_SMTP_USER', 'forms@fellaship.cz' );
define( 'MAXHAIR_SMTP_PASS', 'F&F_Fellaship&Forms69' );
`;

async function pushSmtpConfig() {
  console.log('📤 Přidávám SMTP konstanty do wp-config.php na serveru...\n');

  const sftp = new SftpClient();
  try {
    await sftp.connect({
      host: config.ssh.host,
      port: config.ssh.port || 22,
      username: config.ssh.username,
      password: config.ssh.password,
    });
    console.log('✅ SFTP připojeno\n');

    const remoteContent = await sftp.get(remoteConfigPath);
    let content = remoteContent.toString('utf-8');

    if (content.includes("MAXHAIR_SMTP_PASS")) {
      console.log('⚠️  SMTP konstanty již v wp-config.php jsou');
      return;
    }

    const marker = "/* That's all, stop editing!";
    if (!content.includes(marker)) {
      console.error('❌ Nelze najít marker v wp-config.php');
      process.exit(1);
    }

    content = content.replace(marker, SMTP_BLOCK.trim() + '\n\n' + marker);
    await sftp.put(Buffer.from(content, 'utf-8'), remoteConfigPath);
    console.log('✅ SMTP konstanty přidány do wp-config.php');
  } catch (err) {
    console.error('❌ Chyba:', err.message);
    process.exit(1);
  } finally {
    await sftp.end();
  }
}

pushSmtpConfig();
