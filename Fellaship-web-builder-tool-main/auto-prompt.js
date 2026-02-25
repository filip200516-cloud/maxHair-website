/**
 * Automatický prompt pro Cursor AI
 * Tento soubor se spustí automaticky po pullnutí toolu z GitHubu
 */

import fs from 'fs-extra';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const PROMPT_FILE = path.join(__dirname, '.cursor-initial-prompt.md');
const PRÍSTUPY_FILE = path.join(__dirname, 'přístupy.md');

// Automatický prompt pro Cursor AI
const AUTO_PROMPT = `# 🚀 Fellaship Web Builder Tool - Automatický Setup

Ahoj! Jsem **Fellaship Web Builder Tool**. Pomůžu ti vytvořit WordPress + Bricks Builder web.

## 📋 Co od tebe potřebuji

### 1. WordPress přístupy
- **URL webu:** \`https://...\`
- **Username:** \`...\`
- **Application Password:** \`...\` (jak vytvořit viz níže)

### 2. SSH přístupy (pokud máš)
- **SSH Host:** \`...\`
- **SSH Username:** \`...\`
- **SSH Password:** \`...\`
- **SSH Port:** \`...\` (obvykle 22 nebo jiný)

### 3. Název projektu
- **Název firmy/projektu:** \`...\`
- **GitHub repo název:** \`...\` (založ nový repo na GitHubu)

---

## 📝 Jak vytvořit Application Password

**⚠️ DŮLEŽITÉ: Application Password musí být nejdřív ZAPNUTO v Hostingeru!**

### Krok 1: Zapnout Application Passwords v Hostingeru (POVINNÉ!)

**Pokud tento krok přeskočíš, Application Password nebudeš moci vytvořit!**

1. Přihlas se do **WordPress Adminu** (např. \`https://tvuj-web.com/wp-admin\`)
2. V **levém menu** klikni na záložku **"Hostinger"** (nebo "hPanel")
3. Klikni na **"Tools"**
4. Scrolluj **dolů** na sekci **"Application Passwords"**
5. **Pokud je Toggle OFF (šedý/vypnutý):**
   - **KLIKNI NA TOGGLE** a zapni ho (mělo by se změnit na zelené/ON)
   - **Ulož změny** (pokud je tlačítko "Save" nebo "Update")
6. **Pokud je už Toggle ON (zelený/zapnutý):** Můžeš pokračovat na Krok 2

### Krok 2: Vytvoř Application Password

1. Přejdi na: **Users → Your Profile** (nebo klikni na své jméno v pravém horním rohu WordPress adminu)
2. Scrolluj **dolů** na sekci **"Application Passwords"**
   - **Pokud tuto sekci nevidíš:** Vrať se na Krok 1 a ujisti se, že je Application Passwords zapnuté v Hostingeru!
3. Do pole **"New Application Password Name"** zadej: **"Fellaship Web Builder Tool"**
4. Klikni **"Add New Application Password"**
5. **⚠️ DŮLEŽITÉ:** Zkopíruj heslo **HNED** - zobrazí se jen jednou a už ho neuvidíš!
6. Heslo bude ve formátu: \`xxxx xxxx xxxx xxxx xxxx xxxx\` (s mezerami)
7. Vlož ho do odpovědi, když mi budeš posílat přístupy

---

## 🔗 Jak založit GitHub repo

1. Jdi na **GitHub.com**
2. Klikni **"New repository"** (nebo zelené tlačítko "+" → "New repository")
3. **Repository name:** \`{nazev-firmy}-website\` (např. "acme-website")
4. Můžeš nastavit jako **Private** (doporučeno)
5. **NEMUSÍŠ** inicializovat s README, .gitignore, nebo licencí
6. Klikni **"Create repository"**
7. Dej mi **URL repo** (např. \`https://github.com/user/acme-website\`)

---

## ✅ Co se stane po zadání údajů

Jakmile mi dáš všechny údaje, automaticky:

1. ✅ Vytvořím \`config.json\` s tvými údaji
2. ✅ Uložím přístupy do \`přístupy.md\` (pro budoucí použití)
3. ✅ Nainstaluju a aktivuji plugin \`bricks-api-endpoint.php\`
4. ✅ Nainstaluju Bricks Builder téma
5. ✅ Aktivuji Bricks licenci
6. ✅ Aktualizuji Bricks téma
7. ✅ Nastavím Bricks Settings (Code Execution, Post types)
8. ✅ Vytvořím Homepage stránku
9. ✅ Nastavím WordPress Reading (Static page = Homepage)
10. ✅ Vytvořím prázdné Templates (Header, Footer)

**Po dokončení ti napíšu:** "✅ WordPress je připraven! Můžeme začít tvořit web."

---

## 🎯 Co můžeme dělat potom

- Vytvořit novou stránku (lokálně)
- Upravit existující stránku (lokálně)
- Pushnout změny na WordPress
- Pushnout změny na GitHub

**Řekni mi všechny údaje a začneme!** 🚀
`;

// Uložit prompt do souboru
await fs.writeFile(PROMPT_FILE, AUTO_PROMPT, 'utf-8');

console.log('✅ Automatický prompt vytvořen!');
console.log('📄 Soubor: .cursor-initial-prompt.md');
console.log('\n💡 Cursor AI by měl automaticky načíst tento prompt po pullnutí toolu.');

