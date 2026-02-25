import fs from 'fs-extra';
import path from 'path';
import { fileURLToPath } from 'url';
import { WordPressAPI } from './wp-api.js';
import { BricksHandler } from './bricks-handler.js';
import AdmZip from 'adm-zip';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Načíst konfiguraci
const configPath = path.join(__dirname, 'config.json');
let config;

try {
  config = JSON.parse(await fs.readFile(configPath, 'utf-8'));
} catch (error) {
  console.error('❌ Chyba při načítání config.json:', error.message);
  process.exit(1);
}

// Inicializovat API a handler
const wpAPI = new WordPressAPI(config);
const bricksHandler = new BricksHandler(config);

/**
 * Hlavní funkce pro pull (stáhnutí z WordPressu)
 */
async function pull() {
  console.log('📥 Pull: Stahování dat z WordPressu...\n');

  // Test připojení
  console.log('🔌 Testování připojení k WordPress API...');
  const connectionTest = await wpAPI.testConnection();
  
  if (!connectionTest.success) {
    console.error('❌ Chyba připojení:', connectionTest.error);
    if (connectionTest.details) {
      console.error('Detaily:', JSON.stringify(connectionTest.details, null, 2));
    }
    console.log('\n💡 Tip: Zkontrolujte username a password v config.json');
    process.exit(1);
  }
  console.log('✅ Připojení úspěšné\n');

  // Získat všechny stránky
  console.log('📄 Získávám seznam stránek...');
  const pagesResult = await wpAPI.getPages();
  
  if (!pagesResult.success) {
    console.error('❌ Chyba při získávání stránek:', pagesResult.error);
    process.exit(1);
  }

  const pages = pagesResult.data;
  console.log(`✅ Nalezeno ${pages.length} stránek\n`);

  // Pro každou stránku v mapování
  let pulled = 0;
  let errors = 0;

  for (const [slug, fileName] of Object.entries(config.mapping.pages)) {
    console.log(`📥 Stahuji: ${slug}...`);
    
    // Najít stránku podle slug
    const page = pages.find(p => p.slug === slug);
    
    if (!page) {
      console.log(`⚠️  Stránka "${slug}" nenalezena na WordPressu, přeskočeno`);
      continue;
    }

    // Získat Bricks obsah
    // POZNÁMKA: WordPress REST API standardně nevrací custom meta fields
    // Budeme potřebovat buď custom endpoint, nebo přístup k databázi
    const metaResult = await wpAPI.getBricksContent(page.id, config.bricksMetaKey);
    
    if (!metaResult.success) {
      console.log(`⚠️  Bricks obsah pro "${slug}" nelze získat přes API: ${metaResult.error}`);
      console.log(`   Stránka ID: ${page.id}, Slug: ${page.slug}`);
      console.log(`   💡 Možná je potřeba custom endpoint nebo databázový přístup\n`);
      errors++;
      continue;
    }

    // Uložit do lokálního souboru
    const filePath = bricksHandler.getPageFilePath(slug);
    const saveResult = await bricksHandler.saveJSONFile(filePath, metaResult.data);

    if (saveResult.success) {
      console.log(`✅ Uloženo: ${filePath}\n`);
      pulled++;
    } else {
      console.error(`❌ Chyba při ukládání: ${saveResult.error}\n`);
      errors++;
    }
  }

  console.log('\n📊 Shrnutí:');
  console.log(`   ✅ Staženo: ${pulled}`);
  console.log(`   ❌ Chyby: ${errors}`);
}

/**
 * Hlavní funkce pro push (nahrání do WordPressu)
 */
async function push() {
  console.log('📤 Push: Nahrávání dat do WordPressu...\n');

  // Test připojení
  console.log('🔌 Testování připojení k WordPress API...');
  const connectionTest = await wpAPI.testConnection();
  
  if (!connectionTest.success) {
    console.error('❌ Chyba připojení:', connectionTest.error);
    if (connectionTest.details) {
      console.error('Detaily:', JSON.stringify(connectionTest.details, null, 2));
    }
    console.log('\n💡 Tip: Zkontrolujte username a password v config.json');
    process.exit(1);
  }
  console.log('✅ Připojení úspěšné\n');

  // Načíst všechny lokální stránky
  console.log('📂 Načítám lokální soubory...');
  const localPagesResult = await bricksHandler.getAllLocalPages();
  
  if (!localPagesResult.success) {
    console.error('❌ Chyba při načítání lokálních souborů:', localPagesResult.error);
    process.exit(1);
  }

  const localPages = localPagesResult.data;
  console.log(`✅ Načteno ${Object.keys(localPages).length} lokálních stránek\n`);

  // Získat všechny WordPress stránky
  const pagesResult = await wpAPI.getPages();
  if (!pagesResult.success) {
    console.error('❌ Chyba při získávání WordPress stránek:', pagesResult.error);
    if (pagesResult.details) {
      console.error('Detaily chyby:', JSON.stringify(pagesResult.details, null, 2));
    }
    process.exit(1);
  }

  const wpPages = pagesResult.data;
  const wpPagesBySlug = {};
  wpPages.forEach(page => {
    wpPagesBySlug[page.slug] = page;
  });

  // Push každé lokální stránky
  let pushed = 0;
  let created = 0;
  let errors = 0;

  for (const [slug, localPage] of Object.entries(localPages)) {
    console.log(`📤 Nahrávám: ${slug}...`);

    let wpPage = wpPagesBySlug[slug];

    // Pokud stránka neexistuje, vytvořit ji
    if (!wpPage) {
      console.log(`   ⚠️  Stránka "${slug}" neexistuje, vytvářím...`);
      
      const createResult = await wpAPI.createPage({
        title: slug.charAt(0).toUpperCase() + slug.slice(1).replace(/-/g, ' '),
        slug: slug,
        status: 'publish',
        content: ''
      });

      if (!createResult.success) {
        console.error(`   ❌ Chyba při vytváření stránky: ${createResult.error}`);
        if (createResult.details) {
          console.error(`   Detaily: ${JSON.stringify(createResult.details, null, 2)}`);
        }
        console.log(`   💡 Možná je potřeba Application Password místo standardního hesla\n`);
        errors++;
        continue;
      }

      wpPage = createResult.data;
      created++;
      console.log(`   ✅ Stránka vytvořena (ID: ${wpPage.id})`);
    }

    // Připravit Bricks data pro meta
    const bricksContent = bricksHandler.prepareBricksForMeta(localPage.data);

    // Aktualizovat Bricks obsah
    const updateResult = await wpAPI.updateBricksContent(
      wpPage.id, 
      bricksContent, 
      config.bricksMetaKey
    );

    if (!updateResult.success) {
      console.error(`   ❌ Chyba při aktualizaci Bricks obsahu: ${updateResult.error}`);
      if (updateResult.note) {
        console.log(`   💡 ${updateResult.note}`);
      }
      console.log(`   💡 Možná je potřeba custom endpoint nebo databázový přístup\n`);
      errors++;
      continue;
    }

    // DŮLEŽITÉ: Regenerovat code signatures pro všechny code elementy
    console.log(`   🔐 Regeneruji podpisy kódu...`);
    const signatureResult = await wpAPI.regenerateSignatures(wpPage.id);
    if (signatureResult.success) {
      const sigCount = signatureResult.data?.signatures_regenerated || 0;
      if (sigCount > 0) {
        console.log(`   ✅ Podepsáno ${sigCount} code elementů`);
      }
    } else {
      console.log(`   ⚠️  Varování: Nepodařilo se regenerovat podpisy: ${signatureResult.error}`);
      console.log(`   💡 Podpisy budou regenerovány při otevření stránky v Bricks editoru`);
    }

    console.log(`   ✅ Aktualizováno (ID: ${wpPage.id})\n`);
    pushed++;
  }

  console.log('\n📊 Shrnutí:');
  console.log(`   ✅ Nahráno: ${pushed}`);
  console.log(`   🆕 Vytvořeno: ${created}`);
  console.log(`   ❌ Chyby: ${errors}`);
}

/**
 * Push templates (Header a Footer)
 */
async function pushTemplates() {
  console.log('🎨 Push: Nahrávání Templates (Header/Footer)...\n');

  // Test připojení
  console.log('🔌 Testování připojení k WordPress API...');
  const connectionTest = await wpAPI.testConnection();
  
  if (!connectionTest.success) {
    console.error('❌ Chyba připojení:', connectionTest.error);
    process.exit(1);
  }
  console.log('✅ Připojení úspěšné\n');

  // Získat existující templates
  const templatesResult = await wpAPI.getTemplates();
  const existingTemplates = templatesResult.success ? templatesResult.data : [];
  const templatesByType = {};
  existingTemplates.forEach(t => {
    if (t.type) {
      templatesByType[t.type] = t;
    }
  });

  let pushed = 0;
  let created = 0;
  let errors = 0;

  // Push Header
  if (config.mapping.components.header) {
    console.log('📤 Nahrávám Header...');
    const headerPath = bricksHandler.getComponentFilePath('header');
    
    if (await fs.pathExists(headerPath)) {
      const headerData = await bricksHandler.loadJSONFile(headerPath);
      if (headerData.success) {
        const existingHeader = templatesByType['header'];
        const headerContent = bricksHandler.prepareBricksForMeta(headerData.data);
        
        // Název template získáme z config nebo použijeme default
        const headerTitle = config.templateNames?.header || `Header ${config.wordpress.url.split('//')[1]?.split('.')[0] || 'Site'}`;
        
        // Pokud template existuje, použít nový endpoint /template/{id}/content (stejně jako pages)
        if (existingHeader) {
          try {
            const updateResult = await wpAPI.client.post(
              `/bricks/v1/template/${existingHeader.id}/content`,
              { content: headerContent },
              {
                baseURL: wpAPI.baseURL + '/wp-json'
              }
            );
            
            if (updateResult.data && updateResult.data.success) {
              console.log(`   ✅ Header aktualizován (ID: ${existingHeader.id})`);
              
              // Podepsat code elementy
              console.log(`   🔐 Regeneruji podpisy kódu...`);
              const signatureResult = await wpAPI.regenerateSignatures(existingHeader.id);
              if (signatureResult.success) {
                const sigCount = signatureResult.data?.signatures_regenerated || 0;
                if (sigCount > 0) {
                  console.log(`   ✅ Podepsáno ${sigCount} code elementů`);
                }
              }
              
              pushed++;
            } else {
              throw new Error('Update failed');
            }
          } catch (endpointError) {
            // Pokud nový endpoint selhal, použít starý způsob
            console.log('   ⚠️  Nový endpoint selhal, používám starý způsob...');
            
            // Použít starý způsob (createOrUpdateTemplate)
            const result = await wpAPI.createOrUpdateTemplate(
              headerTitle,
              'header',
              headerContent,
              existingHeader.id
            );
            
            if (result.success) {
              const templateId = result.data.id || result.data.template_id || existingHeader.id;
              console.log(`   ✅ Header aktualizován (ID: ${templateId})`);
              
              // Podepsat code elementy
              console.log(`   🔐 Regeneruji podpisy kódu...`);
              const signatureResult = await wpAPI.regenerateSignatures(templateId);
              if (signatureResult.success) {
                const sigCount = signatureResult.data?.signatures_regenerated || 0;
                if (sigCount > 0) {
                  console.log(`   ✅ Podepsáno ${sigCount} code elementů`);
                }
              }
              
              pushed++;
            } else {
              console.error(`   ❌ Chyba: ${result.error}\n`);
              errors++;
            }
          }
        } else {
          // Template neexistuje - vytvořit ho
          const result = await wpAPI.createOrUpdateTemplate(
            headerTitle,
            'header',
            headerContent,
            null
          );

          if (result.success) {
            const templateId = result.data.id || result.data.template_id;
            if (!templateId) {
              console.error(`   ❌ Chyba: Nelze získat ID template`);
              errors++;
            } else {
              console.log(`   ✅ Header vytvořen (ID: ${templateId})`);
              
              // Podepsat code elementy
              console.log(`   🔐 Regeneruji podpisy kódu...`);
              const signatureResult = await wpAPI.regenerateSignatures(templateId);
              if (signatureResult.success) {
                const sigCount = signatureResult.data?.signatures_regenerated || 0;
                if (sigCount > 0) {
                  console.log(`   ✅ Podepsáno ${sigCount} code elementů`);
                }
              }
              
              created++;
            }
          } else {
            console.error(`   ❌ Chyba: ${result.error}\n`);
            errors++;
          }
        }
      } else {
        console.error(`   ❌ Chyba při načítání: ${headerData.error}\n`);
        errors++;
      }
    } else {
      console.log(`   ⚠️  Soubor nenalezen: ${headerPath}\n`);
    }
  }

  // Push Footer
  if (config.mapping.components.footer) {
    console.log('📤 Nahrávám Footer...');
    const footerPath = bricksHandler.getComponentFilePath('footer');
    
    if (await fs.pathExists(footerPath)) {
      const footerData = await bricksHandler.loadJSONFile(footerPath);
      if (footerData.success) {
        const existingFooter = templatesByType['footer'];
        const footerContent = bricksHandler.prepareBricksForMeta(footerData.data);
        
        // Název template získáme z config nebo použijeme default
        const footerTitle = config.templateNames?.footer || `Footer ${config.wordpress.url.split('//')[1]?.split('.')[0] || 'Site'}`;
        
        // Pokud template existuje, použít nový endpoint /template/{id}/content (stejně jako pages)
        if (existingFooter) {
          try {
            const updateResult = await wpAPI.client.post(
              `/bricks/v1/template/${existingFooter.id}/content`,
              { content: footerContent },
              {
                baseURL: wpAPI.baseURL + '/wp-json'
              }
            );
            
            if (updateResult.data && updateResult.data.success) {
              console.log(`   ✅ Footer aktualizován (ID: ${existingFooter.id})`);
              
              // Podepsat code elementy
              console.log(`   🔐 Regeneruji podpisy kódu...`);
              const signatureResult = await wpAPI.regenerateSignatures(existingFooter.id);
              if (signatureResult.success) {
                const sigCount = signatureResult.data?.signatures_regenerated || 0;
                if (sigCount > 0) {
                  console.log(`   ✅ Podepsáno ${sigCount} code elementů`);
                }
              }
              
              pushed++;
            } else {
              throw new Error('Update failed');
            }
          } catch (endpointError) {
            // Pokud nový endpoint selhal, použít starý způsob
            console.log('   ⚠️  Nový endpoint selhal, používám starý způsob...');
            
            const result = await wpAPI.createOrUpdateTemplate(
              footerTitle,
              'footer',
              footerContent,
              existingFooter.id
            );
            
            if (result.success) {
              const templateId = result.data.id || result.data.template_id || existingFooter.id;
              console.log(`   ✅ Footer aktualizován (ID: ${templateId})`);
              
              // Podepsat code elementy
              console.log(`   🔐 Regeneruji podpisy kódu...`);
              const signatureResult = await wpAPI.regenerateSignatures(templateId);
              if (signatureResult.success) {
                const sigCount = signatureResult.data?.signatures_regenerated || 0;
                if (sigCount > 0) {
                  console.log(`   ✅ Podepsáno ${sigCount} code elementů`);
                }
              }
              
              pushed++;
            } else {
              console.error(`   ❌ Chyba: ${result.error}\n`);
              errors++;
            }
          }
        } else {
          // Template neexistuje - vytvořit ho
          const result = await wpAPI.createOrUpdateTemplate(
            footerTitle,
            'footer',
            footerContent,
            null
          );

          if (result.success) {
            const templateId = result.data.id || result.data.template_id;
            if (!templateId) {
              console.error(`   ❌ Chyba: Nelze získat ID template`);
              errors++;
            } else {
              console.log(`   ✅ Footer vytvořen (ID: ${templateId})`);
              
              // Podepsat code elementy
              console.log(`   🔐 Regeneruji podpisy kódu...`);
              const signatureResult = await wpAPI.regenerateSignatures(templateId);
              if (signatureResult.success) {
                const sigCount = signatureResult.data?.signatures_regenerated || 0;
                if (sigCount > 0) {
                  console.log(`   ✅ Podepsáno ${sigCount} code elementů`);
                }
              }
              
              created++;
            }
          } else {
            console.error(`   ❌ Chyba: ${result.error}\n`);
            errors++;
          }
        }
      } else {
        console.error(`   ❌ Chyba při načítání: ${footerData.error}\n`);
        errors++;
      }
    } else {
      console.log(`   ⚠️  Soubor nenalezen: ${footerPath}\n`);
    }
  }

  console.log('\n📊 Shrnutí Templates:');
  console.log(`   ✅ Aktualizováno: ${pushed}`);
  console.log(`   🆕 Vytvořeno: ${created}`);
  console.log(`   ❌ Chyby: ${errors}`);
}

/**
 * Instalace Bricks pluginu
 */
async function installBricks() {
  console.log('🔧 Instalace Bricks Builder...\n');

  // DŮLEŽITÉ: Bricks je TÉMA, ne plugin!
  // Zkontrolovat, zda je Bricks téma již nainstalováno
  console.log('🔍 Kontroluji, zda je Bricks téma již nainstalováno...');
  const themesResult = await wpAPI.getThemes();
  
  let bricksTheme = null;
  
  if (!themesResult.success) {
    console.log('   ⚠️  Nepodařilo se zkontrolovat přes API:', themesResult.error);
    console.log('   💡 Pokračuji s instalací - pokud je téma už nainstalováno, instalace to zjistí\n');
  } else {
    // Hledat Bricks téma podle slug, stylesheet nebo názvu
    bricksTheme = themesResult.data?.find(t => 
      t.slug === 'bricks' || 
      t.stylesheet === 'bricks' ||
      t.name?.toLowerCase().includes('bricks')
    );
    
    if (bricksTheme) {
      console.log('   ✅ Bricks téma nalezeno:', bricksTheme.name || bricksTheme.slug || 'N/A');
      console.log('   Slug:', bricksTheme.slug || 'N/A');
      console.log('   Stylesheet:', bricksTheme.stylesheet || 'N/A');
    }
  }
  
  if (bricksTheme) {
    console.log('✅ Bricks téma je již nainstalováno');
    console.log('   Téma:', bricksTheme.name || bricksTheme.slug || 'N/A');
    
    // Zkontrolovat, zda je aktivní
    if (bricksTheme.active) {
      console.log('✅ Bricks téma je aktivní\n');
    } else {
      console.log('⚠️  Bricks téma není aktivní, aktivuji...');
      const activateResult = await wpAPI.activateTheme('bricks');
      if (activateResult.success) {
        console.log('✅ Bricks téma aktivováno\n');
      } else {
        console.error('❌ Chyba při aktivaci:', activateResult.error);
      }
    }
  } else {
    // Nainstalovat téma
    // Zkontrolovat, zda je cesta absolutní nebo relativní
    let zipPath;
    if (path.isAbsolute(config.bricks.pluginZip)) {
      zipPath = config.bricks.pluginZip;
    } else {
      // Zkusit najít v různých místech (dynamicky)
      const possiblePaths = [
        path.join(__dirname, config.bricks.pluginZip), // Root složka toolu
        path.join(process.cwd(), config.bricks.pluginZip), // Aktuální working directory
        path.resolve(config.bricks.pluginZip) // Absolutní cesta pokud je relativní
      ];
      
      zipPath = null;
      for (const possiblePath of possiblePaths) {
        if (await fs.pathExists(possiblePath)) {
          zipPath = possiblePath;
          console.log(`   📍 Nalezen ZIP soubor: ${zipPath}`);
          break;
        }
      }
    }
    
    if (!zipPath || !await fs.pathExists(zipPath)) {
      console.error(`❌ ZIP soubor nenalezen: ${config.bricks.pluginZip}`);
      console.log('\n💡 Hledal jsem v těchto místech:');
      const searchPaths = [
        path.join(__dirname, config.bricks.pluginZip),
        path.join(process.cwd(), config.bricks.pluginZip)
      ];
      searchPaths.forEach(p => console.log(`   - ${p}`));
      console.log('\n💡 Alternativní metody instalace:');
      console.log('   1. Zkontrolujte, zda je bricks.2.0.zip v root složce toolu');
      console.log('   2. Nebo nainstalujte Bricks ručně přes WordPress admin → Appearance → Themes → Add New → Upload Theme');
      console.log('   3. Nebo použijte FTP/SFTP přístup');
      process.exit(1);
    }

    console.log(`📦 Instaluji Bricks TÉMA ze souboru: ${zipPath}...`);
    
    const installResult = await wpAPI.installTheme(zipPath);
    
    if (!installResult.success) {
      console.error('❌ Chyba při instalaci:', installResult.error);
      if (installResult.details) {
        console.error('   Detaily:', JSON.stringify(installResult.details, null, 2));
      }
      if (installResult.note) {
        console.log('💡', installResult.note);
      }
      console.log('\n💡 Alternativní metody instalace:');
      console.log('   1. Nainstalujte Bricks ručně přes WordPress admin → Appearance → Themes → Add New → Upload Theme');
      console.log('   2. Nebo použijte FTP/SFTP přístup');
      console.log('   3. Zkontrolujte, zda je bricks-api-endpoint.php plugin aktivní');
      console.log('   4. Zkontrolujte, zda jsou permalinks aktualizované: Settings → Permalinks → Save Changes');
      process.exit(1);
    }

    console.log('✅ Bricks téma nainstalováno');

    // Aktivovat téma
    console.log('🔄 Aktivuji Bricks téma...');
    const activateResult = await wpAPI.activateTheme('bricks');
    
    if (activateResult.success) {
      console.log('✅ Bricks téma aktivováno\n');
    } else {
      console.error('❌ Chyba při aktivaci:', activateResult.error);
      console.log('💡 Aktivuj ručně: Appearance → Themes → Bricks → Activate\n');
    }
  }

  // Aktivovat licenci (použít activateBricksLicense, ne updateBricksLicense!)
  if (config.bricks?.licenseKey) {
    console.log('🔑 Aktivuji Bricks licenci...');
    const licenseResult = await wpAPI.activateBricksLicense(config.bricks.licenseKey);
    
    if (licenseResult.success) {
      console.log('✅ Licence aktivována\n');
    } else {
      console.error('⚠️  Chyba při aktivaci licence:', licenseResult.error);
      if (licenseResult.note) {
        console.log('💡', licenseResult.note);
      }
      console.log('💡 Aktivuj licenci ručně: Bricks → Settings → License\n');
    }
  } else {
    console.log('   ⚠️  License key není v config.json\n');
  }
}

/**
 * Aktualizovat Bricks téma
 */
async function updateBricks() {
  console.log('🔄 Aktualizace Bricks tématu...\n');

  // Test připojení
  console.log('🔌 Testování připojení k WordPress API...');
  const connectionTest = await wpAPI.testConnection();
  
  if (!connectionTest.success) {
    console.error('❌ Chyba připojení:', connectionTest.error);
    process.exit(1);
  }
  console.log('✅ Připojení úspěšné\n');

  // Zkontrolovat, zda je bricks-api-endpoint plugin aktivní
  console.log('🔍 Kontroluji bricks-api-endpoint plugin...');
  try {
    const pluginCheck = await wpAPI.client.get('/bricks/v1/themes', {
      baseURL: wpAPI.baseURL + '/wp-json'
    });
    console.log('✅ Plugin je aktivní\n');
  } catch (error) {
    if (error.response?.status === 404) {
      console.log('⚠️  Plugin bricks-api-endpoint není aktivní nebo permalinks nejsou aktualizovány');
      console.log('💡 Aktualizujte Bricks téma ručně přes WordPress admin:');
      console.log('   Appearance → Themes → Klikněte na "Update now" u Bricks tématu\n');
      return;
    }
  }

  // Získat seznam témat
  console.log('📋 Kontroluji dostupné aktualizace...');
  const themesResult = await wpAPI.getThemes();
  
  if (!themesResult.success) {
    console.error('❌ Chyba při získávání témat:', themesResult.error);
    process.exit(1);
  }

  const themes = themesResult.data;
  const bricksTheme = themes.find(t => t.slug === 'bricks' || t.name.toLowerCase().includes('bricks'));
  
  if (!bricksTheme) {
    console.error('❌ Bricks téma nenalezeno');
    process.exit(1);
  }

  console.log(`📦 Aktuální verze: ${bricksTheme.version}`);
  
  if (bricksTheme.update_available) {
    console.log(`🆕 Dostupné aktualizace: ${bricksTheme.update_version}`);
    console.log(`\n🔄 Aktualizuji Bricks téma...`);
    
    const updateResult = await wpAPI.updateTheme('bricks');
    
    if (updateResult.success) {
      console.log(`✅ Bricks téma úspěšně aktualizováno!`);
      console.log(`   Nová verze: ${updateResult.data.new_version}`);
    } else {
      console.error(`❌ Chyba při aktualizaci: ${updateResult.error}`);
      if (updateResult.details) {
        console.error(`   Detaily: ${JSON.stringify(updateResult.details, null, 2)}`);
      }
      process.exit(1);
    }
  } else {
    // Pokud není detekována aktualizace, ale endpoint funguje, zkusit aktualizaci stejně
    // (WordPress API někdy nehlásí aktualizace správně, ale endpoint je může provést)
    const forceUpdate = process.argv.includes('--force');
    
    if (forceUpdate) {
      console.log('⚠️  Aktualizace není detekována API, ale vynucuji aktualizaci...');
    } else {
      console.log('⚠️  Aktualizace není detekována API, ale zkouším aktualizaci stejně...');
      console.log('   (WordPress API někdy nehlásí aktualizace správně, ale endpoint je může provést)');
    }
    
    console.log(`\n🔄 Aktualizuji Bricks téma...`);
    const updateResult = await wpAPI.updateTheme('bricks');
    
    if (updateResult.success) {
      const newVersion = updateResult.data.new_version || updateResult.data.version;
      if (newVersion && newVersion !== bricksTheme.version) {
        console.log(`✅ Bricks téma úspěšně aktualizováno!`);
        console.log(`   Stará verze: ${bricksTheme.version}`);
        console.log(`   Nová verze: ${newVersion}`);
      } else {
        console.log(`✅ Aktualizace dokončena (verze: ${bricksTheme.version})`);
        console.log(`   💡 Téma je již na nejnovější verzi nebo aktualizace proběhla`);
      }
    } else {
      console.error(`❌ Chyba při aktualizaci: ${updateResult.error}`);
      if (updateResult.details) {
        console.error(`   Detaily: ${JSON.stringify(updateResult.details, null, 2)}`);
      }
      if (updateResult.note) {
        console.log(`   💡 ${updateResult.note}`);
      }
      console.log('\n💡 Možná řešení:');
      console.log('   1. Zkontrolujte, zda je bricks-api-endpoint.php plugin aktivní');
      console.log('   2. Aktualizujte permalinks: Settings → Permalinks → Save Changes');
      console.log('   3. Nebo aktualizujte ručně přes WordPress admin: Appearance → Themes');
      
      // Pokud to není force update, neukončit s chybou (možná je téma už aktualizované)
      if (forceUpdate) {
        process.exit(1);
      }
    }
  }
}

/**
 * Smazat všechny stránky, templates a Bricks
 */
async function cleanAll() {
  console.log('🗑️  Mazání všech stránek, templates a Bricks...\n');

  // Test připojení
  console.log('🔌 Testování připojení k WordPress API...');
  const connectionTest = await wpAPI.testConnection();
  
  if (!connectionTest.success) {
    console.error('❌ Chyba připojení:', connectionTest.error);
    process.exit(1);
  }
  console.log('✅ Připojení úspěšné\n');

  // Zkontrolovat, zda je bricks-api-endpoint plugin aktivní
  console.log('🔍 Kontroluji dostupnost Bricks API endpointů...');
  try {
    const testResponse = await wpAPI.client.get('/bricks/v1/templates', {
      baseURL: wpAPI.baseURL + '/wp-json',
      params: { per_page: 1 }
    });
    console.log('✅ Bricks API endpointy jsou dostupné\n');
  } catch (error) {
    console.log('⚠️  Bricks API endpointy nejsou dostupné (404)');
    console.log('   To může znamenat, že plugin bricks-api-endpoint není aktivní');
    console.log('   nebo permalinks nejsou aktualizované.\n');
    console.log('💡 Řešení:');
    console.log('   1. Zkontrolujte, zda je plugin "Bricks API Endpoint" aktivní v WordPress adminu');
    console.log('   2. Aktualizujte permalinks: Settings → Permalinks → Save Changes');
    console.log('   3. Templates bude potřeba smazat ručně přes WordPress admin\n');
  }

  let deletedPages = 0;
  let deletedTemplates = 0;
  let errors = 0;

  // 1. Smazat všechny stránky s Bricks obsahem
  console.log('📄 Mazání stránek...');
  const pagesResult = await wpAPI.getPages();
  
  if (pagesResult.success) {
    const pages = pagesResult.data;
    console.log(`   Nalezeno ${pages.length} stránek`);
    
    for (const page of pages) {
      const pageTitle = page.title?.rendered || page.title || 'Bez názvu';
      console.log(`   🗑️  Mažu stránku: ${pageTitle} (ID: ${page.id})...`);
      const deleteResult = await wpAPI.deletePage(page.id, true);
      
      if (deleteResult.success) {
        console.log(`   ✅ Smazáno`);
        deletedPages++;
      } else {
        console.error(`   ❌ Chyba: ${deleteResult.error}`);
        errors++;
      }
    }
  } else {
    console.error(`   ❌ Chyba při získávání stránek: ${pagesResult.error}`);
    errors++;
  }

  // 2. Smazat všechny templates
  console.log('\n🎨 Mazání templates...');
  const templatesResult = await wpAPI.getTemplates();
  
  if (templatesResult.success) {
    const templates = templatesResult.data;
    console.log(`   Nalezeno ${templates.length} templates`);
    
    // Zkusit hromadné smazání
    if (templates.length > 0) {
      const templateIds = templates.map(t => t.id);
      console.log(`   🗑️  Mažu ${templates.length} templates hromadně...`);
      const bulkResult = await wpAPI.deleteTemplatesBulk(templateIds, true);
      
      if (bulkResult.success && bulkResult.data.deleted_count > 0) {
        console.log(`   ✅ Smazáno ${bulkResult.data.deleted_count} templates`);
        deletedTemplates = bulkResult.data.deleted_count;
        
        if (bulkResult.data.failed_count > 0) {
          console.log(`   ⚠️  ${bulkResult.data.failed_count} templates se nepodařilo smazat`);
          errors += bulkResult.data.failed_count;
        }
      } else {
        console.log(`   ⚠️  Hromadné smazání selhalo, zkouším jednotlivě...`);
        // Pokračovat s jednotlivým mazáním
      }
    }
    
    // Pokud hromadné smazání selhalo, zkusit jednotlivě
    if (deletedTemplates === 0) {
      for (const template of templates) {
        console.log(`   🗑️  Mažu template: ${template.title} (ID: ${template.id}, Type: ${template.type})...`);
        
        let deleteResult = { success: false };
        
        // Metoda 1: Zkusit přes custom endpoint (DELETE)
        try {
          deleteResult = await wpAPI.deleteTemplate(template.id, true);
          if (deleteResult.success) {
            console.log(`   ✅ Smazáno (custom endpoint)`);
            deletedTemplates++;
            continue;
          }
        } catch (e) {
          // Pokračovat k další metodě
        }
        
        // Metoda 2: Zkusit přes custom endpoint (POST)
        try {
          const response = await wpAPI.client.post(
            `/bricks/v1/template/${template.id}/delete`,
            { force: true },
            {
              baseURL: wpAPI.baseURL + '/wp-json'
            }
          );
          deleteResult = { success: true, data: response.data };
          if (deleteResult.success) {
            console.log(`   ✅ Smazáno (POST endpoint)`);
            deletedTemplates++;
            continue;
          }
        } catch (e) {
          // Pokračovat k další metodě
        }
        
        // Metoda 3: Zkusit přes WordPress REST API pro custom post type (pokud je registrován)
        try {
          const response = await wpAPI.client.delete(`/bricks_template/${template.id}`, {
            params: { force: true }
          });
          deleteResult = { success: true, data: response.data };
          if (deleteResult.success) {
            console.log(`   ✅ Smazáno (REST API)`);
            deletedTemplates++;
            continue;
          }
        } catch (e) {
          // Pokračovat k další metodě
        }
        
        // Metoda 4: Zkusit přes alternativní jednodušší endpoint
        try {
          const response = await wpAPI.client.post(
            `/bricks/v1/delete-template`,
            { id: template.id, force: true },
            {
              baseURL: wpAPI.baseURL + '/wp-json'
            }
          );
          deleteResult = { success: true, data: response.data };
          if (deleteResult.success) {
            console.log(`   ✅ Smazáno (alternativní endpoint)`);
            deletedTemplates++;
            continue;
          }
        } catch (e) {
          // Všechny metody selhaly
        }
        
        // Pokud všechny metody selhaly
        if (!deleteResult.success) {
          console.error(`   ❌ Všechny metody selhaly`);
          if (deleteResult.details) {
            console.error(`   Detaily: ${JSON.stringify(deleteResult.details, null, 2)}`);
          }
          console.log(`   💡 Zkuste smazat ručně přes WordPress admin: Templates → ${template.title} (ID: ${template.id})`);
          console.log(`   💡 Nebo zkontrolujte, zda je plugin 'bricks-api-endpoint' aktivní`);
          errors++;
        }
      }
    }
  } else {
    console.error(`   ❌ Chyba při získávání templates: ${templatesResult.error}`);
    errors++;
  }

  console.log('\n📊 Shrnutí:');
  console.log(`   ✅ Smazáno stránek: ${deletedPages}`);
  console.log(`   ✅ Smazáno templates: ${deletedTemplates}`);
  console.log(`   ❌ Chyby: ${errors}`);
  console.log('\n⚠️  POZNÁMKA: Bricks téma zůstává nainstalované.');
  console.log('   Pokud chcete smazat i Bricks téma, udělejte to ručně přes WordPress admin.');
}

/**
 * Odstranit Bricks téma a aktivovat základní WordPress téma
 */
async function removeBricksTheme() {
  console.log('🔄 Odstraňuji Bricks téma a aktivuji základní WordPress téma...\n');

  // Test připojení
  console.log('🔌 Testování připojení k WordPress API...');
  const connectionTest = await wpAPI.testConnection();
  
  if (!connectionTest.success) {
    console.error('❌ Chyba připojení:', connectionTest.error);
    process.exit(1);
  }
  console.log('✅ Připojení úspěšné\n');

  // 1. Získat seznam nainstalovaných témat
  console.log('📋 Získávám seznam nainstalovaných témat...');
  const themesResult = await wpAPI.getThemes();
  
  if (!themesResult.success) {
    console.error('❌ Chyba při získávání témat:', themesResult.error);
    process.exit(1);
  }

  const themes = themesResult.data;
  console.log(`   Nalezeno ${themes.length} témat\n`);

  // 2. Najít aktivní téma a základní WordPress téma
  let activeTheme = null;
  let defaultTheme = null;
  const defaultThemeNames = ['twentytwentyfour', 'twentytwentythree', 'twentytwentytwo', 'twentytwentyone', 'twentytwenty'];

  for (const theme of themes) {
    if (theme.active) {
      activeTheme = theme;
      console.log(`   🎨 Aktivní téma: ${theme.name} (${theme.slug})`);
    }
    
    // Najít první dostupné základní WordPress téma
    if (!defaultTheme && defaultThemeNames.includes(theme.slug)) {
      defaultTheme = theme;
    }
  }

  // Pokud není žádné základní téma, použít první dostupné (kromě Bricks)
  if (!defaultTheme) {
    for (const theme of themes) {
      if (theme.slug !== 'bricks' && !theme.active) {
        defaultTheme = theme;
        break;
      }
    }
  }

  if (!defaultTheme) {
    console.error('❌ Nenalezeno žádné základní WordPress téma k aktivaci!');
    console.log('   💡 Nainstalujte základní WordPress téma (např. Twenty Twenty-Four)');
    process.exit(1);
  }

  console.log(`   📌 Základní téma k aktivaci: ${defaultTheme.name} (${defaultTheme.slug})\n`);

  // 3. Pokud je aktivní Bricks, aktivovat základní téma
  if (activeTheme && activeTheme.slug === 'bricks') {
    console.log('🔄 Aktivuji základní WordPress téma...');
    const activateResult = await wpAPI.activateTheme(defaultTheme.slug);
    
    if (!activateResult.success) {
      console.error('❌ Chyba při aktivaci tématu:', activateResult.error);
      if (activateResult.details) {
        console.error('   Detaily:', JSON.stringify(activateResult.details, null, 2));
      }
      process.exit(1);
    }
    
    console.log(`✅ Téma ${defaultTheme.name} aktivováno\n`);
  } else {
    console.log(`ℹ️  Aktivní téma není Bricks (je to: ${activeTheme?.name || 'neznámé'})\n`);
  }

  // 4. Smazat Bricks téma
  console.log('\n🗑️  Mažu Bricks téma...');
  let deleteResult = await wpAPI.deleteTheme('bricks');
  
  if (!deleteResult.success) {
    console.log('   ⚠️  Endpoint pro smazání není dostupný (plugin není aktualizovaný)');
    console.log('   💡 Aktualizuji plugin a zkusím znovu...\n');
    
    // Zkusit použít WordPress REST API pro přímé volání delete_theme funkce
    // Nebo použít alternativní endpoint, který už existuje
    console.log('   📋 INSTRUKCE PRO RUČNÍ MAZÁNÍ:');
    console.log('   1. Otevřete WordPress admin: ' + wpAPI.baseURL + '/wp-admin');
    console.log('   2. Appearance → Themes');
    console.log('   3. Najděte Bricks téma a klikněte na "Theme Details"');
    console.log('   4. Klikněte na červené tlačítko "Delete"');
    console.log('\n   NEBO aktualizujte plugin a zkuste znovu:');
    console.log('   1. Přes Hostinger hPanel: Files → File Manager');
    console.log('   2. Přejděte do: public_html/wp-content/plugins/bricks-api-endpoint/');
    console.log('   3. Nahrajte aktualizovaný soubor: bricks-api-endpoint.php');
    console.log('   4. Spusťte znovu: node sync.js remove-bricks');
    
    process.exit(1);
  }

  console.log('✅ Bricks téma úspěšně smazáno');
  console.log('\n📊 Shrnutí:');
  console.log(`   ✅ Aktivní téma: ${defaultTheme.name}`);
  console.log(`   ✅ Bricks téma: smazáno`);
  console.log('\n🎉 Hotovo! WordPress nyní používá základní téma.');
}

/**
 * Setup - inicializace a testování
 */
async function setup() {
  console.log('⚙️  Setup: Inicializace a testování...\n');

  // Zkontrolovat konfiguraci
  console.log('📋 Kontroluji konfiguraci...');
  
  if (!config.wordpress.username || (!config.wordpress.password && !config.wordpress.applicationPassword)) {
    console.error('❌ Chyba: Username nebo password/applicationPassword není nastaven v config.json');
    console.log('\n💡 Nastavte:');
    console.log('   - wordpress.username');
    console.log('   - wordpress.password (nebo wordpress.applicationPassword)');
    process.exit(1);
  }

  console.log('✅ Konfigurace OK');
  console.log(`   URL: ${config.wordpress.url}`);
  console.log(`   Username: ${config.wordpress.username}`);
  console.log(`   Project Path: ${config.local.projectPath}\n`);

  // Test připojení
  console.log('🔌 Testuji připojení k WordPress API...');
  const connectionTest = await wpAPI.testConnection();
  
  if (!connectionTest.success) {
    console.error('❌ Chyba připojení:', connectionTest.error);
    if (connectionTest.details) {
      console.error('Detaily:', JSON.stringify(connectionTest.details, null, 2));
    }
    console.log('\n💡 Možné příčiny:');
    console.log('   1. Špatné přihlašovací údaje');
    console.log('   2. Application Password není správně nastaven');
    console.log('   3. WordPress REST API není dostupné');
    process.exit(1);
  }

  console.log('✅ Připojení úspěšné\n');

  // Zkontrolovat lokální soubory
  console.log('📂 Kontroluji lokální soubory...');
  const localPagesResult = await bricksHandler.getAllLocalPages();
  
  if (localPagesResult.success) {
    const count = Object.keys(localPagesResult.data).length;
    console.log(`✅ Nalezeno ${count} lokálních stránek`);
  } else {
    console.log('⚠️  Chyba při načítání lokálních souborů:', localPagesResult.error);
  }

  // Zkontrolovat Bricks
  console.log('\n🔍 Kontroluji Bricks Builder...');
  const bricksCheck = await wpAPI.isBricksInstalled();
  
  if (bricksCheck.success) {
    if (bricksCheck.installed) {
      console.log('✅ Bricks je nainstalován');
      console.log('   Status:', bricksCheck.plugin?.status || 'N/A');
    } else {
      console.log('⚠️  Bricks není nainstalován');
      console.log('💡 Spusťte: npm run install-bricks');
    }
  } else {
    console.log('⚠️  Nelze zkontrolovat Bricks:', bricksCheck.error);
  }

  console.log('\n✅ Setup dokončen!');
}

// Hlavní spuštění
const command = process.argv[2] || 'setup';

switch (command) {
  case 'pull':
    await pull();
    break;
  case 'push':
    await push();
    break;
  case 'push-templates':
    await pushTemplates();
    break;
  case 'push-all':
    await push();
    await pushTemplates();
    break;
  case 'install-bricks':
    await installBricks();
    break;
  case 'update-bricks':
    await updateBricks();
    break;
  case 'clean-all':
    await cleanAll();
    break;
  case 'remove-bricks':
    await removeBricksTheme();
    break;
  case 'setup':
    await setup();
    break;
  default:
    console.log('📖 Použití:');
    console.log('   node sync.js setup            - Inicializace a testování');
    console.log('   node sync.js pull             - Stáhnout z WordPressu');
    console.log('   node sync.js push             - Nahrát Pages do WordPressu');
    console.log('   node sync.js push-templates   - Nahrát Templates (Header/Footer)');
    console.log('   node sync.js push-all         - Nahrát Pages + Templates');
    console.log('   node sync.js install-bricks   - Nainstalovat Bricks');
    console.log('   node sync.js update-bricks    - Aktualizovat Bricks téma');
    console.log('   node sync.js clean-all        - ⚠️  SMAZAT všechny stránky a templates');
    console.log('   node sync.js remove-bricks    - ⚠️  ODSTRAŇIT Bricks téma a aktivovat základní WP téma');
    process.exit(1);
}
