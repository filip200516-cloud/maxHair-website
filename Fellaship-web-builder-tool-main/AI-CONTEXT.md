# 🤖 AI Context - Fellaship Web Builder Tool

Tento soubor obsahuje klíčové informace pro Cursor AI, jak efektivně používat tento tool.

## 🎯 Účel

Fellaship Web Builder Tool je **univerzální nástroj** pro synchronizaci Bricks Builder struktury s WordPress weby. Tool je navržen pro použití **pouze přes Cursor AI** - uživatel by měl tool ovládat pomocí promptů, ne přímo.

## 📋 Klíčové principy

1. **Všechna konfigurace je v `config.json`** - žádné hardcoded hodnoty
2. **Tool je univerzální** - funguje s jakýmkoliv WordPress + Bricks projektem
3. **AI by mělo vždy nejdřív načíst config.json** před jakoukoliv akcí
4. **Všechny cesty a názvy jsou konfigurovatelné** - žádné pevné reference

## 🔧 Struktura config.json

```json
{
  "wordpress": {
    "url": "https://example.com",
    "username": "admin",
    "applicationPassword": "xxxx xxxx xxxx xxxx"
  },
  "bricks": {
    "licenseKey": "your-license-key",
    "pluginZip": "bricks.2.0.zip"
  },
  "local": {
    "projectPath": "C:\\Users\\User\\Documents\\Project",
    "pagesPath": "pages",
    "sectionsPath": "sections",
    "componentsPath": "."
  },
  "mapping": {
    "pages": {
      "about": "about",
      "contact": "contact"
    },
    "components": {
      "header": "header",
      "footer": "footer"
    }
  },
  "templateNames": {
    "header": "Header",
    "footer": "Footer"
  }
}
```

## 🚀 Základní workflow

### 1. Setup (vždy první)
```bash
node sync.js setup
```
- Ověří konfiguraci
- Otestuje připojení
- Zkontroluje lokální soubory

### 2. Instalace Bricks (pokud není nainstalován)
```bash
node sync.js install-bricks
```
- Nainstaluje Bricks Builder ze ZIP souboru
- Aktivuje plugin
- **DŮLEŽITÉ:** Po instalaci musí uživatel aktivovat editaci stránek v Bricks Settings

### 3. Instalace pluginu (POVINNÉ)
- Plugin: `bricks-api-endpoint.php`
- Cesta: `wp-content/plugins/bricks-api-endpoint/bricks-api-endpoint.php`
- Aktivovat v WordPress Admin
- Aktualizovat permalinks

### 4. Push stránky
```bash
node sync.js push
```
- Pushne všechny stránky z `{projectPath}/{pagesPath}/`
- Automaticky podepíše code elementy

### 5. Push templates
```bash
node sync.js push-templates
```
- Pushne header a footer jako Bricks templates
- **NEPUSHUJE se automaticky s `node sync.js push`!**

## ⚠️ DŮLEŽITÉ PRAVIDLA

1. **Templates se pushují SAMOSTATNĚ** - `node sync.js push-templates`
2. **Podepisování je automatické** - probíhá po každém pushnutí
3. **Bricks očekává pole elementů** - tool automaticky extrahuje `content` pole
4. **Plugin MUSÍ být nainstalován** před pushnutím
5. **VŽDY otestuj připojení** před akcí (`node sync.js setup`)

## 📝 Příklady promptů pro uživatele

### "Nainstaluj Bricks Builder"
AI by mělo:
1. Zkontrolovat `config.json` → `bricks.pluginZip`
2. Spustit: `node sync.js install-bricks`
3. Připomenout aktivaci editace stránek

### "Pushni stránku 'about'"
AI by mělo:
1. Zkontrolovat `config.json` → `mapping.pages.about`
2. Ověřit existenci: `{projectPath}/{pagesPath}/about.json`
3. Spustit: `node sync.js push`
4. Ověřit výstup (mělo by být "✅ Podepsáno X code elementů")

### "Pushni Header a Footer"
AI by mělo:
1. Zkontrolovat `config.json` → `mapping.components.header` a `footer`
2. Ověřit existenci souborů
3. Spustit: `node sync.js push-templates` (NE `push`!)
4. Ověřit výstup

## 🔑 Klíčové soubory

- `sync.js` - Hlavní skript (push, pull, install, setup)
- `wp-api.js` - WordPress REST API klient
- `bricks-handler.js` - Handler pro Bricks JSON (extrahuje content pole)
- `bricks-api-endpoint.php` - WordPress plugin (MUSÍ být na serveru)
- `config.json` - Veškerá konfigurace (NIKDY necommitovat!)

## 🐛 Časté problémy

- **401 Unauthorized**: Zkontroluj `config.json` → `wordpress.applicationPassword`
- **404 Not Found**: Plugin není aktivní nebo permalinks nejsou aktualizované
- **Critical error**: Bricks obsah není ve správném formátu
- **Code elementy se nespouští**: Chybí podpisy (měly by se generovat automaticky)

## 📚 Více informací

- `CURSOR-AI-GUIDE.md` - Kompletní průvodce
- `.cursorrules` - Pravidla pro Cursor AI
- `QUICK-START-AI.md` - Rychlý start

