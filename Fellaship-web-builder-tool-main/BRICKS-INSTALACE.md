# 🎨 Instalace Bricks Builder Tématu

Tento dokument popisuje kompletní proces instalace Bricks Builder tématu pomocí Fellaship Web Builder Tool.

## 📋 Předpoklady

1. **WordPress web** je nastaven a běží
2. **Plugin `bricks-api-endpoint.php`** je nainstalován a aktivní
3. **ZIP soubor s Bricks tématem** (`bricks.2.0.zip`) je v root složce toolu
4. **Licenční klíč** je nastaven v `config.json`

## 📦 ZIP Soubor s Bricks Tématem

### Umístění souboru

ZIP soubor `bricks.2.0.zip` musí být umístěn v **root složce toolu** (stejná složka jako `sync.js`).

Tool automaticky hledá soubor na těchto místech (v tomto pořadí):
1. `{root-složka-toolu}/bricks.2.0.zip`
2. `C:\Users\eschl\Documents\Fellaship-web-builder-tool-test\bricks.2.0.zip` (fallback pro Windows)
3. `{aktuální-složka}/bricks.2.0.zip`

### Konfigurace v config.json

V `config.json` nastavte:

```json
{
  "bricks": {
    "licenseKey": "d663003f17eaefce68fb6eee304b63e6",
    "pluginZip": "bricks.2.0.zip"
  }
}
```

**DŮLEŽITÉ:** `pluginZip` by měl být relativní cesta k souboru v root složce toolu, ne absolutní cesta.

## 🔧 Proces instalace

### Krok 1: Kontrola připojení

Nejdřív zkontrolujte, zda je připojení k WordPress API funkční:

```bash
node sync.js setup
```

Měli byste vidět:
```
✅ Připojení úspěšné
```

### Krok 2: Instalace Bricks tématu

Spusťte instalaci:

```bash
node sync.js install-bricks
```

Tool automaticky:
1. ✅ Zkontroluje, zda je Bricks téma již nainstalováno
2. ✅ Pokud ne, nainstaluje téma ze ZIP souboru
3. ✅ Aktivuje téma
4. ✅ Aktivuje licenci (pokud je `licenseKey` v `config.json`)

### Výstup při úspěšné instalaci

```
🔧 Instalace Bricks Builder...

🔍 Kontroluji, zda je Bricks téma již nainstalováno...
📦 Instaluji Bricks TÉMA ze souboru: C:\...\bricks.2.0.zip...
✅ Bricks téma nainstalováno
🔄 Aktivuji Bricks téma...
✅ Bricks téma aktivováno

🔑 Aktivuji Bricks licenci...
✅ Licence aktivována
```

## 🔍 Řešení problémů

### Problém: ZIP soubor nenalezen

**Chyba:**
```
❌ ZIP soubor nenalezen: ...
```

**Řešení:**
1. Zkontrolujte, zda existuje soubor `bricks.2.0.zip` v root složce toolu
2. Pokud ne, zkopírujte ho tam z `C:\Users\eschl\Documents\Fellaship-web-builder-tool-test\`
3. Nebo upravte `config.json` → `bricks.pluginZip` na absolutní cestu

### Problém: Instalace selhala (401/403)

**Chyba:**
```
❌ Chyba při instalaci: Request failed with status code 401
```

**Řešení:**
1. Zkontrolujte, zda je plugin `bricks-api-endpoint.php` aktivní
2. Zkontrolujte, zda jsou permalinks aktualizované: **Settings → Permalinks → Save Changes**
3. Zkontrolujte Application Password v `config.json`

### Problém: Aktivace tématu selhala (404)

**Chyba:**
```
❌ Chyba při aktivaci: Request failed with status code 404
Téma nebylo nalezeno: bricks
```

**Řešení:**
1. Aktualizujte plugin na serveru: `node update-plugin-ssh.js`
2. Zkontrolujte, zda je téma skutečně nainstalováno (WordPress Admin → Appearance → Themes)
3. Pokud je téma nainstalováno, ale aktivace selhává, aktivujte ho ručně v WordPress Adminu

### Problém: Aktivace licence selhala (404)

**Chyba:**
```
❌ Chyba při aktivaci licence: Request failed with status code 404
```

**Řešení:**
1. Zkontrolujte, zda je plugin `bricks-api-endpoint.php` aktivní
2. Zkontrolujte, zda je Bricks téma aktivní
3. Aktivujte licenci ručně: **Bricks → Settings → License**

## 📝 Technické detaily

### Jak funguje instalace

1. **Upload ZIP souboru:**
   - Tool použije endpoint `/wp-json/bricks/v1/install-theme`
   - ZIP soubor se uploaduje jako `multipart/form-data` s field name `theme_file`
   - Plugin použije WordPress `Theme_Upgrader` pro instalaci

2. **Aktivace tématu:**
   - Tool použije endpoint `/wp-json/bricks/v1/activate-theme`
   - Plugin najde téma podle slug/názvu a aktivuje ho pomocí `switch_theme()`

3. **Aktivace licence:**
   - Tool použije endpoint `/wp-json/bricks/v1/activate-license`
   - Plugin uloží licenční klíč do WordPress options a pokusí se aktivovat přes Bricks API

### Endpointy v bricks-api-endpoint.php

- `POST /wp-json/bricks/v1/install-theme` - Instalace tématu ze ZIP
- `POST /wp-json/bricks/v1/activate-theme` - Aktivace tématu
- `POST /wp-json/bricks/v1/activate-license` - Aktivace licence

### Soubory zapojené do procesu

- `sync.js` → funkce `installBricks()`
- `wp-api.js` → metody `installTheme()`, `activateTheme()`, `activateBricksLicense()`
- `bricks-api-endpoint.php` → funkce `bricks_install_theme()`, `bricks_activate_theme()`, `bricks_activate_license()`
- `config.json` → konfigurace `bricks.pluginZip` a `bricks.licenseKey`

## ✅ Kontrola úspěšné instalace

Po instalaci zkontrolujte:

1. **WordPress Admin → Appearance → Themes**
   - Bricks téma by mělo být viditelné a aktivní

2. **Bricks → Settings → License**
   - Licence by měla být aktivní

3. **WordPress Admin → Bricks → Settings**
   - Code Execution by mělo být zapnuté
   - Post types → Page by mělo být zapnuté

## 🚀 Automatický setup

Pokud používáte `setup-wordpress.js`, instalace Bricks proběhne automaticky po aktivaci pluginu:

```bash
node setup-wordpress.js
```

Tool automaticky:
1. Zkontroluje aktivaci pluginu
2. Nainstaluje Bricks téma
3. Aktivuje licenci
4. Nastaví Bricks Settings
5. Vytvoří Homepage
6. Nastaví WordPress Reading
7. Vytvoří Templates (Header, Footer)

## 📚 Související dokumentace

- `CURSOR-AI-GUIDE.md` - Kompletní průvodce pro Cursor AI
- `AUTO-SETUP.md` - Automatický setup workflow
- `KONTEXT-A-POSTUPY.md` - Kontext a postupy projektu

