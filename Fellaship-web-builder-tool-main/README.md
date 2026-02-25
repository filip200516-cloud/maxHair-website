# 🛠️ Fellaship Web Builder Tool

**Fellaship Web Builder Tool** - Univerzální nástroj pro synchronizaci Bricks Builder struktury mezi lokálním projektem a WordPress webem.

Tento tool je navržen pro použití s **Cursor AI** - obsahuje dokumentaci a kontext pro efektivní automatizaci workflow pomocí AI asistenta.

## 🎯 Pro koho je tento tool

- **Vývojáři** používající Cursor AI pro tvorbu WordPress + Bricks Builder webů
- **Týmy Fellaship** potřebující efektivní synchronizaci obsahu
- **Kdokoliv** pracující s Bricks Builder a potřebující automatizaci

## 🤖 Použití s Cursor AI

### Workflow

1. **Pull z GitHubu:**
   ```bash
   git clone https://github.com/Fellaship/fellaship-web-builder-tool.git
   ```

2. **Automatické napromptování:**
   - Cursor AI automaticky načte `.cursor-initial-prompt.md`
   - AI řekne uživateli přesně, co potřebuje (přístupy, SSH, GitHub repo)
   - AI poskytne detailní instrukce jak získat Application Password

3. **Automatický setup:**
   - AI uloží přístupy do `přístupy.md`
   - AI vytvoří `config.json`
   - AI spustí `node setup-wordpress.js` (automaticky nastaví vše)

4. **Tvorba webu:**
   - Uživatel říká AI: "Vytvoř stránku X"
   - AI vytvoří JSON lokálně a spustí lokální server
   - Uživatel upravuje lokálně
   - Když řekne "pushni to", AI pushne na WordPress + GitHub

Více v: **`CURSOR-AI-GUIDE.md`** - kompletní průvodce pro AI

## 📋 Co tento repozitář obsahuje

### Hlavní soubory
- **sync.js** - Hlavní synchronizační skript
- **wp-api.js** - WordPress REST API klient
- **bricks-handler.js** - Handler pro práci s Bricks JSON daty
- **bricks-api-endpoint.php** - WordPress plugin pro Bricks meta API
- **config.json.example** - Příklad konfiguračního souboru

### Dokumentace pro AI
- **CURSOR-AI-GUIDE.md** - Kompletní průvodce pro Cursor AI (DŮLEŽITÉ!)
- **.cursorrules** - Pravidla a instrukce pro Cursor AI
- **QUICK-START-AI.md** - Rychlý start pro AI
- **AI-CONTEXT.md** - Klíčové informace pro AI

## 🚀 Rychlý start

1. **Nainstalujte závislosti:**
   ```bash
   npm install
   ```

2. **Vytvořte config.json:**
   ```bash
   cp config.json.example config.json
   ```
   A upravte s vašimi WordPress údaji.

3. **Spusťte setup:**
   ```bash
   node sync.js setup
   ```

4. **Použijte sync příkazy:**
   ```bash
   node sync.js pull   # Stáhnout z WordPressu
   node sync.js push   # Nahrát do WordPressu
   ```

## 📚 Dokumentace

### Pro uživatele
- **START-HERE.md** - Rychlý start guide
- **README.md** - Kompletní dokumentace
- **INSTALACE.md** - Instrukce pro instalaci WordPress pluginu
- **CO-DAL.md** - Co dál po setupu

### Pro Cursor AI
- **CURSOR-AI-GUIDE.md** - Kompletní průvodce pro AI (DŮLEŽITÉ!)
- **.cursorrules** - Pravidla a instrukce pro Cursor AI
- **QUICK-START-AI.md** - Rychlý start pro AI

## 🔐 Bezpečnost

- `config.json` je v `.gitignore` - neukládá se do Git
- Použijte `config.json.example` jako šablonu
- Citlivé údaje (hesla, API klíče) nikdy necommitněte

## 🔗 Integrace s projekty

Fellaship Web Builder Tool je univerzální nástroj pro synchronizaci Bricks Builder struktury s WordPress weby.

## 📖 Použití

### Lokální synchronizace

```bash
# Test připojení
node sync.js setup

# Stáhnout z WordPressu
node sync.js pull

# Nahrát Pages do WordPressu
node sync.js push

# Nahrát Templates (Header/Footer)
node sync.js push-templates

# Nahrát Pages + Templates
node sync.js push-all

# Aktualizovat Bricks téma
node sync.js update-bricks
```

### GitHub Actions

Fellaship Web Builder Tool může být integrován do jakéhokoliv WordPress projektu s Bricks Builder pomocí GitHub Actions.

## ⚙️ Konfigurace

Viz `config.json.example` pro strukturu konfiguračního souboru.

## 🐛 Řešení problémů

Viz `README.md` nebo `CO-DAL.md` pro detailní troubleshooting.

---

**Verze:** 1.0.0  
**Vytvořeno:** 2025-01-02  
**Autor:** Fellaship  
**Název projektu:** Fellaship Web Builder Tool
