/**
 * Uložení přístupů do přístupy.md
 * Tento skript uloží všechny přístupy, které AI získalo od uživatele
 */

import fs from 'fs-extra';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const PRÍSTUPY_FILE = path.join(__dirname, 'přístupy.md');
const CONFIG_FILE = path.join(__dirname, 'config.json');

/**
 * Uložit přístupy do souboru
 */
export async function saveAccess(accessData) {
  const {
    projectName,
    wordpressUrl,
    wordpressUsername,
    wordpressApplicationPassword,
    sshHost,
    sshUsername,
    sshPassword,
    sshPort,
    githubRepo
  } = accessData;

  const timestamp = new Date().toISOString().split('T')[0];
  
  const content = `# Přístupy - ${projectName || 'Projekt'}

**Vytvořeno:** ${timestamp}

## WordPress
- **URL:** ${wordpressUrl || 'NENASTAVENO'}
- **Username:** ${wordpressUsername || 'NENASTAVENO'}
- **Application Password:** ${wordpressApplicationPassword ? wordpressApplicationPassword.split(' ').map((s, i) => i < 2 ? s : '****').join(' ') : 'NENASTAVENO'}
- **Vytvořeno:** ${timestamp}

## SSH (pokud je)
- **Host:** ${sshHost || 'NENASTAVENO'}
- **Username:** ${sshUsername || 'NENASTAVENO'}
- **Password:** ${sshPassword ? '********' : 'NENASTAVENO'}
- **Port:** ${sshPort || 'NENASTAVENO'}

## GitHub
- **Repo:** ${githubRepo || 'NENASTAVENO'}
- **Název:** ${githubRepo ? githubRepo.split('/').pop() : 'NENASTAVENO'}

## Projekt
- **Název firmy:** ${projectName || 'NENASTAVENO'}
- **Lokální cesta:** ${accessData.localPath || 'NENASTAVENO'}

---

**POZNÁMKA:** Tento soubor obsahuje citlivé údaje. NIKDY ho necommitni do Git!
`;

  await fs.writeFile(PRÍSTUPY_FILE, content, 'utf-8');
  console.log('✅ Přístupy uloženy do přístupy.md');
  
  // Také vytvořit/aktualizovat config.json
  await createConfigJson(accessData);
}

/**
 * Vytvořit config.json z přístupů
 */
async function createConfigJson(accessData) {
  const configExamplePath = path.join(__dirname, 'config.json.example');
  let config;
  let exampleConfig = null;
  
  try {
    const exampleContent = await fs.readFile(configExamplePath, 'utf-8');
    exampleConfig = JSON.parse(exampleContent);
    config = JSON.parse(exampleContent); // Začít s example jako základ
  } catch (error) {
    // Pokud example neexistuje, vytvořit nový
    config = {
      wordpress: {},
      bricks: {},
      local: {},
      mapping: {}
    };
  }
  
  // Aktualizovat config s údaji
  config.wordpress.url = accessData.wordpressUrl || config.wordpress.url;
  config.wordpress.username = accessData.wordpressUsername || config.wordpress.username;
  config.wordpress.applicationPassword = accessData.wordpressApplicationPassword || config.wordpress.applicationPassword;
  
  // DŮLEŽITÉ: Zachovat bricks sekci z config.json.example (včetně licenseKey)
  // Pokud už není v config, použít z example
  if (!config.bricks) {
    config.bricks = {};
  }
  // Zachovat licenseKey z example, pokud už není v config
  if (exampleConfig?.bricks?.licenseKey && !config.bricks.licenseKey) {
    config.bricks.licenseKey = exampleConfig.bricks.licenseKey;
  }
  // Zachovat pluginZip z example, pokud už není v config
  if (exampleConfig?.bricks?.pluginZip && !config.bricks.pluginZip) {
    config.bricks.pluginZip = exampleConfig.bricks.pluginZip;
  }
  
  // SSH (pokud je)
  if (accessData.sshHost) {
    config.ssh = {
      host: accessData.sshHost,
      username: accessData.sshUsername,
      password: accessData.sshPassword,
      port: accessData.sshPort || 22
    };
  }
  
  // Lokální cesta
  if (accessData.localPath) {
    config.local.projectPath = accessData.localPath;
  }
  
  // Template names
  if (accessData.projectName) {
    config.templateNames = {
      header: `Header ${accessData.projectName}`,
      footer: `Footer ${accessData.projectName}`
    };
  }
  
  await fs.writeFile(CONFIG_FILE, JSON.stringify(config, null, 2), 'utf-8');
  console.log('✅ config.json vytvořen/aktualizován');
}

// Pokud se spustí přímo
if (import.meta.url === `file://${process.argv[1]}`) {
  console.log('💡 Tento skript se používá automaticky AI při získání přístupů');
  console.log('   Použití: import { saveAccess } from "./save-access.js"');
}

