.

# 🤖 Cursor AI Guide - Fellaship Web Builder Tool

Tento dokument obsahuje instrukce pro Cursor AI, jak efektivně používat Fellaship Web Builder Tool pro správu WordPress + Bricks Builder projektů.

## 🎯 Účel tohoto toolu

**Fellaship Web Builder Tool** je univerzální nástroj pro:
- Synchronizaci Bricks Builder struktury mezi lokálním projektem a WordPress webem
- Automatizaci instalace a konfigurace Bricks Builder
- Správu stránek, templates a komponent přes REST API
- Automatické podepisování code elementů

**DŮLEŽITÉ:** Tool je navržen pro použití **POUZE přes Cursor AI**. Uživatel by měl tool ovládat pomocí promptů, ne přímo.

## 📋 TODO Systém - DŮLEŽITÉ!

**VŽDY používej TODO systém pro strukturovaný workflow!**

Po pullnutí toolu z GitHubu:

1. **Načti TODO systém:**
   ```javascript
   import { WORKFLOW_TODOS, displayTodos, getNextTodo, updateTodoStatus } from './workflow-todo.js';
   ```

2. **Zobraz aktuální fázi TODO:**
   ```javascript
   const todos = WORKFLOW_TODOS.GET_ACCESS; // nebo jiná fáze
   displayTodos(todos, 'Získání přístupů');
   ```

3. **Postupuj podle TODO krok za krokem:**
   - Zobraz uživateli instrukce z TODO
   - Po dokončení TODO aktualizuj status: `updateTodoStatus(todos, 'access-1', 'completed')`
   - Přejdi na další TODO: `getNextTodo(todos)`

4. **Fáze workflow:**
   - `INITIAL` - Počáteční setup (pull, npm install)
   - `GET_ACCESS` - Získání přístupů od uživatele
   - `SETUP_PLUGIN` - Nahrání a aktivace pluginu
   - `SETUP_BRICKS` - Instalace a konfigurace Bricks
   - `DONE` - Shrnutí a další kroky

**DŮLEŽITÉ:** TODO systém zajišťuje, že se na nic nezapomene a uživatel má jasný přehled, co se děje.

### Příklad použití TODO systému:

```javascript
// 1. Načíst TODO systém
import { WORKFLOW_TODOS, displayTodos, getNextTodo, updateTodoStatus } from './workflow-todo.js';

// 2. Zobrazit aktuální fázi
const todos = WORKFLOW_TODOS.GET_ACCESS;
displayTodos(todos, 'Získání přístupů');

// 3. Získat další TODO k vykonání
const nextTodo = getNextTodo(todos);
if (nextTodo) {
  console.log(`\n🎯 Další krok: ${nextTodo.title}`);
  
  // Pokud vyžaduje akci uživatele, zobraz instrukce
  if (nextTodo.userAction && nextTodo.instructions) {
    console.log('\n📝 Instrukce pro uživatele:');
    nextTodo.instructions.forEach(instruction => {
      console.log(instruction);
    });
  }
  
  // Po dokončení aktualizuj status
  updateTodoStatus(todos, nextTodo.id, 'completed');
}
```

## 🚀 Automatický workflow po pullnutí z GitHubu

### Krok 1: Automatické napromptování

**Po pullnutí toolu z GitHubu se automaticky spustí:**

1. AI načte `.cursor-initial-prompt.md` (vytvoří se automaticky)
2. AI řekne uživateli přesně, co potřebuje:
   - WordPress přístupy (URL, username, Application Password)
   - SSH přístupy (pokud má)
   - Název projektu
   - GitHub repo

3. AI poskytne **DETAILNÍ instrukce** jak získat Application Password:
   - **⚠️ DŮLEŽITÉ:** Application Password musí být nejdřív ZAPNUTO v Hostingeru!
   - Kde to najít: WordPress Dashboard → **Hostinger záložka** → Tools → Application Passwords
   - Jak to toggleovat ON (pokud je vypnuté) - **TO JE POVINNÉ!**
   - Jak vytvořit nový Application Password
   
   **Použij instrukce z TODO systému (`WORKFLOW_TODOS.GET_ACCESS[1].instructions`)**

### Krok 2: Uložení přístupů

**AI automaticky uloží všechny přístupy pomocí existujícího `save-access.js`:**

```javascript
// DŮLEŽITÉ: Použij existující save-access.js, nevytvářej nový soubor!
import { saveAccess } from './save-access.js';

await saveAccess({
  projectName: 'Acme Corp',
  wordpressUrl: 'https://example.com',
  wordpressUsername: 'admin',
  wordpressApplicationPassword: 'xxxx xxxx xxxx xxxx',
  sshHost: '123.456.789.0',
  sshUsername: 'u123456789',
  sshPassword: 'password',
  sshPort: 65002,
  githubRepo: 'https://github.com/user/acme-website',
  localPath: 'C:\\Users\\User\\Documents\\acme-website'
});
```

Toto vytvoří:
- `přístupy.md` - soubor s přístupy (pro budoucí použití)
- `config.json` - konfigurační soubor pro tool

**⚠️ NIKDY nevytvářej nový soubor `save-access-now.js` nebo podobný - použij existující `save-access.js`!**

### Krok 3: Automatický setup WordPressu - FÁZE 1

**AI automaticky spustí:**

```bash
node setup-wordpress.js
```

Toto provede:
1. ✅ Test připojení
2. ✅ Nahrání pluginu přes SSH
3. ⏸️ **POZASTAVÍ setup** a řekne uživateli:

> "✅ Plugin byl nahrán na server.
> 
> **CO DĚLAT:**
> 1. Jdi do WordPress Admin → Plugins
> 2. Najdi "Bricks API Endpoint"
> 3. Klikni "Activate"
> 4. Aktualizuj permalinks: Settings → Permalinks → Save Changes
> 5. Napiš mi: **"Plugin je aktivní"** nebo **"Aktivoval jsem plugin"**
> 
> ⏸️ Čekám na aktivaci pluginu..."

**DŮLEŽITÉ:** AI **NEPOKRAČUJE** s instalací Bricks, dokud uživatel neřekne, že plugin je aktivní!

### Krok 4: Automatický setup WordPressu - FÁZE 2 (po aktivaci pluginu)

**Když uživatel napíše "Plugin je aktivní" nebo "Aktivoval jsem plugin", AI pokračuje:**

```bash
node setup-wordpress.js
```

Nebo AI znovu spustí setup, který nyní:
1. ✅ Zkontroluje, že plugin je aktivní
2. ✅ Instalace Bricks (`node sync.js install-bricks`)
3. ✅ Aktivace licence
4. ✅ Aktualizace Bricks
5. ✅ Nastavení Bricks Settings (Code Execution + Post Types)
6. ✅ Vytvoření Homepage
7. ✅ Nastavení WordPress Reading (Static page = Homepage)
8. ✅ Vytvoření Templates (Header, Footer - prázdné)

**Po dokončení AI napíše:**
> "✅ WordPress je připraven! Můžeme začít tvořit web."

### Krok 4: Tvorba stránky (lokálně)

**Workflow:**

1. **Uživatel řekne:** "Vytvoř stránku 'About'" nebo "Vytvoř Header/Footer/Homepage"
2. **AI:**
   - **Načte `inspirace.json`** jako referenci pro správnou strukturu
   - **Načte `BRICKS-JSON-GUIDE.md`** pro detailní instrukce
   - Vytvoří lokální strukturu projektu (pokud neexistuje)
   - Vytvoří JSON soubor s **SPRÁVNOU strukturou** (s `id`, `parent`, `children` jako pole stringů)
   - **SPUSTÍ LOKÁLNÍ SERVER:** `node local-server.js` nebo `npm run dev`
   - **AUTOMATICKY OTEVŘE PROHLÍŽEČ** na `http://localhost:3000`
   - **ZOBRAZÍ VIZUÁLNÍ PREVIEW** stránky - renderuje Bricks JSON do skutečného HTML/CSS, stejně jako na WordPress webu!
3. **Uživatel upravuje stránku lokálně** (JSON soubor v Cursoru, vidí změny v prohlížeči po refresh)
4. **Když je spokojený:** "Pushni to"
5. **AI:**
   - Pushne stránku na WordPress: `node sync.js push` (pro pages) nebo `node sync.js push-templates` (pro Header/Footer)
   - **⚠️ DŮLEŽITÉ:** Templates (Header/Footer) se pushují JINAK než pages - používají `node sync.js push-templates`!
   - Tool automaticky extrahuje `content` pole a podepíše code elementy
   - Commitne a pushne do GitHubu: `git add . && git commit -m "..." && git push`
   
**📖 Pro pushování templates si přečti:** `PUSHING-TEMPLATES.md`

**⚠️ DŮLEŽITÉ PRAVIDLA:**
- **AI NIKDY nepushuje stránku bez předchozího spuštění lokálního serveru!**
- **AI VŽDY použije strukturu z `inspirace.json` jako referenci!**
- **AI VŽDY vytvoří JSON s `id`, `parent`, `children` jako pole stringů!**
- **AI VŽDY použije CODE ELEMENTY pro design (section → container → code), ne normální Bricks elementy (heading, text, button)!**

## 📋 První kroky při použití toolu

### 0. Načtení referenčních souborů

**Před tvorbou jakékoliv stránky AI MUSÍ načíst:**

1. **`inspirace.json`** - příklad správné struktury Bricks JSON
   ```javascript
   import fs from 'fs-extra';
   const inspirace = JSON.parse(await fs.readFile('inspirace.json', 'utf-8'));
   // Použij jako referenci pro strukturu s id, parent, children jako pole stringů
   ```

2. **`BRICKS-JSON-GUIDE.md`** - detailní návod na tvorbu JSON

**DŮLEŽITÉ:** AI VŽDY použije strukturu z `inspirace.json` jako základ!

### 1. Načtení konfigurace

**VŽDY nejdřív načti `config.json` a zjisti:**
- `wordpress.url` - URL WordPress webu
- `wordpress.username` - WordPress username
- `wordpress.applicationPassword` nebo `wordpress.password` - pro autentizaci
- `local.projectPath` - cesta k lokálnímu projektu
- `local.pagesPath` - složka s pages (obvykle "pages")
- `local.componentsPath` - složka s komponentami (obvykle "." = root)
- `mapping.pages` - mapování slug → název souboru
- `mapping.components` - mapování komponent (header, footer, homepage)

**Příklad:**
```json
{
  "wordpress": {
    "url": "https://example.com",
    "username": "admin",
    "applicationPassword": "xxxx xxxx xxxx xxxx"
  },
  "local": {
    "projectPath": "C:\\Users\\User\\Documents\\MyProject",
    "pagesPath": "pages",
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
  }
}
```

### 2. Test připojení

**Před jakoukoliv akcí vždy otestuj připojení:**
```bash
node sync.js setup
```

Tento příkaz:
- Ověří konfiguraci
- Otestuje připojení k WordPress API
- Zkontroluje lokální soubory
- Zkontroluje, zda je Bricks nainstalován

## 🔧 Základní workflow pro AI

### Workflow 1: Instalace Bricks Builder

**Kdy použít:** Uživatel chce nainstalovat Bricks Builder na nový WordPress web.

**Kroky:**
1. Zkontroluj, zda je `bricks.pluginZip` v config.json a zda soubor existuje
2. Spusť: `node sync.js install-bricks`
3. Po instalaci: Zkontroluj, zda je Bricks aktivní
4. **DŮLEŽITÉ:** Musíš aktivovat editaci stránek v Bricks Settings:
   - WordPress Admin → Bricks → Settings
   - Sekce "Post types" → ZAPNĚTE toggle pro "Page"

**Kód pro AI:**
```javascript
// 1. Zkontroluj config
const config = require('./config.json');
if (!config.bricks.pluginZip) {
  console.error('❌ bricks.pluginZip není nastaven v config.json');
}

// 2. Spusť instalaci
// node sync.js install-bricks

// 3. Po instalaci zkontroluj status
// node sync.js setup
```

### Workflow 2: Instalace Bricks API Endpoint pluginu

**Kdy použít:** Před prvním pushnutím stránek MUSÍ být plugin nainstalován.

**Kroky:**
1. Plugin soubor: `bricks-api-endpoint.php` (je v root složce toolu)
2. Nahrání na server:
   - Přes Hostinger hPanel: Files → File Manager
   - Cesta: `public_html/wp-content/plugins/bricks-api-endpoint/bricks-api-endpoint.php`
   - Nebo přes SSH (pokud máš přístup)
3. Aktivace: WordPress Admin → Plugins → Bricks API Endpoint → Activate
4. Aktualizace permalinks: Settings → Permalinks → Save Changes

**Kód pro AI (SSH upload):**
```javascript
// Pokud máš SSH přístup, použij update-plugin-ssh.js
// Nejdřív nastav SSH údaje v config.json nebo environment variables
// node update-plugin-ssh.js
```

### Workflow 3: Push stránky (Page)

**Kdy použít:** Uživatel chce nahrát/aktualizovat WordPress stránku z lokálního JSON souboru.

**Kroky:**
1. Zkontroluj, zda soubor existuje: `{projectPath}/{pagesPath}/{slug}.json`
2. Zkontroluj mapping v config.json: `mapping.pages[slug]`
3. Spusť: `node sync.js push`
4. Tool automaticky:
   - Vytvoří stránku, pokud neexistuje
   - Aktualizuje Bricks obsah
   - **AUTOMATICKY podepíše všechny code elementy**

**DŮLEŽITÉ:**
- Bricks očekává obsah jako **pole elementů**, ne celý objekt
- Tool automaticky extrahuje `content` pole z JSON
- Podepisování probíhá automaticky po pushnutí

**Kód pro AI:**
```javascript
// Struktura lokálního JSON souboru:
{
  "content": [
    { "name": "section", "settings": {...}, "children": [...] },
    { "name": "code", "settings": { "code": "..." } }
  ],
  "source": "bricksCopiedElements",
  "version": "2.0"
}

// Tool automaticky:
// 1. Extrahuje pouze content pole
// 2. Uloží do _bricks_page_content meta
// 3. Nastaví _bricks_editor_mode = 'bricks'
// 4. Regeneruje podpisy pro code elementy
```

### Workflow 4: Push template (Header/Footer)

**Kdy použít:** Uživatel chce nahrát Header nebo Footer jako Bricks template.

**Kroky:**
1. Zkontroluj, zda soubor existuje: `{projectPath}/{componentsPath}/{component-name}.json`
2. Zkontroluj mapping: `mapping.components.header` nebo `mapping.components.footer`
3. Spusť: `node sync.js push-templates`
4. Tool automaticky:
   - Vytvoří/aktualizuje template (post_type: `bricks_template`)
   - Nastaví `_bricks_template_type` = 'header' nebo 'footer'
   - **Uloží obsah do VŠECH tří meta klíčů:**
     - `_bricks_page_content` (standardní)
     - `_bricks_page_content_2` (standardní)
     - **`_bricks_page_header_2`** (pro header) nebo **`_bricks_page_footer_2`** (pro footer) ← **KLÍČOVÉ!**
   - **AUTOMATICKY podepíše všechny code elementy**

**DŮLEŽITÉ:**
- Templates se **NEPUSHUJÍ** automaticky s `node sync.js push`!
- Musíš použít `node sync.js push-templates`
- Nebo `node sync.js push-all` (pushne pages + templates)
- **Po pushnutí:** Uživatel musí manuálně requestnout podpisy v Bricks Settings → Templates → [Template] → Request Signatures

**🔑 Proč to funguje:**
- Bricks editor pro header/footer templates hledá obsah v **specifických meta klíčích** (`_bricks_page_header_2` / `_bricks_page_footer_2`)
- Bez těchto klíčů struktura nebude viditelná v Bricks editoru!
- Viz [TEMPLATES-VS-PAGES-EXPLAINED.md](./TEMPLATES-VS-PAGES-EXPLAINED.md) pro detailní vysvětlení

**Kód pro AI:**
```javascript
// Template se ukládá jako:
// post_type: 'bricks_template'
// meta: {
//   '_bricks_template_type': 'header' | 'footer',
//   '_bricks_template_active': true,
//   '_bricks_template_conditions': [],
//   '_bricks_page_content': [...]
// }
```

### Workflow 5: Podepisování code elementů

**Kdy použít:** Po každém pushnutí se podpisy generují automaticky. Pokud potřebuješ regenerovat ručně:

**Kroky:**
1. Zavolej endpoint: `POST /wp-json/bricks/v1/regenerate-signatures/{page_id}`
2. Nebo použij funkci v `wp-api.js`: `wpAPI.regenerateSignatures(pageId)`

**DŮLEŽITÉ:**
- Bez podpisů se code elementy **NESPUSTÍ**
- Podpisy se generují pomocí `wp_hash()` (WordPress funkce)
- Každý code element musí mít: `signature`, `user_id`, `time`, `executeCode: true`

**Kód pro AI:**
```javascript
// Automatické podepisování probíhá v:
// sync.js → push() → wpAPI.regenerateSignatures(pageId)

// Formát podpisu:
{
  "name": "code",
  "settings": {
    "code": "...",
    "signature": "abc123...",  // HMAC-MD5 hash
    "user_id": 1,
    "time": 1735689600,
    "executeCode": true
  }
}
```

## 🚨 Časté problémy a řešení

### Problém 1: "Bricks content not found"

**Příčina:** Plugin `bricks-api-endpoint.php` není nainstalován nebo není aktivní.

**Řešení:**
1. Zkontroluj, zda je plugin aktivní: WordPress Admin → Plugins
2. Pokud není, nainstaluj podle Workflow 2
3. Aktualizuj permalinks: Settings → Permalinks → Save Changes

### Problém 2: "Critical error" na stránce po pushnutí

**Příčina:** Bricks obsah není ve správném formátu.

**Řešení:**
1. Zkontroluj, zda lokální JSON má správnou strukturu (musí mít `content` pole)
2. Tool automaticky extrahuje `content` pole - zkontroluj, zda to funguje
3. Zkontroluj, zda jsou nastavené meta klíče: `_bricks_editor_mode = 'bricks'`

### Problém 3: Code elementy se nespouští

**Příčina:** Chybí podpisy nebo jsou neplatné.

**Řešení:**
1. Podpisy se generují automaticky po pushnutí
2. Pokud ne, zkontroluj endpoint: `/bricks/v1/regenerate-signatures/{id}`
3. Zkontroluj, zda plugin `bricks-api-endpoint.php` je aktualizovaný

### Problém 4: "401 Unauthorized"

**Příčina:** Špatné přihlašovací údaje.

**Řešení:**
1. Zkontroluj `config.json` → `wordpress.username`
2. Zkontroluj `config.json` → `wordpress.applicationPassword` (doporučeno)
3. Nebo použij `wordpress.password` (méně bezpečné)
4. Application Password vytvoříš v: WordPress Admin → Users → Your Profile → Application Passwords

## 📝 Příklady promptů pro uživatele

### "Nainstaluj Bricks Builder"
```bash
# AI by mělo:
1. Zkontrolovat config.json
2. Spustit: node sync.js install-bricks
3. Po instalaci připomenout aktivaci editace stránek
```

### "Pushni stránku 'about'"
```bash
# AI by mělo:
1. Zkontrolovat, zda existuje: {projectPath}/pages/about.json
2. Zkontrolovat mapping: config.mapping.pages.about
3. Spustit: node sync.js push
4. Ověřit, že stránka byla pushnuta a podepsána
```

### "Pushni Header a Footer"
```bash
# AI by mělo:
1. Zkontrolovat, zda existují: {projectPath}/header.json a footer.json
2. Zkontrolovat mapping: config.mapping.components.header a footer
3. Spustit: node sync.js push-templates
4. Ověřit, že templates byly pushnuty a podepsány
```

### "Aktualizuj plugin na serveru"
```bash
# AI by mělo:
1. Zkontrolovat SSH přístup (pokud je v config.json)
2. Spustit: node update-plugin-ssh.js
3. Nebo poskytnout instrukce pro ruční upload
```

## 🔑 Klíčové soubory pro AI

### `sync.js` - Hlavní skript
- `push()` - Push pages
- `pushTemplates()` - Push templates
- `installBricks()` - Instalace Bricks
- `setup()` - Test připojení

### `wp-api.js` - WordPress API klient
- `createPage()` - Vytvoření stránky
- `updateBricksContent()` - Aktualizace Bricks obsahu
- `regenerateSignatures()` - Regenerace podpisů
- `createOrUpdateTemplate()` - Vytvoření/aktualizace template

### `bricks-handler.js` - Handler pro Bricks data
- `prepareBricksForMeta()` - Příprava obsahu (extrahuje content pole)
- `getAllLocalPages()` - Načtení lokálních stránek
- `getComponentFilePath()` - Cesta k komponentě

### `bricks-api-endpoint.php` - WordPress plugin
- Custom REST API endpointy pro Bricks
- **MUSÍ být nainstalován na serveru!**

## ⚠️ DŮLEŽITÉ PRAVIDLA PRO AI

1. **VŽDY nejdřív načti config.json** - všechna konfigurace je tam
2. **VŽDY otestuj připojení** před pushnutím (`node sync.js setup`)
3. **NIKDY nehardcoduj cesty nebo názvy** - vše z config.json
4. **VŽDY pushni templates samostatně** - `node sync.js push-templates`
5. **VŽDY ověř podepisování** - probíhá automaticky, ale zkontroluj
6. **VŽDY zkontroluj plugin** - `bricks-api-endpoint.php` musí být aktivní
7. **NIKDY nepushuj bez testování** - vždy nejdřív setup

## 🎯 Typický workflow pro nový projekt

1. **Setup:**
   ```bash
   # Uživatel vytvoří config.json s údaji svého projektu
   # AI zkontroluje config.json
   node sync.js setup
   ```

2. **Instalace Bricks:**
   ```bash
   node sync.js install-bricks
   # AI připomene aktivaci editace stránek
   ```

3. **Instalace pluginu:**
   ```bash
   # AI poskytne instrukce nebo použije SSH
   # node update-plugin-ssh.js
   ```

4. **Push stránek:**
   ```bash
   node sync.js push
   ```

5. **Push templates:**
   ```bash
   node sync.js push-templates
   ```

6. **Ověření:**
   ```bash
   node sync.js setup
   ```

## 📚 Další dokumentace

- `README.md` - Obecná dokumentace
- `KONTEXT-A-POSTUPY.md` - Detailní workflow
- `INSTALACE.md` - Instalace pluginu
- `START-HERE.md` - Rychlý start

---

**Pamatuj:** Tento tool je univerzální - žádné hardcoded reference na konkrétní projekty! Vše je konfigurovatelné přes `config.json`.

