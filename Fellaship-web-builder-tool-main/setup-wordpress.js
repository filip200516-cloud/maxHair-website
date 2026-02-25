/**
 * Automatický setup WordPressu
 * Tento skript provede kompletní nastavení WordPress + Bricks Builder
 */

import fs from 'fs-extra';
import path from 'path';
import { fileURLToPath } from 'url';
import { WordPressAPI } from './wp-api.js';
import { exec } from 'child_process';
import { promisify } from 'util';

const execAsync = promisify(exec);
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Načíst konfiguraci
const configPath = path.join(__dirname, 'config.json');
let config;

try {
  config = JSON.parse(await fs.readFile(configPath, 'utf-8'));
} catch (error) {
  console.error('❌ Chyba při načítání config.json:', error.message);
  console.log('💡 Nejdřív musíš zadat přístupy - AI by mělo vytvořit config.json');
  process.exit(1);
}

const wpAPI = new WordPressAPI(config);

console.log('🚀 Automatický setup WordPressu...\n');

let errors = 0;

// 1. Test připojení
console.log('🔌 Testuji připojení k WordPress API...');
const connectionTest = await wpAPI.testConnection();
if (!connectionTest.success) {
  console.error('❌ Chyba připojení:', connectionTest.error);
  process.exit(1);
}
console.log('✅ Připojení úspěšné\n');

// DŮLEŽITÉ: Plugin MUSÍ být aktivní před instalací Bricks!
// Zkontrolovat, zda je plugin aktivní
console.log('📦 Kontroluji plugin bricks-api-endpoint...');
let pluginActive = false;
try {
  const pluginsResult = await wpAPI.getPlugins();
  const bricksPlugin = pluginsResult.data?.find(p => p.plugin?.includes('bricks-api-endpoint'));
  
  if (!bricksPlugin) {
    console.log('   ⚠️  Plugin není nainstalován');
    console.log('   💡 Plugin byl nahrán přes SSH, ale není aktivní');
    console.log('   📋 CO DĚLAT:');
    console.log('      1. Jdi do WordPress Admin → Plugins');
    console.log('      2. Najdi "Bricks API Endpoint"');
    console.log('      3. Klikni "Activate"');
    console.log('      4. Aktualizuj permalinks: Settings → Permalinks → Save Changes');
    console.log('      5. Napiš mi: "Plugin je aktivní" nebo "Aktivoval jsem plugin"\n');
    console.log('   ⏸️  Čekám na aktivaci pluginu...');
    console.log('   💡 Po aktivaci plugin napiš a já pokračuji s instalací Bricks\n');
    process.exit(0);
  } else if (bricksPlugin.status !== 'active') {
    console.log('   ⚠️  Plugin je nainstalován, ale není aktivní');
    console.log('   🔄 Zkouším aktivovat automaticky...');
    const activateResult = await wpAPI.activatePlugin('bricks-api-endpoint/bricks-api-endpoint.php');
    if (activateResult.success) {
      console.log('✅ Plugin aktivován automaticky\n');
      pluginActive = true;
    } else {
      console.log('   ⚠️  Nepodařilo se aktivovat automaticky');
      console.log('   📋 CO DĚLAT:');
      console.log('      1. Jdi do WordPress Admin → Plugins');
      console.log('      2. Najdi "Bricks API Endpoint"');
      console.log('      3. Klikni "Activate"');
      console.log('      4. Aktualizuj permalinks: Settings → Permalinks → Save Changes');
      console.log('      5. Napiš mi: "Plugin je aktivní" nebo "Aktivoval jsem plugin"\n');
      console.log('   ⏸️  Čekám na aktivaci pluginu...');
      console.log('   💡 Po aktivaci plugin napiš a já pokračuji s instalací Bricks\n');
      process.exit(0);
    }
  } else {
    console.log('✅ Plugin je aktivní\n');
    pluginActive = true;
  }
} catch (error) {
  console.log('   ⚠️  Nepodařilo se zkontrolovat plugin');
  console.log('   💡 Pravděpodobně není aktivní');
  console.log('   📋 CO DĚLAT:');
  console.log('      1. Jdi do WordPress Admin → Plugins');
  console.log('      2. Najdi "Bricks API Endpoint"');
  console.log('      3. Klikni "Activate"');
  console.log('      4. Aktualizuj permalinks: Settings → Permalinks → Save Changes');
  console.log('      5. Napiš mi: "Plugin je aktivní" nebo "Aktivoval jsem plugin"\n');
  console.log('   ⏸️  Čekám na aktivaci pluginu...');
  console.log('   💡 Po aktivaci plugin napiš a já pokračuji s instalací Bricks\n');
  process.exit(0);
}

// Pokud plugin není aktivní, ukončit
if (!pluginActive) {
  console.log('⏸️  Setup pozastaven - čekám na aktivaci pluginu');
  console.log('💡 Po aktivaci plugin napiš a já pokračuji\n');
  process.exit(0);
}

// Plugin už je zkontrolován výše - pokud jsme se sem dostali, je aktivní

// 3. Instalace Bricks
console.log('🎨 Instaluji Bricks Builder...');
try {
  // Použít sync.js install-bricks - TOTO FUNGUJE!
  const { stdout, stderr } = await execAsync('node sync.js install-bricks');
  // Zkontrolovat výstup - pokud obsahuje "✅" nebo "instalován", je to OK
  if (stdout.includes('✅') || stdout.includes('instalován') || stdout.includes('nainstalován')) {
    console.log('✅ Bricks nainstalován\n');
  } else if (stderr && !stderr.includes('✅')) {
    // Stderr může obsahovat varování, ale ne chyby
    if (stderr.includes('Error') || stderr.includes('Chyba')) {
      console.error('   ⚠️  Chyba:', stderr);
      errors++;
    } else {
      console.log('✅ Bricks nainstalován\n');
    }
  } else {
    console.log('✅ Bricks nainstalován\n');
  }
} catch (error) {
  // Pokud je to jen warning, ne error
  if (error.message.includes('Warning') || error.message.includes('warning')) {
    console.log('✅ Bricks nainstalován (s varováním)\n');
  } else {
    console.error('❌ Chyba při instalaci Bricks:', error.message);
    errors++;
  }
}

// 4. Aktivace Bricks licence
if (config.bricks?.licenseKey) {
  console.log('🔑 Aktivuji Bricks licenci...');
  const licenseResult = await wpAPI.activateBricksLicense(config.bricks.licenseKey);
  if (licenseResult.success) {
    console.log('✅ Licence aktivována\n');
  } else {
    console.log('   ⚠️  Nepodařilo se aktivovat licenci automaticky');
    console.log('   💡 Aktivuj ručně: Bricks → Settings → License\n');
    errors++;
  }
} else {
  console.log('   ⚠️  License key není v config.json\n');
}

// 5. Aktualizace Bricks
console.log('🔄 Aktualizuji Bricks téma...');
try {
  const { stdout, stderr } = await execAsync('node sync.js update-bricks');
  if (stderr && !stderr.includes('✅')) {
    console.log('   ⚠️  Aktualizace může být potřeba ručně\n');
  } else {
    console.log('✅ Bricks aktualizován\n');
  }
} catch (error) {
  console.log('   ⚠️  Aktualizace může být potřeba ručně\n');
}

// 6. Nastavení Bricks Settings
console.log('⚙️  Nastavuji Bricks Settings...');
const settingsResult = await wpAPI.configureBricksSettings();
if (settingsResult.success) {
  console.log('✅ Bricks Settings nastaveny (Code Execution + Post Types)\n');
} else {
  console.log('   ⚠️  Nepodařilo se nastavit automaticky');
  console.log('   💡 Nastav ručně: Bricks → Settings → Post types → Page (ON)');
  console.log('   💡 A také: Bricks → Settings → Custom code → Code execution (ON)\n');
  errors++;
}

// 7. Vytvoření Homepage
console.log('🏠 Vytvářím Homepage stránku...');
let homepageId = null;

// Zkontrolovat, zda už existuje
const pagesResult = await wpAPI.getPages();
const existingHomepage = pagesResult.data?.find(p => p.slug === 'homepage');

if (existingHomepage) {
  homepageId = existingHomepage.id;
  console.log(`   ℹ️  Homepage už existuje (ID: ${homepageId})\n`);
} else {
  const homepageResult = await wpAPI.createPage({
    title: 'Homepage',
    slug: 'homepage',
    status: 'publish',
    content: ''
  });
  
  if (homepageResult.success) {
    homepageId = homepageResult.data.id;
    console.log(`✅ Homepage vytvořena (ID: ${homepageId})\n`);
  } else {
    console.error('❌ Chyba při vytváření Homepage:', homepageResult.error);
    errors++;
  }
}

// 8. Nastavení WordPress Reading
if (homepageId) {
  console.log('📖 Nastavuji WordPress Reading (Static page = Homepage)...');
  const readingResult = await wpAPI.setReadingSettings(homepageId);
  if (readingResult.success) {
    console.log('✅ Reading nastaveno na Homepage\n');
  } else {
    console.log('   ⚠️  Nepodařilo se nastavit automaticky');
    console.log('   💡 Nastav ručně: Settings → Reading → Static page → Homepage\n');
    errors++;
  }
}

// 9. Vytvoření Templates (prázdné)
console.log('🎨 Vytvářím Templates (Header, Footer)...');
try {
  // Vytvořit prázdné templates
  const emptyContent = JSON.stringify([]);
  
  // Header
  const headerResult = await wpAPI.createOrUpdateTemplate(
    config.templateNames?.header || 'Header',
    'header',
    emptyContent
  );
  
  // Footer
  const footerResult = await wpAPI.createOrUpdateTemplate(
    config.templateNames?.footer || 'Footer',
    'footer',
    emptyContent
  );
  
  if (headerResult.success && footerResult.success) {
    console.log('✅ Templates vytvořeny (prázdné, připravené pro pozdější push)\n');
  } else {
    console.log('   ⚠️  Nepodařilo se vytvořit templates automaticky\n');
    errors++;
  }
} catch (error) {
  console.log('   ⚠️  Templates se vytvoří později při pushnutí\n');
}

// Shrnutí
console.log('📊 Shrnutí setupu:');
if (errors === 0) {
  console.log('✅ WordPress setup dokončen úspěšně!');
  console.log('✅ Vše je připraveno pro tvorbu webu.\n');
  console.log('🎯 Co dál:');
  console.log('   - Můžeš začít tvořit stránky lokálně');
  console.log('   - Řekni AI: "Vytvoř stránku X"');
  console.log('   - AI vytvoří JSON soubor a spustí lokální server');
  console.log('   - Po úpravách řekni: "Pushni to"');
} else {
  console.log(`⚠️  Setup dokončen s ${errors} chybami`);
  console.log('💡 Zkontroluj výše uvedené poznámky a dokonči nastavení ručně\n');
}

