/**
 * Lokální server pro preview Bricks JSON stránek
 * Renderuje Bricks JSON do skutečného HTML/CSS jako na WordPress webu
 */

import http from 'http';
import fs from 'fs-extra';
import path from 'path';
import { fileURLToPath } from 'url';
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
  process.exit(1);
}

const PORT = 3000;
const projectPath = config.local.projectPath;
const pagesPath = path.join(projectPath, config.local.pagesPath || 'pages');
const componentsPath = config.local.componentsPath === '.' 
  ? projectPath 
  : path.join(projectPath, config.local.componentsPath || '.');

/**
 * Převést CSS settings na inline CSS string
 */
function settingsToCSS(settings) {
  const css = {};
  
  // Mapování Bricks settings na CSS vlastnosti
  if (settings.background) css.background = settings.background;
  if (settings.backgroundColor) css['background-color'] = settings.backgroundColor;
  if (settings.color) css.color = settings.color;
  if (settings.padding) css.padding = settings.padding;
  if (settings.margin) css.margin = settings.margin;
  if (settings.width) css.width = settings.width;
  if (settings._width) css.width = settings._width;
  if (settings.height) css.height = settings.height;
  if (settings._height) css.height = settings._height;
  if (settings.maxWidth) css['max-width'] = settings.maxWidth;
  if (settings.fontSize) css['font-size'] = settings.fontSize;
  if (settings.fontWeight) css['font-weight'] = settings.fontWeight;
  if (settings.textAlign) css['text-align'] = settings.textAlign;
  if (settings.display) css.display = settings.display;
  if (settings.flexDirection) css['flex-direction'] = settings.flexDirection;
  if (settings.alignItems) css['align-items'] = settings.alignItems;
  if (settings.justifyContent) css['justify-content'] = settings.justifyContent;
  if (settings.gap) css.gap = settings.gap;
  if (settings.gridTemplateColumns) css['grid-template-columns'] = settings.gridTemplateColumns;
  if (settings.borderRadius) css['border-radius'] = settings.borderRadius;
  if (settings.border) css.border = settings.border;
  if (settings.borderTop) css['border-top'] = settings.borderTop;
  if (settings.borderBottom) css['border-bottom'] = settings.borderBottom;
  if (settings.boxShadow) css['box-shadow'] = settings.boxShadow;
  if (settings.minHeight) css['min-height'] = settings.minHeight;
  if (settings.position) css.position = settings.position;
  if (settings._position) css.position = settings._position;
  if (settings._sticky) css.position = 'sticky';
  if (settings._top) css.top = settings._top;
  if (settings._zIndex) css['z-index'] = settings._zIndex;
  if (settings.zIndex) css['z-index'] = settings.zIndex;
  if (settings.top) css.top = settings.top;
  if (settings.marginTop) css['margin-top'] = settings.marginTop;
  if (settings.marginBottom) css['margin-bottom'] = settings.marginBottom;
  if (settings.marginLeft) css['margin-left'] = settings.marginLeft;
  if (settings.marginRight) css['margin-right'] = settings.marginRight;
  if (settings.lineHeight) css['line-height'] = settings.lineHeight;
  
  return Object.entries(css)
    .map(([key, value]) => `${key.replace(/([A-Z])/g, '-$1').toLowerCase()}: ${value}`)
    .join('; ');
}

/**
 * Renderovat Bricks element do HTML
 */
function renderElement(element, elementsMap, depth = 0) {
  if (!element) return '';
  
  const { id, name, settings = {}, children = [] } = element;
  const css = settingsToCSS(settings);
  const styleAttr = css ? ` style="${css}"` : '';
  const classAttr = id ? ` class="bricks-${id}"` : '';
  
  // Renderovat děti
  const childrenHTML = children
    .map(childId => {
      const childElement = elementsMap[childId];
      return childElement ? renderElement(childElement, elementsMap, depth + 1) : '';
    })
    .join('');
  
  // Renderovat podle typu elementu
  switch (name) {
    case 'section':
      return `<section${classAttr}${styleAttr}>${childrenHTML}</section>`;
    
    case 'container':
      return `<div class="container"${styleAttr}>${childrenHTML}</div>`;
    
    case 'div':
      return `<div${classAttr}${styleAttr}>${childrenHTML}</div>`;
    
    case 'heading':
      const tag = settings.tag || 'h1';
      const text = settings.text || '';
      return `<${tag}${classAttr}${styleAttr}>${text}</${tag}>`;
    
    case 'text':
      const textContent = settings.text || '';
      return `<p${classAttr}${styleAttr}>${textContent}</p>`;
    
    case 'button':
      const buttonText = settings.text || 'Button';
      const link = settings.link?.url || '#';
      const target = settings.link?.target || '_self';
      return `<a href="${link}" target="${target}"${classAttr}${styleAttr}>${buttonText}</a>`;
    
    case 'image':
      const imageUrl = settings.image?.url || settings.url || '';
      const imageAlt = settings.image?.alt || settings.alt || '';
      return `<img src="${imageUrl}" alt="${imageAlt}"${classAttr}${styleAttr} />`;
    
    case 'code':
      // Code elementy obsahují HTML/CSS/JS - vložit přímo
      const code = settings.code || '';
      return code; // Vložit HTML/CSS/JS přímo
    
    default:
      // Neznámý typ - renderovat jako div
      return `<div${classAttr}${styleAttr}>${childrenHTML}</div>`;
  }
}

/**
 * Renderovat Bricks JSON do HTML
 */
function renderBricksToHTML(bricksData) {
  // Handle both array format (direct) and object format (with content property)
  let content;
  if (Array.isArray(bricksData)) {
    content = bricksData;
  } else {
    content = bricksData.content || [];
  }
  
  if (content.length === 0) {
    return '';
  }
  
  // Vytvořit mapu elementů podle ID
  const elementsMap = {};
  content.forEach(element => {
    if (element.id) {
      elementsMap[element.id] = element;
    }
  });
  
  // Najít root elementy (parent === 0)
  const rootElements = content.filter(el => el.parent === 0 || el.parent === '0');
  
  // Renderovat všechny root elementy
  const html = rootElements
    .map(element => renderElement(element, elementsMap))
    .join('');
  
  return html;
}

/**
 * Načíst Header nebo Footer komponentu
 */
async function loadComponent(componentName) {
  try {
    const componentFile = config.mapping?.components?.[componentName] || componentName;
    const filePath = path.join(componentsPath, `${componentFile}.json`);
    
    if (await fs.pathExists(filePath)) {
      const fileContent = await fs.readFile(filePath, 'utf-8');
      return JSON.parse(fileContent);
    }
  } catch (error) {
    // Komponenta neexistuje nebo chyba - vrátit null
  }
  return null;
}

/**
 * HTML template s renderovaným obsahem - čistá stránka bez preview baru
 */
async function HTML_TEMPLATE(title, pageContent) {
  // Načíst Header a Footer
  const headerData = await loadComponent('header');
  const footerData = await loadComponent('footer');
  
  const headerHTML = headerData ? renderBricksToHTML(headerData) : '';
  const footerHTML = footerData ? renderBricksToHTML(footerData) : '';
  const renderedContent = renderBricksToHTML(pageContent);
  
  return `<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>${title || 'MaxHair'}</title>
  <style>
    * { 
      margin: 0; 
      padding: 0; 
      box-sizing: border-box; 
    }
    
    html {
      overflow-y: auto;
      overflow-x: hidden;
      -webkit-overflow-scrolling: touch;
    }
    
    body { 
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      line-height: 1.6;
      color: #333;
      overflow-y: auto;
      overflow-x: hidden;
      min-height: 100%;
      position: relative;
    }
    
    /* Základní styly pro Bricks elementy */
    .container {
      width: 100%;
      margin: 0 auto;
    }
    
    section {
      display: block;
      width: 100%;
    }
    
    /* Hlavní obsah - zajištění scrollování a klikání */
    #main-content {
      position: relative;
      z-index: 0;
      min-height: 100vh;
    }
  </style>
</head>
<body>
  ${headerHTML}
  <main id="main-content">
  ${renderedContent}
  </main>
  ${footerHTML}
  <style id="mh-alternating-styles">
    /* Střídající se pozadí sekcí - #F8F8F8 a #F0F0F0 */
    #main-content > section:nth-of-type(odd) > div > section:not([class*="hero"]) {
      background-color: #F8F8F8 !important;
    }
    #main-content > section:nth-of-type(even) > div > section:not([class*="hero"]) {
      background-color: #F0F0F0 !important;
    }
    #main-content > section:nth-of-type(odd) > div > section [class*="-card"],
    #main-content > section:nth-of-type(odd) > div > section .mh-faq-item,
    #main-content > section:nth-of-type(odd) > div > section .doctor-card,
    #main-content > section:nth-of-type(odd) > div > section .why-card,
    #main-content > section:nth-of-type(odd) > div > section .cert-card {
      background-color: #F0F0F0 !important;
    }
    #main-content > section:nth-of-type(even) > div > section [class*="-card"],
    #main-content > section:nth-of-type(even) > div > section .mh-faq-item,
    #main-content > section:nth-of-type(even) > div > section .doctor-card,
    #main-content > section:nth-of-type(even) > div > section .why-card,
    #main-content > section:nth-of-type(even) > div > section .cert-card {
      background-color: #F8F8F8 !important;
    }
  </style>
</body>
</html>`;
}

// Server
const server = http.createServer(async (req, res) => {
  const url = new URL(req.url, `http://localhost:${PORT}`);
  
  if (url.pathname === '/') {
    // Hlavní stránka - zobrazit homepage.json
    const filePath = path.join(pagesPath, 'homepage.json');
    
    try {
      const fileContent = await fs.readFile(filePath, 'utf-8');
      const pageData = JSON.parse(fileContent);
      
      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      res.end(await HTML_TEMPLATE('MaxHair - Domů', pageData));
    } catch (error) {
      res.writeHead(500, { 'Content-Type': 'text/plain' });
      res.end('Chyba při načítání homepage: ' + error.message);
    }
  } else if (url.pathname.startsWith('/component/')) {
    // Preview komponenty (Header/Footer)
    const componentName = url.pathname.split('/component/')[1];
    const componentFile = config.mapping?.components?.[componentName] || componentName;
    const filePath = path.join(componentsPath, `${componentFile}.json`);
    
    try {
      const fileContent = await fs.readFile(filePath, 'utf-8');
      const componentData = JSON.parse(fileContent);
      
      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      res.end(await HTML_TEMPLATE(componentName, componentData));
    } catch (error) {
      res.writeHead(404, { 'Content-Type': 'text/plain' });
      res.end('Komponenta nenalezena: ' + error.message);
    }
  } else if (url.pathname.startsWith('/assets/')) {
    // Statické soubory ze složky assets (obrázky procesu atd.)
    const filePath = path.join(projectPath, url.pathname.slice(1));
    try {
      const data = await fs.readFile(filePath);
      const ext = path.extname(filePath).toLowerCase();
      const mime = { '.png': 'image/png', '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg', '.gif': 'image/gif', '.webp': 'image/webp', '.svg': 'image/svg+xml' }[ext] || 'application/octet-stream';
      res.writeHead(200, { 'Content-Type': mime });
      res.end(data);
    } catch (err) {
      res.writeHead(404, { 'Content-Type': 'text/plain' });
      res.end('Not Found');
    }
    return;
  } else {
    // Přímé routování - /{slug} → {slug}.json
    const slug = url.pathname.slice(1); // Odstranit úvodní /
    
    // Ignorovat requesty na favicon a prázdný slug (assets už jsou výše)
    if (slug.includes('.') || slug === '') {
      res.writeHead(404, { 'Content-Type': 'text/plain' });
      res.end('Not Found');
      return;
    }
    
    const filePath = path.join(pagesPath, `${slug}.json`);
    
    try {
      const fileContent = await fs.readFile(filePath, 'utf-8');
      const pageData = JSON.parse(fileContent);
      
      const pageTitle = slug.charAt(0).toUpperCase() + slug.slice(1).replace(/-/g, ' ');
      res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
      res.end(await HTML_TEMPLATE(pageTitle, pageData));
    } catch (error) {
      res.writeHead(404, { 'Content-Type': 'text/plain' });
      res.end('Stránka nenalezena: ' + slug);
    }
  }
});

server.listen(PORT, async () => {
  console.log(`🚀 Lokální server spuštěn na http://localhost:${PORT}`);
  console.log(`📁 Sleduji: ${pagesPath}\n`);
  console.log(`💡 Domů: http://localhost:${PORT}/`);
  console.log(`💡 Stránky: http://localhost:${PORT}/{slug} (např. /o-nas, /kontakt, /faq)`);
  console.log(`💡 Komponenty: http://localhost:${PORT}/component/{name}\n`);
  
  // Automaticky otevřít prohlížeč
  try {
    const platform = process.platform;
    let command;
    
    if (platform === 'win32') {
      command = `start http://localhost:${PORT}`;
    } else if (platform === 'darwin') {
      command = `open http://localhost:${PORT}`;
    } else {
      command = `xdg-open http://localhost:${PORT}`;
    }
    
    await execAsync(command);
    console.log('✅ Prohlížeč otevřen automaticky\n');
  } catch (error) {
    console.log(`💡 Otevři prohlížeč: http://localhost:${PORT}\n`);
  }
  
  console.log('💡 Pro ukončení stiskni Ctrl+C');
});
