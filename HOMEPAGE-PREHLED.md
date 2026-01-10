# 📄 Kompletní hlavní stránka MaxHair.cz - PŘEHLED

**Datum:** 30. prosince 2025  
**Status:** ✅ HOTOVO - Všech 12 sekcí + sticky CTA vygenerováno

---

## 📁 Struktura souborů

### Hlavní soubory:
1. `header-maxhair.json` - Header (sticky navigace, logo, telefon, CTA)
2. `footer-maxhair.json` - Footer (newsletter, kontakty, navigace)
3. `homepage-maxhair.json` - Hero sekce (úvodní)

### Sekce homepage (složka sections):
4. `sections/02-problem.json` - Problem sekce (bolesti zákazníka)
5. `sections/03-vyhody.json` - Proč MaxHair (4 výhody)
6. `sections/04-sluzby.json` - Naše služby (5 služeb s fotkami)
7. `sections/05-metody.json` - Metody (DHI vs Sapphire FUE - zjednodušené)
8. `sections/06-proces.json` - Jak to probíhá (accordion 5 kroků)
9. `sections/07-zahrnuto.json` - Co je zahrnuto (minimalistická sekce, 8 položek)
10. `sections/08-cenik.json` - Ceník (3 balíčky: Economic, Standard Plus, Premium Care)
11. `sections/09-tym.json` - Náš tým (skupinová fotka + popis)
12. `sections/10-reference.json` - Reference (před/po fotky + slider recenzí)
13. `sections/11-faq.json` - FAQ (5 nejdůležitějších otázek + odkaz na více)
14. `sections/12-kontakt.json` - Kontakt (formulář + Michaela s fotkou)
15. `sections/13-sticky-cta.json` - Sticky CTA (plovoucí tlačítko)

---

## 📊 Struktura hlavní stránky (od shora dolů)

### HEADER (scroll behavior, z-index: 1000)
- **Scroll behavior:** Zobrazuje se při scrollování nahoru, skrývá při scrollování dolů
- **Animace:** Otevírání z prostřed displeje do stran
- **Logo:** MaxHair ikona (vlevo, dynamicky škálovaná)
- **Navigace:** 
  - Domů (/) | Služby (#sluzby) | Metody (#metody) | Reference | FAQ (/faq) | Kontakt
- **Telefon:** +420 601 515 323
- **Šířka:** 50% obrazovky (70% na menších obrazovkách, 90% na mobilech)
- **Responzivní:** Mobile menu toggle

### SEKCE 1: HERO (100vh)
- **Headline:** 
  - "Transplantace vlasů bez prostředníka!"
  - "Kompletní servis za férovou cenu."
  - "Vše zahrnuto v ceně: letenka, ubytování v 5* hotelu, VIP transport a operace zkušenými doktory. S českou podporou Michaely na místě."
- **Zvýraznění:** Důležité slova zlatou barvou s text-shadow
- **2 CTA tlačítka:** "Bezplatná konzultace" + "Zjistit cenu"
- **3 statistiky:** 
  - 5,000+ Spokojených klientů (potřeba ověřit)
  - 95% Úspěšnost
  - 15+ Let zkušeností
- **Video na pozadí** (z operace)
- **Dynamické škálování:** Všechny velikosti pomocí `clamp()`

### SEKCE 2: PROBLEM (60vh)
- **3 bolesti zákazníka:**
  - Ztráta sebedůvěry
  - Neúspěšné léčby
  - Drahé řešení v ČR
- **Responzivní:** Grid 3 sloupce → 2 → 1

### SEKCE 3: PROČ MAXHAIR (80vh)
- **4 výhody (karty):**
  - Přímá klinika (ušetříte 30%)
  - Česká podpora (Michaela 24/7, SVG vlajka)
  - Vše zařídíme (letenka, hotel, transport)
  - Osvědčené výsledky (95% úspěšnost)
- **Zmenšené prvky:** Dynamické škálování
- **Responzivní:** Grid 4 sloupce → 2 → 1

### SEKCE 4: SLUŽBY (auto height)
- **5 služeb (karty s fotkami):**
  - Transplantace vlasů - Muži (od 69 900 Kč)
  - Transplantace vlasů - Ženy (od 69 900 Kč)
  - Transplantace vousů (od 69 900 Kč)
  - Transplantace obočí (od 69 900 Kč)
  - PRP terapie (zahrnuto v ceně)
- **Zmenšené karty:** Dynamické škálování
- **Responzivní:** Grid 3 sloupce → 2 → 1

### SEKCE 5: METODY (optimalizováno pro "na první dobrou")
- **Zjednodušená sekce:**
  - 2 metody (DHI, Sapphire FUE)
  - Základní info + obrázky (bez videí)
  - "Přečti si více →" odkazy
  - CTA "Která metoda je pro mě?" **pod dlaždicemi**
- **Optimalizováno:** Zmenšené padding, mezery, fonty a obrázky pro zobrazení na první pohled
- **Bez porovnávací tabulky**
- **Responzivní:** Grid 2 sloupce → 1

### SEKCE 6: JAK TO PROBÍHÁ (auto height)
- **Accordion 5 kroků:**
  1. Konzultace
  2. Příprava
  3. Operace
  4. Hojení
  5. Růst
- **Bez videa** (odstraněno)
- **CTA "Chci konzultaci"** pod accordionem
- **Responzivní:** Accordion funguje na všech zařízeních

### SEKCE 7: CO JE ZAHRNUTO (auto height)
- **Minimalistická sekce:**
  - 8 klíčových položek (místo 12)
  - Jednoduchý seznam s ikonami
  - Bez karet, stínů, hover efektů
  - SVG vlajka pro českou podporu
- **Responzivní:** Grid 4 sloupce → 2 → 1

### SEKCE 8: CENÍK (auto height)
- **3 balíčky vedle sebe:**
  - 🟢 Economic Package (69,900 Kč / €2,900)
  - 🔵 Standard Plus Package (81,900 Kč / €3,400) - Nejoblíbenější
  - 🟣 Premium Care Package (89,200 Kč / €3,700)
- **Každý balíček:** Badge, cena, seznam výhod, tlačítko
- **Bez CTA boxu** (odstraněno)
- **Responzivní:** Grid 3 sloupce → 2 → 1

### SEKCE 9: NÁŠ TÝM (auto height)
- **Zjednodušená sekce:**
  - Skupinová fotka (staff.jpg)
  - Krátký popis
  - Tlačítko "Poznejte náš tým" → /o-nas
- **Bez individuálních karet doktorů**
- **Responzivní:** Centrováno

### SEKCE 10: REFERENCE (auto height)
- **Před/po fotky:** Grid 4 sloupce (statické)
- **Recenze slider:**
  - Automatické posouvání (5 sekund)
  - Navigační šipky
  - Pagination dots
  - 6 recenzí
- **Responzivní:** 
  - Fotky: 4 → 2 → 1 sloupec
  - Slider: 3 → 2 → 1 karta

### SEKCE 11: FAQ (auto height)
- **5 nejdůležitějších otázek:**
  1. Kolik to stojí?
  2. Co je zahrnuto v ceně?
  3. Je to bolestivé?
  4. Jsou výsledky trvalé?
  5. Proč je vaše cena nižší než u konkurence?
- **Accordion funkce**
- **Odkaz:** "Zobrazit všechny FAQ" → /faq
- **Responzivní:** Accordion funguje na všech zařízeních

### SEKCE 12: KONTAKT (auto height)
- **Zjednodušená sekce:**
  - Formulář vlevo (jméno, email, telefon, zpráva, GDPR)
  - Michaela vpravo:
    - Placeholder pro fotku
    - Jméno, role, popis
    - Kontaktní odkazy (email, telefon, WhatsApp)
- **Bez lokace kliniky**
- **Bez otevírací doby**
- **Responzivní:** Grid 2 sloupce → 1

### STICKY CTA (plovoucí tlačítko)
- **Pozice:** Fixed bottom right
- **Z-index:** 998 (pod headerem)
- **Text:** "Bezplatná konzultace"
- **Odkaz:** #kontakt
- **Responzivní:** Zobrazuje se na všech zařízeních

### FOOTER
- **Logo MaxHair**
- **O nás text**
- **Newsletter** (10% sleva)
- **Sociální sítě**
- **Navigace** (služby, metody, reference, FAQ, kontakt)
- **Kontaktní údaje** (telefon, email)
- **Právní odkazy** (VOP, GDPR, Cookies)
- **Copyright**

---

## 🎨 Design prvky

### Barvy
- Primary Gold: `#E5C158`
- Secondary Gold: `#A67C00`
- Dark Brown: `#5A452C`
- White: `#FFFFFF`
- Light Gray: `#F5F5F5`
- Medium Gray: `#CCCCCC`

### Fonty
- Logo: `Guton`
- Nadpisy: `Poppins` (400, 600, 700, 800)
- Text: `Inter` (400, 500, 600, 700)

### Responzivní design
- Všechny velikosti: `clamp(min, preferred, max)`
- Padding: `clamp(50px, 10vw, 100px) clamp(20px, 5vw, 80px)`
- Font sizes: `clamp(14px, 1.8vw, 18px)`
- Box-sizing: `border-box`
- Width: `100%; max-width: 100vw` (prevence horizontálního scrollování)

---

## 📞 Kontaktní informace

- **Telefon:** +420 601 515 323
- **Email:** Michaela@maxhair.cz
- **Česká podpora:** Michaela (24/7)
- **WhatsApp:** +420 601 515 323

---

## 💰 Ceník (3 balíčky)

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

## ✅ Checklist před spuštěním

### Technické
- [x] Všechny sekce mají `width: 100%; max-width: 100vw; box-sizing: border-box;`
- [x] Dynamické škálování pomocí `clamp()`
- [x] Hash odkazy fungují (#kontakt, #faq, #sluzby, #metody)
- [x] Routing mezi stránkami funguje
- [x] Responzivní design na všech zařízeních
- [x] Lokální preview funguje

### Obsah
- [x] Telefonní číslo aktualizováno: +420 601 515 323
- [x] Ceny aktualizovány (3 balíčky)
- [x] Úspěšnost: 95% (místo 98%)
- [x] Čísla označena "(potřeba ověřit)" kde je potřeba
- [x] SVG vlajka místo emoji 🇨🇿

### Design
- [x] Logo centrované, s textem
- [x] Navigace zarovnaná doleva
- [x] Sekce zmenšené a optimalizované
- [x] Minimalistický design kde je potřeba
- [x] Slider recenzí místo statických karet

---

## 📝 Import do WordPress

1. **Import header:**
   - Bricks → Templates → Import → `header-maxhair.json`

2. **Import footer:**
   - Bricks → Templates → Import → `footer-maxhair.json`

3. **Import homepage:**
   - Vytvoř novou stránku "Homepage"
   - Import `homepage-maxhair.json` jako sekci
   - Import všechny sekce z `sections/` ve správném pořadí

4. **Import podstránek:**
   - Vytvoř nové stránky podle URL
   - Import JSON souborů z `pages/`

---

**Status:** ✅ **HOMEPAGE KOMPLETNÍ**

**Poslední aktualizace:** 1. ledna 2026
