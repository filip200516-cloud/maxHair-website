# ⚡ Quick Start pro Cursor AI

Rychlý průvodce pro Cursor AI, jak začít s Fellaship Web Builder Tool.

## 🚀 První kroky

### 1. Načti konfiguraci
```javascript
// VŽDY nejdřív načti config.json
const config = require('./config.json');

// Zkontroluj:
// - config.wordpress.url
// - config.wordpress.username
// - config.wordpress.applicationPassword
// - config.local.projectPath
// - config.mapping.pages
// - config.mapping.components
```

### 2. Test připojení
```bash
node sync.js setup
```

### 3. Instalace Bricks (pokud není nainstalován)
```bash
node sync.js install-bricks
```

### 4. Instalace pluginu (POVINNÉ před pushnutím)
- Plugin: `bricks-api-endpoint.php`
- Cesta na serveru: `wp-content/plugins/bricks-api-endpoint/bricks-api-endpoint.php`
- Aktivovat v WordPress Admin → Plugins
- Aktualizovat permalinks: Settings → Permalinks → Save Changes

## 📤 Push workflow

### Push stránky
```bash
# 1. Zkontroluj, zda soubor existuje
# {projectPath}/{pagesPath}/{slug}.json

# 2. Spusť push
node sync.js push

# Automaticky:
# - Vytvoří/aktualizuje stránku
# - Uloží Bricks obsah
# - Podepíše code elementy
```

### Push templates
```bash
# 1. Zkontroluj mapping v config.json
# mapping.components.header a mapping.components.footer

# 2. Spusť push templates
node sync.js push-templates

# Automaticky:
# - Vytvoří/aktualizuje templates
# - Nastaví správné meta
# - Podepíše code elementy
```

## ⚠️ DŮLEŽITÉ

1. **Templates se NEPUSHUJÍ s `node sync.js push`!**
   - Použij: `node sync.js push-templates`
   - Nebo: `node sync.js push-all` (pushne pages + templates)

2. **Podepisování probíhá automaticky**
   - Po každém pushnutí se automaticky regenerují podpisy
   - Bez podpisů se code elementy nespustí

3. **Bricks očekává pole elementů**
   - Tool automaticky extrahuje `content` pole z JSON
   - Struktura: `{ content: [...], source: '...', version: '...' }`

## 🔧 Časté příkazy

```bash
# Test připojení
node sync.js setup

# Instalace Bricks
node sync.js install-bricks

# Push pages
node sync.js push

# Push templates
node sync.js push-templates

# Push všeho
node sync.js push-all

# Pull z WordPressu
node sync.js pull

# Aktualizace Bricks tématu
node sync.js update-bricks
```

## 📚 Více informací

- **CURSOR-AI-GUIDE.md** - Kompletní průvodce pro AI
- **.cursorrules** - Pravidla pro Cursor AI
- **README.md** - Obecná dokumentace
- **KONTEXT-A-POSTUPY.md** - Detailní workflow

