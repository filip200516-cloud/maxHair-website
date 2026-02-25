# 📤 Pushování Templates (Header/Footer)

Tento dokument popisuje, jak správně pushovat Bricks Templates (Header a Footer) na WordPress web.

## ⚠️ DŮLEŽITÉ: Templates vs. Pages

**Templates se pushují JINAK než normální stránky!**

- **Pages** používají post type `page` a pushují se přes `node sync.js push`
- **Templates** používají post type `bricks_template` a pushují se přes `node sync.js push-templates`

## 📋 Workflow pro pushování Templates

### 1. Příprava lokálních souborů

Templates musí být v lokálním projektu:
- **Header:** `{projectPath}/{componentsPath}/{mapping.components.header}.json`
- **Footer:** `{projectPath}/{componentsPath}/{mapping.components.footer}.json`

**Příklad:**
- Pokud `config.json` má:
  ```json
  {
    "local": {
      "projectPath": "C:\\Users\\User\\Documents\\Project",
      "componentsPath": "."
    },
    "mapping": {
      "components": {
        "header": "header-maxhair",
        "footer": "footer-maxhair"
      }
    }
  }
  ```
- Pak se načtou soubory:
  - `C:\Users\User\Documents\Project\header-maxhair.json`
  - `C:\Users\User\Documents\Project\footer-maxhair.json`

### 2. Struktura JSON souboru

Templates používají **STEJNOU strukturu** jako pages:

```json
{
  "content": [
    {
      "id": "headersection",
      "name": "section",
      "parent": 0,
      "children": ["headercontainer"],
      "settings": {
        "_width": "100vw",
        "_position": "fixed",
        "_top": "0",
        "_zIndex": "1000"
      },
      "label": "Header | MaxHair"
    },
    {
      "id": "headercontainer",
      "name": "container",
      "parent": "headersection",
      "children": ["headercode"],
      "settings": {
        "_width": "100vw"
      }
    },
    {
      "id": "headercode",
      "name": "code",
      "parent": "headercontainer",
      "children": [],
      "settings": {
        "code": "<!-- HTML/CSS/JS -->",
        "executeCode": true,
        "signature": "",
        "user_id": 0,
        "time": 0
      }
    }
  ],
  "source": "bricksCopiedElements",
  "version": "2.0"
}
```

**⚠️ DŮLEŽITÉ:**
- Používej **CODE ELEMENTY** pro design (section → container → code)
- Struktura: `section` (root) → `container` → `code` (s HTML/CSS/JS)
- Každý element musí mít: `id`, `parent`, `children` jako pole stringů

### 3. Push příkaz

```bash
node sync.js push-templates
```

### 4. Co se děje při pushnutí

1. **Načtení lokálních souborů:**
   - Skript načte `header.json` a `footer.json` z `componentsPath`

2. **Kontrola existujících templates:**
   - Zavolá se: `GET /wp-json/bricks/v1/templates?type=header`
   - Zavolá se: `GET /wp-json/bricks/v1/templates?type=footer`
   - Pokud template existuje → aktualizuje se
   - Pokud template neexistuje → vytvoří se nový

3. **Příprava obsahu:**
   - Z JSON objektu se extrahuje pouze pole `content` (pole elementů)
   - Obsah se převede na JSON string a pošle se na server

4. **Vytvoření/aktualizace Template:**
   - Zavolá se: `POST /wp-json/bricks/v1/template`
   - **Post type:** `bricks_template` (ne `page`!)
   - **Meta klíče:**
     - `_bricks_page_content` = array elementů
     - `_bricks_page_content_2` = array elementů (backup)
     - `_bricks_template_type` = `'header'` nebo `'footer'`
     - `_bricks_template_active` = `true`
     - `_bricks_template_conditions` = `[]` (prázdné = použít všude)
     - `_bricks_editor_mode` = `'bricks'`

5. **🔐 Automatické podepisování:**
   - Zavolá se: `POST /wp-json/bricks/v1/regenerate-signatures/{template_id}`
   - Pro každý code element se vygeneruje podpis
   - **Bez podpisů code elementy nebudou fungovat!**
   - **⚠️ DŮLEŽITÉ:** Po pushnutí je potřeba **manuálně requestnout podpisy** v Bricks Settings → Templates → [Template] → Request Signatures

6. **DŮLEŽITÉ: Specifické meta klíče pro templates:**
   - Pro **Header templates:** Obsah se ukládá také do `_bricks_page_header_2`
   - Pro **Footer templates:** Obsah se ukládá také do `_bricks_page_footer_2`
   - Bricks editor hledá obsah v těchto specifických meta klíčích!
   - Bez těchto klíčů struktura nebude viditelná v Bricks editoru!

### 5. Očekávaný výstup

```
🎨 Push: Nahrávání Templates (Header/Footer)...

🔌 Testování připojení k WordPress API...
✅ Připojení úspěšné

📤 Nahrávám Header...
   ✅ Header aktualizován (ID: 108)
   🔐 Regeneruji podpisy kódu...
   ✅ Podepsáno 1 code elementů
📤 Nahrávám Footer...
   ✅ Footer aktualizován (ID: 109)
   🔐 Regeneruji podpisy kódu...
   ✅ Podepsáno 1 code elementů

📊 Shrnutí Templates:
   ✅ Aktualizováno: 2
   🆕 Vytvořeno: 0
   ❌ Chyby: 0
```

## 🔍 Kontrola po pushnutí

Po pushnutí zkontroluj v WordPress adminu:

1. **Bricks → Templates:**
   - Měly by být vidět Header a Footer templates
   - Template Type by měl být správně nastaven (Header/Footer)

2. **Otevření v Bricks editoru:**
   - Klikni na template v seznamu
   - Měla by se zobrazit **struktura v Structure panelu** (vpravo)
   - Pokud je Structure panel prázdný → template není správně uložený

3. **Frontend:**
   - Header a Footer by se měly zobrazit na webu automaticky
   - Bricks používá první publikovaný header/footer template jako default

## ❌ Časté problémy

### Problém 1: Structure panel je prázdný

**Příčina:** Obsah není správně uložený jako array elementů.

**Řešení:**
- Zkontroluj, zda JSON soubor má správnou strukturu (`content` jako pole)
- Zkontroluj, zda každý element má `id`, `parent`, `children` jako pole stringů
- Zkontroluj, zda se obsah ukládá do `_bricks_page_content_2` jako array

### Problém 2: Template není vidět na frontendu

**Příčina:** Template není aktivní nebo nemá správné conditions.

**Řešení:**
- Zkontroluj, zda `_bricks_template_active` = `true`
- Zkontroluj, zda `_bricks_template_conditions` = `[]` (prázdné = použít všude)
- Zkontroluj, zda template je publikovaný (`post_status = 'publish'`)

### Problém 3: Code elementy se nespouští

**Příčina:** Chybí podpisy code elementů.

**Řešení:**
- Zkontroluj, zda proběhlo podepisování (mělo by být v logu)
- Zkontroluj, zda `executeCode: true` je nastaveno
- Zkontroluj, zda `signature`, `user_id`, `time` jsou vyplněné

## 📝 Rozdíly: Pages vs. Templates

| Aspekt | Pages | Templates |
|--------|-------|-----------|
| **Post Type** | `page` | `bricks_template` |
| **Push příkaz** | `node sync.js push` | `node sync.js push-templates` |
| **Soubory** | `{pagesPath}/*.json` | `{componentsPath}/{header}.json`, `{componentsPath}/{footer}.json` |
| **Mapping v config** | `mapping.pages` | `mapping.components` |
| **Meta klíče** | `_bricks_page_content`<br/>`_bricks_page_content_2` | `_bricks_page_content`<br/>`_bricks_page_content_2`<br/>**+ `_bricks_page_header_2` (pro header)** ← **KLÍČOVÉ!**<br/>**+ `_bricks_page_footer_2` (pro footer)** ← **KLÍČOVÉ!** |
| **Dodatečné meta** | `_bricks_editor_mode = 'bricks'`<br/>`_bricks_page_content_type = 'bricks'` | `_bricks_template_type = 'header'/'footer'`<br/>`_bricks_template_active = true`<br/>`_bricks_template_conditions = []`<br/>`_bricks_editor_mode = 'bricks'` |
| **API Endpoint** | `POST /wp-json/wp/v2/pages`<br/>`POST /wp-json/wp/v2/pages/{id}` | `POST /wp-json/bricks/v1/template`<br/>`POST /wp-json/bricks/v1/template/{id}/content` |
| **Podepisování** | ✅ Automatické po push | ✅ Automatické po push<br/>⚠️ **+ manuální request v Bricks Settings → Templates → Request Signatures** |

## 🔑 KLÍČOVÝ ROZDÍL: Proč Templates potřebují speciální meta klíče

**DŮLEŽITÉ:** Bricks Builder používá **jiné meta klíče** pro header a footer templates než pro normální stránky!

### Pro Header Templates:
- ✅ `_bricks_page_content` (standardní)
- ✅ `_bricks_page_content_2` (standardní)
- ✅ **`_bricks_page_header_2`** ← **TOHLE JE KLÍČOVÉ!** Bricks editor hledá obsah v tomto klíči!

### Pro Footer Templates:
- ✅ `_bricks_page_content` (standardní)
- ✅ `_bricks_page_content_2` (standardní)
- ✅ **`_bricks_page_footer_2`** ← **TOHLE JE KLÍČOVÉ!** Bricks editor hledá obsah v tomto klíči!

### Proč to funguje:
Když vytvoříš template ručně v Bricks editoru, Bricks automaticky ukládá obsah do specifického meta klíče podle typu template:
- Header → `_bricks_page_header_2`
- Footer → `_bricks_page_footer_2`

Pokud obsah není v tomto specifickém klíči, Bricks editor ho **nevidí** v structure panelu, i když je správně uložený v `_bricks_page_content_2`!

### Workflow:
1. Uložit obsah do `_bricks_page_content` a `_bricks_page_content_2` (standardní)
2. **DŮLEŽITÉ:** Uložit také do `_bricks_page_header_2` (pro header) nebo `_bricks_page_footer_2` (pro footer)
3. Regenerovat podpisy
4. **DŮLEŽITÉ:** V Bricks Settings → Templates → [Template] → Request Signatures (manuálně)

## 🎯 Pro Cursor AI

**Při pushování templates:**

1. **VŽDY použij:** `node sync.js push-templates` (ne `node sync.js push`!)
2. **VŽDY zkontroluj:** Zda templates mají správnou strukturu (section → container → code)
3. **VŽDY ověř:** Zda proběhlo podepisování code elementů
4. **VŽDY načti:** `inspirace.json` jako referenci pro správnou strukturu

**Příklad promptu:**
```
Pushni Header a Footer templates na WordPress.
```

AI by mělo:
1. Zkontrolovat, zda existují soubory `header.json` a `footer.json` v `componentsPath`
2. Spustit: `node sync.js push-templates`
3. Ověřit výstup - mělo by být vidět "✅ Podepsáno X code elementů"
4. Informovat uživatele o výsledku

