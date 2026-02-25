import fs from 'fs-extra';
import path from 'path';
import AdmZip from 'adm-zip';

/**
 * Handler pro práci s Bricks Builder daty
 */
export class BricksHandler {
  constructor(config) {
    this.config = config;
    this.projectPath = config.local.projectPath;
    this.pagesPath = path.join(this.projectPath, config.local.pagesPath);
    this.sectionsPath = path.join(this.projectPath, config.local.sectionsPath);
    this.componentsPath = config.local.componentsPath === '.' 
      ? this.projectPath 
      : path.join(this.projectPath, config.local.componentsPath);
  }

  /**
   * Načíst JSON soubor
   */
  async loadJSONFile(filePath) {
    try {
      const content = await fs.readFile(filePath, 'utf-8');
      return { success: true, data: JSON.parse(content) };
    } catch (error) {
      return { success: false, error: error.message };
    }
  }

  /**
   * Uložit JSON soubor
   */
  async saveJSONFile(filePath, data) {
    try {
      await fs.ensureDir(path.dirname(filePath));
      await fs.writeFile(filePath, JSON.stringify(data, null, 2), 'utf-8');
      return { success: true };
    } catch (error) {
      return { success: false, error: error.message };
    }
  }

  /**
   * Získat cestu k souboru stránky
   */
  getPageFilePath(pageSlug) {
    const fileName = this.config.mapping.pages[pageSlug] || pageSlug;
    return path.join(this.pagesPath, `${fileName}.json`);
  }

  /**
   * Získat cestu k souboru komponenty
   */
  getComponentFilePath(componentName) {
    const fileName = this.config.mapping.components[componentName] || componentName;
    return path.join(this.componentsPath, `${fileName}.json`);
  }

  /**
   * Získat cestu k souboru sekce
   */
  getSectionFilePath(sectionName) {
    return path.join(this.sectionsPath, `${sectionName}.json`);
  }

  /**
   * Načíst všechny lokální stránky
   */
  async getAllLocalPages() {
    try {
      const files = await fs.readdir(this.pagesPath);
      const jsonFiles = files.filter(f => f.endsWith('.json'));
      
      const pages = {};
      for (const file of jsonFiles) {
        const slug = path.basename(file, '.json');
        const filePath = path.join(this.pagesPath, file);
        const result = await this.loadJSONFile(filePath);
        
        if (result.success) {
          pages[slug] = {
            slug,
            filePath,
            data: result.data
          };
        }
      }
      
      return { success: true, data: pages };
    } catch (error) {
      return { success: false, error: error.message };
    }
  }

  /**
   * Načíst všechny lokální komponenty
   */
  async getAllLocalComponents() {
    try {
      const components = {};
      
      for (const [name, fileName] of Object.entries(this.config.mapping.components)) {
        const filePath = this.getComponentFilePath(name);
        if (await fs.pathExists(filePath)) {
          const result = await this.loadJSONFile(filePath);
          if (result.success) {
            components[name] = {
              name,
              filePath,
              data: result.data
            };
          }
        }
      }
      
      return { success: true, data: components };
    } catch (error) {
      return { success: false, error: error.message };
    }
  }

  /**
   * Normalizovat Bricks JSON strukturu
   */
  normalizeBricksData(data) {
    // DŮLEŽITÉ: Pokud je vstup pole (array), vrátit ho přímo
    // Bricks ukládá obsah jako pole elementů
    if (Array.isArray(data)) {
      return data;
    }
    
    // Pokud je to objekt s content polem, extrahovat content
    if (data && typeof data === 'object' && data.content && Array.isArray(data.content)) {
      return data.content;
    }
    
    // Zajistit, že data mají správnou strukturu pro legacy formát
    if (!data.content) {
      data.content = [];
    }
    if (!data.source) {
      data.source = 'bricksCopiedElements';
    }
    if (!data.version) {
      data.version = '2.0';
    }
    return data;
  }

  /**
   * Porovnat dva Bricks JSON soubory
   */
  compareBricksData(localData, remoteData) {
    const localStr = JSON.stringify(this.normalizeBricksData(localData));
    const remoteStr = JSON.stringify(this.normalizeBricksData(remoteData));
    return localStr === remoteStr;
  }

  /**
   * Extrahovat Bricks obsah z WordPress meta
   */
  extractBricksFromMeta(meta) {
    const metaKey = this.config.bricksMetaKey || '_bricks_page_content';
    
    if (meta && meta[metaKey]) {
      try {
        const content = typeof meta[metaKey] === 'string' 
          ? JSON.parse(meta[metaKey]) 
          : meta[metaKey];
        return { success: true, data: content };
      } catch (error) {
        return { success: false, error: 'Invalid JSON in Bricks meta' };
      }
    }
    
    return { success: false, error: 'Bricks meta not found' };
  }

  /**
   * Připravit Bricks data pro uložení do WordPress meta
   * Bricks očekává pole elementů, ne celý objekt
   */
  prepareBricksForMeta(bricksData) {
    const normalized = this.normalizeBricksData(bricksData);
    
    // DŮLEŽITÉ: Bricks ukládá obsah jako pole elementů, ne jako objekt s content polem
    // Pokud má data.content, použít jen content pole
    if (normalized.content && Array.isArray(normalized.content)) {
      return JSON.stringify(normalized.content);
    }
    
    // Pokud už je to pole, použít přímo
    if (Array.isArray(normalized)) {
      return JSON.stringify(normalized);
    }
    
    // Jinak vrátit normalizovaný objekt
    return JSON.stringify(normalized);
  }
}


