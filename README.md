# MaxHair.cz - Web Projekt

## 📋 Přehled projektu

Web pro MaxHair.cz vytvářený pomocí **Bricks Builder** na WordPressu. Projekt obsahuje kompletní homepage s 12 sekcemi a 15 podstránkami.

## 🎯 Klíčové informace

- **Platforma:** WordPress + Bricks Builder
- **Formát:** JSON struktura pro Bricks (sekce → kontejner → code blok)
- **Verze:** PC verze s responzivním designem
- **Škálování:** Dynamické škálování obsahu podle velikosti displeje pomocí `clamp()`
- **Lokální preview:** Funkční lokální hosting pro real-time zobrazení a editaci

## 📁 Struktura projektu

```
MaxHair/
├── sections/          # Sekce pro homepage (12 sekcí + sticky CTA)
├── pages/             # Podstránky (14 podstránek)
├── kontext/           # Kontextové informace, dokumentace
├── zadani/            # Zadání od klienta, analýzy
├── header-maxhair.json    # Header komponenta
├── footer-maxhair.json    # Footer komponenta
├── homepage-maxhair.json  # Hero sekce homepage
├── index.html            # HTML pro lokální preview
├── preview.js            # JavaScript pro lokální preview
├── preview.css           # CSS pro lokální preview
└── README.md            # Tento soubor
```

## 🏗️ Struktura Bricks elementů

Každá sekce má strukturu:
```
SEKCE (100vw)
  └── KONTEJNER (100vw)
      └── CODE blok (HTML + CSS + JS)
          - executeCode: true
          - HTML obsah
          - <style> blok s CSS
          - <script> blok s JavaScriptem
```

## 🎨 Design systém

### Barvy (CSS Variables)
- `--primary-gold: #E5C158` - Primární zlatá
- `--secondary-gold: #A67C00` - Sekundární zlatá
- `--dark-brown: #5A452C` - Tmavě hnědá (text)
- `--black: #000000` - Černá
- `--white: #FFFFFF` - Bílá
- `--light-gray: #F5F5F5` - Světle šedá (pozadí)
- `--medium-gray: #CCCCCC` - Středně šedá

### Fonty
- **Logo:** `Guton` (Google Fonts)
- **Nadpisy:** `Poppins` (300, 400, 500, 600, 700, 800)
- **Text:** `Inter` (300, 400, 500, 600, 700)

### Responzivní design
- Všechny velikosti používají `clamp(min, preferred, max)`
- Padding: `clamp(20px, 4vw, 80px)`
- Font sizes: `clamp(14px, 1.8vw, 18px)`
- Box-sizing: `border-box` pro všechny elementy
- Width: `100%; max-width: 100vw` (prevence horizontálního scrollování)

## 🔧 Technické funkce

### Header
- **Scroll behavior:** Zobrazuje se při scrollování nahoru, skrývá při scrollování dolů
- **Animace:** Otevírání z prostřed displeje do stran při zobrazení
- **Logo:** Ikona vlevo, dynamicky škálovaná
- **Navigace:** Domů, Služby (#sluzby), Metody (#metody), Reference, FAQ (/faq), Kontakt
- **Telefon:** +420 601 515 323
- **Šířka:** 50% obrazovky (70% na menších obrazovkách, 90% na mobilech)

### Sticky CTA
- **Umístění:** Pravý dolní roh
- **Viditelnost:** Na všech stránkách
- **Funkce:** Scrolluje na kontaktní sekci (#kontakt)

### Hash odkazy
- **Funkce:** Smooth scroll na sekce s offsetem pro fixed header
- **Cross-page:** Pokud je hash v URL, automaticky scrolluje po načtení stránky

## 📝 Homepage sekce

1. **Hero** - Úvodní sekce s videem, statistikami a CTA
2. **Problem** - Bolesti zákazníka
3. **Proč MaxHair** - 4 výhody (přímá klinika, česká podpora, vše zařídíme, osvědčené výsledky)
4. **Služby** - 5 služeb s fotkami (muži, ženy, vousy, obočí, PRP)
5. **Metody** - DHI a Sapphire FUE (zjednodušené, optimalizováno pro zobrazení "na první dobrou", CTA pod dlaždicemi)
6. **Jak to probíhá** - Accordion s 5 kroky
7. **Co je zahrnuto** - Minimalistická sekce s 8 položkami
8. **Ceník** - 3 balíčky (Economic, Standard Plus, Premium Care)
9. **Náš tým** - Skupinová fotka + popis
10. **Reference** - Před/po fotky + slider recenzí
11. **FAQ** - 5 nejdůležitějších otázek + odkaz na více
12. **Kontakt** - Formulář + Michaela s fotkou
13. **Sticky CTA** - Plovoucí tlačítko (na všech stránkách)

## 📄 Podstránky

### Služby (5 stránek)
- Transplantace vlasů - Muži
- Transplantace vlasů - Ženy
- Transplantace vousů
- Transplantace obočí
- PRP terapie

### Metody (2 stránky)
- Metoda DHI (sekce s videem a procesem: dva sloupce, video 16:9)
- Metoda Sapphire FUE (sekce s videem a procesem: dva sloupce, video 16:9 - stejná struktura jako DHI)

### Informace (3 stránky)
- Reference
- O nás
- Kontakt (minimalistická sekce z homepage + další sekce)

### Systémové (5 stránek)
- FAQ (30 otázek + kontaktní formulář)
- Děkujeme
- VOP (Všeobecné obchodní podmínky)
- GDPR (Ochrana osobních údajů)
- Cookies (Zásady používání cookies)

## 🚀 Lokální preview

Pro real-time zobrazení a editaci bez nutnosti nahrávat do Bricks:

1. **Spusť server:**
   ```bash
   # Windows
   start-server.bat
   
   # Unix/Mac
   ./start-server.sh
   ```

2. **Otevři v prohlížeči:**
   ```
   http://localhost:8000
   ```

3. **Funkce:**
   - Routing mezi stránkami
   - Hash odkazy (#kontakt, #faq, atd.)
   - Dynamické načítání JSON souborů
   - Real-time zobrazení změn po refresh

## 🔧 Technické poznámky

### Responzivní design
- Používej `clamp()` pro responzivní velikosti
- Všechny sekce: `width: 100%; max-width: 100vw; box-sizing: border-box;`
- Padding: `clamp(50px, 10vw, 100px) clamp(20px, 5vw, 80px)`
- Font sizes: `clamp(14px, 1.8vw, 18px)`

### Routing
- Hash odkazy (`#kontakt`, `#faq`) scrollují na sekce
- Odkazy na stránky (`/kontakt`, `/o-nas`) načítají JSON soubory
- Automatický scroll na hash po načtení stránky

### JSON struktura
- Všechny kódy jsou v CODE blocích s `executeCode: true`
- HTML, CSS a JS jsou v jednom stringu v `code` property
- Escape sekvence pro newlines: `\n`

## 📚 Klíčové soubory dokumentace

- `INDEX.md` - Kompletní index všech souborů
- `QUICK-START.md` - Rychlý start pro nové spuštění
- `HOMEPAGE-PREHLED.md` - Detailní přehled homepage
- `CO-CHYBI.md` - Seznam dokončených a chybějících stránek
- `README-PREVIEW.md` - Dokumentace lokálního preview systému
- `kontext/` - Všechny kontextové informace
- `zadani/` - Zadání a analýzy

## 📞 Kontaktní informace

- **Telefon:** +420 601 515 323
- **Email:** Michaela@maxhair.cz
- **Česká podpora:** Michaela (24/7)

## 💰 Ceník

### Economic Package
- **Cena:** 69,900 Kč (€2,900)
- Neomezený počet štěpů, 1 PRP, letenka, hotel, transfery

### Standard Plus Package (Nejoblíbenější)
- **Cena:** 81,900 Kč (€3,400)
- Vše z Economic + 6měsíční plán růstu vlasů

### Premium Care Package
- **Cena:** 89,200 Kč (€3,700)
- Vše z Standard Plus + 1roční plán údržby vlasů

---

**Vytvořeno:** 14. prosince 2025  
**Poslední aktualizace:** 1. ledna 2026
