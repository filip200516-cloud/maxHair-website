# 🚀 Automatický Setup - Fellaship Web Builder Tool

Tento dokument popisuje automatický workflow, který se spustí po pullnutí toolu z GitHubu.

## 📋 Workflow krok za krokem

### 1. Pull z GitHubu
Uživatel pullne tool z GitHubu do Cursoru pomocí:
```bash
git clone https://github.com/Fellaship/fellaship-web-builder-tool.git
```

### 2. Automatické napromptování Cursor AI

**Po pullnutí se Cursor AI automaticky "napromptuje" a řekne uživateli:**

> "Ahoj! Jsem Fellaship Web Builder Tool. Pomůžu ti vytvořit WordPress + Bricks Builder web.
> 
> **Co od tebe potřebuji:**
> 
> 1. **WordPress přístupy:**
>    - URL webu: `https://...`
>    - Username: `...`
>    - Application Password: `...` (jak vytvořit viz níže)
> 
> 2. **SSH přístupy (pokud máš):**
>    - SSH Host: `...`
>    - SSH Username: `...`
>    - SSH Password: `...`
>    - SSH Port: `...` (obvykle 22 nebo jiný)
> 
> 3. **Název projektu:**
>    - Název firmy/projektu: `...`
>    - GitHub repo název: `...` (založ nový repo na GitHubu)
> 
> **Jak vytvořit Application Password:**
> 1. Přihlas se do WordPress Adminu
> 2. Přejdi na: **Users → Your Profile**
> 3. Scrolluj dolů na sekci **"Application Passwords"**
>    - Pokud ji nevidíš, může být vypnutá v Hostinger nastavení
>    - Jdi do: **WordPress Dashboard → Hostinger záložka → Tools → scroll dolů → Application Passwords → Toggle ON**
> 4. Vrať se do: **Users → Your Profile → Application Passwords**
> 5. Zadej název: "Fellaship Web Builder Tool"
> 6. Klikni "Add New Application Password"
> 7. Zkopíruj heslo (zobrazí se jen jednou!)
> 
> **Jak založit GitHub repo:**
> 1. Jdi na GitHub.com
> 2. Klikni "New repository"
> 3. Název: `{nazev-firmy}-website` (např. "acme-website")
> 4. Vytvoř repo (může být private)
> 5. Dej mi odkaz na repo
> 
> Jakmile mi dáš všechny údaje, automaticky nastavím WordPress a můžeme začít tvořit web!"

### 3. Uložení přístupů

**Cursor AI automaticky uloží všechny přístupy do `přístupy.md`:**

```markdown
# Přístupy - {Název projektu}

## WordPress
- URL: https://example.com
- Username: admin
- Application Password: xxxx xxxx xxxx xxxx
- Vytvořeno: 2025-01-02

## SSH (pokud je)
- Host: 123.456.789.0
- Username: u123456789
- Password: ********
- Port: 65002

## GitHub
- Repo: https://github.com/user/project-website
- Název: project-website

## Projekt
- Název firmy: Acme Corp
- Lokální cesta: C:\Users\User\Documents\acme-website
```

### 4. Automatický setup WordPressu - FÁZE 1

**Cursor AI automaticky provede:**

1. **Vytvoří config.json** z `config.json.example` s údaji uživatele
2. **Nahraje plugin přes SSH:**
   - Nahrání `bricks-api-endpoint.php` na server přes SSH
   - Plugin je nahrán, ale **NENÍ aktivní**

**DŮLEŽITÉ:** Po nahrání pluginu AI **POZASTAVÍ** setup a řekne uživateli:

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

### 5. Automatický setup WordPressu - FÁZE 2 (po aktivaci pluginu)

**Když uživatel napíše, že plugin je aktivní, AI pokračuje:**

1. **Nainstaluje Bricks:**
   - `node sync.js install-bricks` (použije `bricks.2.0.zip` z toolu)
   - Aktivace Bricks tématu
   - Aktivace licence (použije `bricks.licenseKey` z config)
   - Aktualizace Bricks tématu
2. **Nastaví Bricks Settings:**
   - Code Execution: ON
   - Post types: Page (ON)
   - Post types: Post (podle potřeby)
3. **Nastaví WordPress Reading:**
   - Static page: Homepage (vytvoří novou stránku "Homepage")
4. **Vytvoří Templates:**
   - Header template (prázdný, připravený pro pozdější push)
   - Footer template (prázdný, připravený pro pozdější push)

**Po dokončení:**
> "✅ WordPress je připraven! Můžeme začít tvořit web.
> 
> **Co můžeme dělat:**
> - Vytvořit novou stránku (lokálně)
> - Upravit existující stránku (lokálně)
> - Pushnout změny na WordPress
> 
> Řekni mi, co chceš vytvořit!"

### 5. Tvorba stránky (lokálně)

**Workflow:**

1. **Uživatel řekne:** "Vytvoř stránku 'About'"
2. **Cursor AI:**
   - Vytvoří lokální strukturu projektu (pokud neexistuje)
   - Vytvoří `pages/about.json` s prázdnou Bricks strukturou
   - Spustí lokální server: `npm run dev` nebo podobně
   - Otevře prohlížeč s preview
3. **Uživatel upravuje stránku lokálně** (pomocí Cursor AI)
4. **Když je spokojený:** "Pushni to"
5. **Cursor AI:**
   - Pushne stránku na WordPress: `node sync.js push`
   - Commitne a pushne do GitHubu: `git add . && git commit && git push`

## 📁 Struktura projektu

Po setupu bude struktura:

```
{project-name}-website/
├── pages/
│   ├── homepage.json
│   ├── about.json
│   └── ...
├── sections/          # (volitelné)
├── header.json        # (vytvoří se později)
├── footer.json        # (vytvoří se později)
├── package.json
└── README.md
```

## 🔧 Automatické příkazy

Cursor AI bude automaticky používat:

```bash
# Setup
node sync.js setup

# Instalace Bricks
node sync.js install-bricks

# Push stránky
node sync.js push

# Push templates
node sync.js push-templates

# Lokální server (bude potřeba vytvořit)
npm run dev
```

