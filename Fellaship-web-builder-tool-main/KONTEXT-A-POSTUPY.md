# 📚 Kontext a Postupy - Fellaship Web Builder Tool

## 🔧 Instalace Bricks

### Důležité kroky po instalaci:

1. **Aktivovat editaci stránek v Bricks nastavení:**
   - WordPress Admin → Bricks → Settings
   - Sekce "Post types"
   - **ZAPNĚTE toggle pro "Page"** (aby se stránky mohly editovat v Bricks)
   - Uložit

### Automatická instalace:
```bash
cd C:\Users\YourUser\Documents\Fellaship-Web-Builder-Tool
node sync.js install-bricks
```

## 📦 Push komponent

### Header a Footer - DŮLEŽITÉ!

**Header a Footer se NIKDY nepushují jako stránky!**

**Správný postup (automatický přes tool):**
1. Zkontroluj `config.json` → `mapping.components.header` a `mapping.components.footer`
2. Spusť: `node sync.js push-templates`
3. Tool automaticky vytvoří/aktualizuje templates a podepíše code elementy

**Nebo ruční postup:**
1. WordPress Admin → Bricks → Templates
2. Add New Template
3. Template Type: zvolit **Header** nebo **Footer**
4. Pojmenovat (podle `config.templateNames` nebo default)
5. Uložit (Save Draft)
6. Publikovat (Publish)
7. Otevřít v Bricks Editoru
8. Importovat JSON z lokálního souboru

**Nesprávný postup:**
- ❌ Pushovat jako WordPress stránky (`node sync.js push`)
- ❌ Vytvářet stránky "bricks-header" nebo "bricks-footer"

## 📂 Struktura projektu

Struktura projektu je konfigurovatelná přes `config.json`:

```
{projectPath}/
├── {pagesPath}/        # Stránky (např. pages/about.json, pages/contact.json)
├── {sectionsPath}/     # Sekce (pokud existují)
├── {componentsPath}/   # Komponenty (obvykle root složka)
│   ├── {header}.json   # Header komponenta (pushnout do Template)
│   ├── {footer}.json   # Footer komponenta (pushnout do Template)
│   └── {homepage}.json # Homepage komponenta (pokud existuje)
```

**Příklad konfigurace v config.json:**
```json
{
  "local": {
    "projectPath": "C:\\Users\\User\\Documents\\MyProject",
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
  }
}
```

## 🔄 Push postup

### 📄 PAGES (Stránky) - Workflow

**Jak se pushují Pages:**

1. **Načtení lokálních souborů:**
   - Skript načte všechny JSON soubory ze složky `pages/` podle mapping v `config.json`
   - Každý soubor obsahuje Bricks strukturu s `content` polem (pole elementů)

2. **Vytvoření/aktualizace WordPress stránky:**
   - Pokud stránka neexistuje → vytvoří se nová WordPress stránka (post_type: `page`)
   - Pokud stránka existuje → aktualizuje se existující stránka

3. **Příprava Bricks obsahu:**
   - Z JSON objektu se extrahuje pouze pole `content` (pole elementů)
   - Bricks očekává obsah jako pole elementů, ne celý objekt s `content`, `source`, `version`

4. **Uložení do WordPress meta:**
   - Obsah se uloží do meta klíčů:
     - `_bricks_page_content`
     - `_bricks_page_content_2` (pro kompatibilitu)
   - Nastaví se meta:
     - `_bricks_editor_mode` = `'bricks'`
     - `_bricks_page_content_type` = `'bricks'`

5. **🔐 AUTOMATICKÉ PODEPISOVÁNÍ CODE ELEMENTŮ:**
   - **DŮLEŽITÉ:** Po každém pushnutí se automaticky regenerují podpisy pro všechny code elementy
   - Zavolá se endpoint: `POST /wp-json/bricks/v1/regenerate-signatures/{page_id}`
   - Pro každý code element se:
     - Vygeneruje podpis pomocí `wp_hash()` (WordPress funkce s HMAC-MD5)
     - Přidá `signature`, `user_id`, `time` do `settings` code elementu
     - Nastaví `executeCode: true`
   - **Bez podpisů code elementy nebudou fungovat!**

**Příkaz:**
```bash
npm run push
# nebo
node sync.js push
```

**Příklad výstupu:**
```
📤 Nahrávám: faq...
   ✅ Stránka vytvořena (ID: 75)
   🔐 Regeneruji podpisy kódu...
   ✅ Podepsáno 3 code elementů
   ✅ Aktualizováno (ID: 75)
```

---

### 🎨 TEMPLATES (Header/Footer) - Workflow

**Jak se pushují Templates:**

1. **Templates se pushují SAMOSTATNĚ přes `node sync.js push-templates`!**
   - Templates (Header/Footer) se pushují jinak než pages
   - Používají se jiné endpointy a post_type
   - **NEPUSHUJÍ se automaticky přes `npm run push`!**

2. **Workflow pro Templates:**

   **Krok 1: Načtení lokálních souborů:**
   - Skript načte header a footer soubory podle `config.json` → `mapping.components`
   - Cesta: `{projectPath}/{componentsPath}/{mapping.components.header}.json`
   - Cesta: `{projectPath}/{componentsPath}/{mapping.components.footer}.json`
   - Příklad: Pokud `mapping.components.header = "header"`, načte se `{projectPath}/header.json`

   **Krok 2: Kontrola existujících templates:**
   - Zavolá se endpoint: `GET /wp-json/bricks/v1/templates?type=header` nebo `type=footer`
   - Pokud template existuje → aktualizuje se
   - Pokud template neexistuje → vytvoří se nový

   **Krok 3: Příprava Bricks obsahu:**
   - Z JSON objektu se extrahuje pouze pole `content` (pole elementů)
   - Stejně jako u pages - Bricks očekává obsah jako pole elementů

   **Krok 4: Vytvoření/aktualizace Template:**
   - Zavolá se endpoint: `POST /wp-json/bricks/v1/template`
   - Post type: `bricks_template` (ne `page`!)
   - Meta klíče: stejné jako u pages (`_bricks_page_content`, `_bricks_page_content_2`)
   - **Dodatečné meta (specifické pro templates):**
     - `_bricks_template_type` = `'header'` nebo `'footer'`
     - `_bricks_template_active` = `true`
     - `_bricks_template_conditions` = `[]` (prázdné = použít všude)
     - `_bricks_editor_mode` = `'bricks'`

   **Krok 5: 🔐 AUTOMATICKÉ PODEPISOVÁNÍ CODE ELEMENTŮ:**
   - **DŮLEŽITÉ:** Po každém pushnutí se automaticky regenerují podpisy
   - Zavolá se endpoint: `POST /wp-json/bricks/v1/regenerate-signatures/{template_id}`
   - Pro každý code element se:
     - Vygeneruje podpis pomocí `wp_hash()` (WordPress funkce s HMAC-MD5)
     - Přidá `signature`, `user_id`, `time` do `settings` code elementu
     - Nastaví `executeCode: true`
   - **Bez podpisů code elementy nebudou fungovat!**

**Příkaz pro Templates:**
```bash
node sync.js push-templates
```

**Příklad výstupu:**
```
🎨 Push: Nahrávání Templates (Header/Footer)...
✅ Připojení úspěšné
📤 Nahrávám Header...
   ✅ Header vytvořen (ID: 103)
   🔐 Regeneruji podpisy kódu...
   ✅ Podepsáno X code elementů
📤 Nahrávám Footer...
   ✅ Footer vytvořen (ID: 104)
   🔐 Regeneruji podpisy kódu...
   ✅ Podepsáno X code elementů
```

**Rozdíly Pages vs Templates:**

| Aspekt | Pages | Templates |
|--------|-------|-----------|
| **Post Type** | `page` | `bricks_template` |
| **Push příkaz** | `npm run push`<br/>`node sync.js push` | `node sync.js push-templates` |
| **Soubory** | `{pagesPath}/*.json` | `{componentsPath}/{header}.json`, `{componentsPath}/{footer}.json` |
| **Mapping v config** | `mapping.pages` | `mapping.components` |
| **Meta klíče** | `_bricks_page_content`<br/>`_bricks_page_content_2` | `_bricks_page_content`<br/>`_bricks_page_content_2` |
| **Dodatečné meta** | `_bricks_editor_mode = 'bricks'`<br/>`_bricks_page_content_type = 'bricks'` | `_bricks_template_type = 'header'/'footer'`<br/>`_bricks_template_active = true`<br/>`_bricks_template_conditions = []`<br/>`_bricks_editor_mode = 'bricks'` |
| **API Endpoint** | `POST /wp-json/wp/v2/pages`<br/>`POST /wp-json/wp/v2/pages/{id}` | `POST /wp-json/bricks/v1/template` |
| **Podepisování** | ✅ Automatické po push | ✅ Automatické po push |
| **Endpoint pro podpisy** | `POST /bricks/v1/regenerate-signatures/{id}` | `POST /bricks/v1/regenerate-signatures/{id}` |
| **Kdy použít** | Všechny běžné stránky webu | Header a Footer (globální komponenty) |

**Důležité poznámky:**
- ⚠️ **Templates se NEPUSHUJÍ automaticky s pages!** Musí se pushnout samostatně
- ✅ **Podepisování funguje stejně** - automaticky po pushnutí
- 📁 **Templates jsou v root složce**, ne v `pages/`
- 🎯 **Templates se používají globálně** - header a footer se zobrazují na všech stránkách

---

### 1. Header a Footer (Templates) ⚠️ DŮLEŽITÉ
- **Pushnout SAMOSTATNĚ:** `node sync.js push-templates`
- Pushnout do Bricks Templates (Header/Footer type)
- **NE** jako WordPress stránky!
- Post type: `bricks_template` (ne `page`)
- Podepisování probíhá automaticky stejně jako u pages
- **NEPUSHUJÍ se automaticky s `npm run push`!**

### 2. Homepage
- Pushnout jako WordPress stránka s slug "homepage" nebo podobně
- Použít: `node sync.js push` (pokud je v `pages/` a mapping)
- Podepisování automatické po pushnutí

### 3. Ostatní stránky
- Pushnout jako WordPress stránky podle mapping v `config.json`
- Použít: `node sync.js push`
- Podepisování automatické po pushnutí

## 🛠️ Příkazy

### Push Pages (Stránky)
```bash
npm run push
# nebo
node sync.js push
```
Pushne všechny stránky z `pages/` složky podle mapping v `config.json`.

### Push Templates (Header/Footer)
```bash
node sync.js push-templates
```
Pushne header a footer jako Bricks templates. **NEPUSHUJE se automaticky s pages!**

### Push všeho (Pages + Templates)
```bash
node sync.js push-all
```
Pushne nejdřív všechny pages, pak templates. Užitečné pro kompletní synchronizaci.

### Aktualizovat Bricks téma
```bash
node sync.js update-bricks
```
Zkontroluje a aktualizuje Bricks téma, pokud je dostupná nová verze.

**⚠️ DŮLEŽITÉ:** Aktualizace Bricks tématu přes API může vyžadovat aktivní `bricks-api-endpoint.php` plugin a aktualizované permalinks.

**Pokud endpoint není dostupný (404):**
1. Zkontrolujte, zda je `bricks-api-endpoint.php` plugin aktivní
2. Aktualizujte permalinks: **Settings → Permalinks → Save Changes**
3. Nebo aktualizujte **ručně přes WordPress admin:**
   - Přejděte na **Appearance → Themes**
   - Najděte Bricks téma
   - Klikněte na **"Update now"** (žlutý banner s upozorněním)

### Pull z WordPressu
```bash
npm run pull
# nebo
node sync.js pull
```
Stáhne aktuální Bricks obsah ze všech stránek z WordPressu.

## ⚙️ Konfigurace

Soubor: `config.json` (v root složce toolu)

```json
{
  "wordpress": {
    "url": "https://your-wordpress-site.com",
    "username": "your-username",
    "applicationPassword": "xxxx xxxx xxxx xxxx"
  },
  "bricks": {
    "licenseKey": "your-license-key",
    "pluginZip": "bricks.2.0.zip"
  },
  "local": {
    "projectPath": "C:\\Users\\User\\Documents\\YourProject",
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
      "footer": "footer",
      "homepage": "homepage"
    }
  },
  "templateNames": {
    "header": "Header",
    "footer": "Footer"
  }
}
```

## 🔐 Autentizace

- Používá se **Application Password** (ne běžné heslo)
- Vytvořit v: WordPress Admin → Users → Your Profile → Application Passwords
- Název: "Fellaship Web Builder Tool"

## 📝 Bricks Templates API

Bricks Builder ukládá Templates jako custom post type `bricks_template`.

Pro push do Templates je potřeba:
1. Vytvořit post typu `bricks_template`
2. Nastavit meta `_bricks_template_type` na `header` nebo `footer`
3. Uložit Bricks obsah do `_bricks_page_content`

## 🐛 Řešení problémů

### Bricks nefunguje na stránkách
- Zkontrolovat: Bricks → Settings → Post types → Page musí být ZAPNUTO

### Header/Footer se nezobrazují
- Zkontrolovat, že jsou vytvořeny jako Templates (ne stránky)
- Zkontrolovat, že jsou publikované
- V Bricks nastavení zkontrolovat, které Templates jsou přiřazeny

### Chyba autentizace
- Zkontrolovat Application Password v `config.json`
- Vytvořit nový Application Password v WordPressu

## 🔐 Podepisování Code Elementů

### Proč je podepisování důležité?

Bricks vyžaduje podpisy pro všechny code elementy z bezpečnostních důvodů. Bez podpisů:
- ❌ Code elementy se nebudou spouštět
- ❌ Bricks editor může zobrazit chyby
- ❌ Stránka může mít critical error

### Jak funguje podepisování?

1. **Automatické podepisování Pages:**
   - Po každém `npm run push` se automaticky zavolá endpoint pro regeneraci podpisů
   - Pro každý code element se vygeneruje podpis pomocí `wp_hash()` (WordPress funkce)
   - Podpis se uloží do `element.settings.signature`
   - Přidá se `user_id` a `time` pro audit

2. **Podepisování Templates:**
   - Templates se podepisují stejně jako pages
   - Používá se stejný endpoint: `/bricks/v1/regenerate-signatures/{id}`
   - Musí se zavolat ručně po pushnutí template

3. **Formát podpisu:**
   ```json
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

### Manuální regenerace podpisů

Pokud potřebujete regenerovat podpisy ručně:

```bash
# Pro konkrétní stránku (ID: 75)
curl -X POST \
  -u "username:password" \
  https://darkgray-caribou-733262.hostingersite.com/wp-json/bricks/v1/regenerate-signatures/75

# Pro všechny stránky
curl -X POST \
  -u "username:password" \
  https://darkgray-caribou-733262.hostingersite.com/wp-json/bricks/v1/regenerate-all-signatures
```

## 📌 Důležité poznámky

1. **Bricks je TÉMA, ne plugin!**
2. **Header/Footer = Templates, ne stránky!**
3. **Po instalaci Bricks vždy aktivovat editaci stránek!**
4. **Používat Application Password pro autentizaci!**
5. **🔐 VŽDY podepisovat code elementy po pushnutí!** (automatické pro pages)
6. **Pages se podepisují automaticky, Templates je potřeba podepsat ručně**


