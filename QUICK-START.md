# 🚀 Quick Start - MaxHair.cz

**Poslední aktualizace:** 30. prosince 2025

---

## Rychlá orientace v projektu

### Co je tento projekt?
Web pro MaxHair.cz vytvářený pomocí **Bricks Builder** na WordPressu. Všechny sekce jsou vytvářeny jako CODE bloky s HTML, CSS a JavaScriptem. Projekt obsahuje kompletní homepage (12 sekcí) a 14 podstránek.

### Klíčové soubory
- `README.md` - Přehled projektu
- `INDEX.md` - Kompletní index všech souborů
- `HOMEPAGE-PREHLED.md` - Detailní přehled homepage
- `CO-CHYBI.md` - Seznam dokončených stránek
- `kontext/` - Všechny kontextové informace
  - `struktura-bricks.md` - Jak funguje Bricks Builder
  - `design-system.md` - Barvy, fonty, komponenty
  - `pracovni-postup.md` - Jak vytvářet sekce
  - `maxhair-kontext.md` - Kontext o MaxHair.cz
  - `marketing-strategie.md` - Marketingová strategie
  - `cenotvorba-maxhair.md` - Ceník (3 balíčky)
- `zadani/` - Zadání a požadavky od klienta

### Struktura Bricks
```
SEKCE (100vw)
  └── KONTEJNER (100vw)
      └── CODE blok (HTML + CSS + JS)
          - executeCode: true
```

### Design systém

#### Barvy (CSS Variables)
- `--primary-gold: #E5C158` - Primární zlatá
- `--secondary-gold: #A67C00` - Sekundární zlatá
- `--dark-brown: #5A452C` - Tmavě hnědá
- `--white: #FFFFFF` - Bílá
- `--light-gray: #F5F5F5` - Světle šedá

#### Fonty
- **Logo:** `Guton`
- **Nadpisy:** `Poppins` (400, 600, 700, 800)
- **Text:** `Inter` (400, 500, 600, 700)

#### Škálování
- Dynamické pomocí `clamp(min, preferred, max)`
- Padding: `clamp(50px, 10vw, 100px) clamp(20px, 5vw, 80px)`
- Font sizes: `clamp(14px, 1.8vw, 18px)`
- Box-sizing: `border-box` pro všechny elementy

### Lokální preview

**Spuštění:**
```bash
# Windows
start-server.bat

# Unix/Mac
./start-server.sh
```

**Otevři:** `http://localhost:8000`

**Funkce:**
- ✅ Routing mezi stránkami
- ✅ Hash odkazy (#kontakt, #faq) s smooth scroll
- ✅ Real-time zobrazení změn
- ✅ Dynamické načítání JSON
- ✅ Sticky CTA na všech stránkách
- ✅ Header scroll behavior (show/hide)

### Pracovní postup
1. Vytvoř SEKCI v Bricks
2. Přidej KONTEJNER
3. Přidej CODE blok
4. Napiš HTML + CSS + JS
5. Testuj škálování
6. Exportuj JSON

### Důležité!
- ✅ Responzivní design povinný (`clamp()`)
- ✅ `width: 100%; max-width: 100vw; box-sizing: border-box;`
- ✅ `executeCode: true` v CODE bloku
- ✅ Hash odkazy scrollují na sekce
- ✅ Dynamické škálování všech velikostí

### Kde najdu co?

#### Dokumentace
- **Struktura Bricks:** `kontext/struktura-bricks.md`
- **Barvy a fonty:** `kontext/design-system.md`
- **Jak vytvářet sekce:** `kontext/pracovni-postup.md`
- **Template:** `kontext/template-sekce.md`

#### Kontext
- **O MaxHair:** `kontext/maxhair-kontext.md`
- **Marketing:** `kontext/marketing-strategie.md`
- **Ceník:** `kontext/cenotvorba-maxhair.md`
- **Média:** `kontext/media-seznam.md`

#### Zadání
- **Poznámky:** `zadani/poznamky.md`
- **Analýza:** `zadani/SOUHRN-ANALYZY.md`
- **Doporučení:** `zadani/doporuceni-pro-maxhair.md`

### Homepage sekce (12 sekcí)
1. Hero (homepage-maxhair.json)
2. Problem (02-problem.json)
3. Proč MaxHair (03-vyhody.json)
4. Služby (04-sluzby.json)
5. Metody (05-metody.json)
6. Jak to probíhá (06-proces.json)
7. Co je zahrnuto (07-zahrnuto.json)
8. Ceník (08-cenik.json)
9. Náš tým (09-tym.json)
10. Reference (10-reference.json)
11. FAQ (11-faq.json)
12. Kontakt (12-kontakt.json)
13. Sticky CTA (13-sticky-cta.json)

### Podstránky (15 stránek)
- ✅ Všechny podstránky vygenerovány
- **FAQ stránka:** 30 otázek + kontaktní formulář
- **Kontakt stránka:** Minimalistická sekce z homepage + další sekce
- Viz `CO-CHYBI.md` pro kompletní seznam

---

**Poslední aktualizace:** 1. ledna 2026
