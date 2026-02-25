import Client from 'ssh2-sftp-client';
import fs from 'fs-extra';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Načíst konfiguraci
const configPath = path.join(__dirname, 'config.json');
let config;

try {
  config = JSON.parse(await fs.readFile(configPath, 'utf-8'));
} catch (error) {
  console.error('❌ Chyba při načítání config.json:', error.message);
  console.log('💡 Nejdřív musíš zadat přístupy - vytvoř config.json');
  process.exit(1);
}

// SSH přístupové údaje z config.json
if (!config.ssh) {
  console.error('❌ SSH údaje nejsou v config.json');
  console.log('💡 Přidej SSH údaje do config.json:');
  console.log('   {');
  console.log('     "ssh": {');
  console.log('       "host": "...",');
  console.log('       "username": "...",');
  console.log('       "password": "...",');
  console.log('       "port": 22');
  console.log('     }');
  console.log('   }');
  process.exit(1);
}

const sshConfig = {
  host: config.ssh.host,
  username: config.ssh.username,
  password: config.ssh.password,
  port: config.ssh.port || 22,
  readyTimeout: 20000 // 20 sekund timeout
};

console.log(`🔌 Připojuji se k ${sshConfig.host}:${sshConfig.port} jako ${sshConfig.username}...`);

console.log('🔌 Připojuji se k serveru přes SSH...');

const sftp = new Client();

try {
  await sftp.connect(sshConfig);
  console.log('✅ Připojení úspěšné\n');

  // Zjistit správnou cestu k pluginu na serveru
  // Hostinger obvykle používá: /domains/[domain]/public_html nebo /home/[username]/public_html
  console.log('🔍 Hledám správnou cestu k pluginu...');
  
  // Získat doménu z WordPress URL
  const wpUrl = config.wordpress?.url || '';
  const domain = wpUrl.replace(/^https?:\/\//, '').replace(/\/.*$/, '');
  
  // Zkusit najít správnou cestu
  const possiblePaths = [
    `/domains/${domain}/public_html/wp-content/plugins/bricks-api-endpoint/bricks-api-endpoint.php`,
    `/home/${sshConfig.username}/public_html/wp-content/plugins/bricks-api-endpoint/bricks-api-endpoint.php`,
    `/home/${sshConfig.username}/domains/${domain}/public_html/wp-content/plugins/bricks-api-endpoint/bricks-api-endpoint.php`
  ];
  
  let remotePath = null;
  let pluginDir = null;
  
  for (const testPath of possiblePaths) {
    const dirPath = testPath.substring(0, testPath.lastIndexOf('/'));
    try {
      const exists = await sftp.exists(dirPath);
      if (exists) {
        remotePath = testPath;
        pluginDir = dirPath;
        console.log(`   ✅ Nalezena cesta: ${dirPath}`);
        break;
      }
    } catch (e) {
      // Pokračovat
    }
  }
  
  if (!remotePath) {
    // Zkusit najít wp-content složku
    console.log('   🔍 Hledám wp-content složku...');
    try {
      const wpContentPath = `/home/${sshConfig.username}/domains/${domain}/public_html/wp-content/plugins/bricks-api-endpoint`;
      const exists = await sftp.exists(wpContentPath);
      if (exists) {
        remotePath = wpContentPath + '/bricks-api-endpoint.php';
        pluginDir = wpContentPath;
        console.log(`   ✅ Nalezena cesta: ${wpContentPath}`);
      } else {
        // Vytvořit složku, pokud neexistuje
        console.log('   📁 Vytvářím složku pro plugin...');
        await sftp.mkdir(wpContentPath, true);
        remotePath = wpContentPath + '/bricks-api-endpoint.php';
        pluginDir = wpContentPath;
      }
    } catch (e) {
      console.error('   ❌ Nepodařilo se najít nebo vytvořit cestu');
      throw e;
    }
  }
  
  const localPath = path.join(__dirname, 'bricks-api-endpoint.php');

  // Zkontrolovat, zda lokální soubor existuje
  if (!await fs.pathExists(localPath)) {
    console.error('❌ Lokální soubor bricks-api-endpoint.php nenalezen!');
    process.exit(1);
  }

  console.log('📤 Nahrávám aktualizovaný plugin...');
  await sftp.put(localPath, remotePath);
  console.log('✅ Plugin úspěšně nahrán\n');

  // Nastavit oprávnění
  await sftp.chmod(remotePath, 0o644);
  console.log('✅ Oprávnění nastavena\n');

  await sftp.end();
  console.log('🎉 Hotovo! Plugin byl aktualizován.');
  console.log('\n💡 Nyní spusťte: node sync.js remove-bricks');

} catch (error) {
  console.error('❌ Chyba:', error.message);
  console.log('\n💡 Možná řešení:');
  console.log('   1. Zkontrolujte SSH přístupové údaje');
  console.log('   2. Zkontrolujte, zda máte SSH přístup v Hostinger hPanel');
  console.log('   3. Použijte alternativní metodu: nahrajte plugin přes Hostinger File Manager');
  process.exit(1);
}

