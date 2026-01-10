# 💡 Konkrétní doporučení pro MaxHair.cz

**Vytvořeno:** 14. prosince 2025  
**Založeno na:** Analýze 5 konkurenčních webů

---

## 🎨 Design doporučení

### Barevná paleta
**Doporučení:**
- **Primární barva:** Teplá, důvěryhodná (např. tmavě modrá, zelená, nebo oranžová)
- **Sekundární barva:** Komplementární k primární
- **Pozadí:** Bílá nebo velmi světlá šedá
- **Text:** Tmavě šedá (#333333) pro hlavní text, středně šedá (#666666) pro sekundární

**Inspirace:**
- ABClinic: Bílá + modrá (profesionální, lékařské)
- Hair Again: Černá + šedá (moderní, minimalistické)
- Premier Clinic: Bílá + zlatá (premium, luxusní)

### Typografie
**Doporučení:**
- **Heading font:** Moderní, bezpatkové (Poppins, Montserrat, Inter)
- **Body font:** Čitelný, bezpatkové (Inter, Open Sans, Roboto)
- **Velikosti:** Používat clamp() pro responzivní škálování
- **Hierarchie:** Jasná hierarchie H1 → H6

### Layout
**Doporučení:**
- **Hero sekce:** 100vh, velký headline, CTA tlačítko
- **Sekce:** 100vh nebo auto-height podle obsahu
- **Max-width kontejneru:** 1200px - 1400px
- **Padding:** Generous padding (40-60px na desktopu)

---

## 📐 Struktura webu - doporučené sekce

### 1. Hero sekce (100vh)
**Obsah:**
- Velký headline (např. "Vraťte si husté vlasy s MaxHair")
- Podnadpis s hodnotovou proposicí
- CTA tlačítko "Bezplatná konzultace"
- Možná statistiky (počet klientů, úspěšnost)

**Design:**
- Velký, poutavý obrázek/video na pozadí
- Overlay pro čitelnost textu
- Centrování obsahu

### 2. O nás / Proč MaxHair (80vh)
**Obsah:**
- Co dělá MaxHair jinak?
- Klíčové výhody (3-4 karty)
- Statistiky (počet klientů, zkušenosti, úspěšnost)

**Design:**
- Karty s ikonami
- Číselné statistiky
- Animace při scrollování

### 3. Naše služby (80vh)
**Obsah:**
- Transplantace vlasů (muži)
- Transplantace vlasů (ženy)
- Transplantace vousů
- Další služby (pokud jsou)

**Design:**
- Grid 3-4 karty
- Obrázky zákroků
- Ceny (pokud jsou transparentní)
- CTA "Zjistit více" na každé kartě

### 4. Jak to probíhá / Proces (80vh)
**Obsah:**
- Krok 1: Konzultace
- Krok 2: Příprava
- Krok 3: Zákrok
- Krok 4: Rekonvalescence
- Krok 5: Výsledky

**Design:**
- Timeline nebo kroky vedle sebe
- Ikony pro každý krok
- Popis každého kroku

### 5. Metoda / Technologie (60vh)
**Obsah:**
- Jaká metoda se používá? (DHI, FUE, atd.)
- Proč je tato metoda lepší?
- Technologie a vybavení

**Design:**
- Velký obrázek/video
- Text vedle obrázku
- Možná animace

### 6. Ceník (80vh)
**Obsah:**
- Transparentní ceník (jako Hair Again)
- Co je zahrnuto v ceně
- Možnost financování (pokud je)

**Design:**
- Karty s cenami
- Seznam co je zahrnuto
- CTA "Poptat" na každé kartě

### 7. Reference / Recenze (60vh)
**Obsah:**
- Recenze klientů
- Před/po fotky
- Video recenze (pokud jsou)

**Design:**
- Grid s recenzemi
- Fotky klientů
- Hvězdičky hodnocení
- Možná karusel

### 8. Tým (60vh)
**Obsah:**
- Představení lékařů
- Zkušenosti a certifikace
- Fotky týmu

**Design:**
- Karty s fotkami
- Jméno, specializace, zkušenosti
- Možná animace

### 9. FAQ (95vh - expandable)
**Obsah:**
- Často kladené otázky
- Odpovědi na otázky
- Možnost přidat vlastní otázku

**Design:**
- Accordion design
- Expandable answers
- Snadné vyhledávání

### 10. Kontakt (60vh)
**Obsah:**
- Kontaktní formulář
- Telefon, email, adresa
- Mapa (pokud je fyzická klinika)
- Otevírací doba

**Design:**
- Formulář vlevo, info vpravo
- CTA tlačítko na odeslání
- Validace formuláře

---

## 🎯 CTA (Call-to-Action) strategie

### Primární CTA
- **"Bezplatná konzultace"** - hlavní CTA na hero sekci
- **"Poptat"** - na ceníku
- **"Objednat se"** - na službách

### Sekundární CTA
- **"Zjistit více"** - na službách
- **"Kontaktovat nás"** - v footeru
- **"Zavolat"** - telefon v headeru

### Umístění CTA
- Hero sekce (primární)
- Po každé sekci služeb
- V sidebaru (sticky)
- V footeru
- Floating button (mobil)

---

## 📱 UX best practices

### Navigace
- **Jasná, jednoduchá navigace** - max 7 položek
- **Sticky header** - vždy viditelný
- **Breadcrumbs** - na podstránkách
- **Mobilní menu** - hamburger menu

### Formuláře
- **Krátké formuláře** - jen nezbytné údaje
- **Validace v reálném čase** - okamžitá zpětná vazba
- **Děkujeme stránka** - po odeslání
- **GDPR souhlas** - checkbox

### Rychlost
- **Optimalizované obrázky** - WebP formát
- **Lazy loading** - načítání při scrollování
- **Minimalizace JavaScriptu** - jen nutné
- **CDN** - pro rychlé načítání

### Důvěryhodnost
- **Certifikace** - logo certifikátů
- **Recenze** - skutečné recenze
- **Garance** - co garantujete
- **Bezpečnost** - SSL, GDPR

---

## 💰 Cenová strategie

### Transparentní ceník (doporučeno)
**Výhody:**
- ✅ Buduje důvěru
- ✅ Filtruje klienty (ti, co si to nemohou dovolit, nevolají)
- ✅ Šetří čas (méně telefonátů s dotazy na cenu)

**Formát:**
- Karty s cenami (jako Hair Again)
- Co je zahrnuto v ceně
- Možnost "od X Kč" pokud je cena variabilní

### Individuální ceník
**Výhody:**
- ✅ Flexibilita
- ✅ Možnost slev pro VIP klienty

**Nevýhody:**
- ❌ Méně transparentní
- ❌ Více telefonátů s dotazy

**Doporučení:** Kombinace - základní ceník transparentní, detailní cena po konzultaci

---

## 🔍 SEO doporučení

### Klíčová slova
- Transplantace vlasů
- Transplantace vlasů Praha
- Transplantace vlasů ČR
- DHI metoda
- FUE metoda
- Cena transplantace vlasů

### Obsah
- **Blog sekce** - články o transplantaci vlasů
- **FAQ** - odpovědi na otázky (SEO friendly)
- **Lokální SEO** - pokud je fyzická klinika

### Technické SEO
- **Structured data** - Schema.org markup
- **Sitemap** - XML sitemap
- **Robots.txt** - správně nastavený
- **Meta tagy** - title, description

---

## 📊 Měření a analýza

### Google Analytics
- Sledování návštěvnosti
- Konverze (formuláře, telefonáty)
- Zdroje trafficu

### Hotjar / Microsoft Clarity
- Heatmaps - kde klikají uživatelé
- Session recordings - jak se pohybují
- Conversion funnels - kde opouštějí

### A/B testování
- Různé verze CTA tlačítek
- Různé verze hero sekce
- Různé verze formulářů

---

## 🚀 Priorita implementace

### Fáze 1 (MVP - Minimum Viable Product)
1. ✅ Hero sekce
2. ✅ O nás / Proč MaxHair
3. ✅ Služby
4. ✅ Ceník
5. ✅ Kontakt

### Fáze 2 (Rozšíření)
6. ✅ Jak to probíhá
7. ✅ Reference / Recenze
8. ✅ FAQ
9. ✅ Tým

### Fáze 3 (Optimalizace)
10. ✅ Blog
11. ✅ Před/po galerie
12. ✅ Online objednávání
13. ✅ Chat widget

---

## 📝 Checklist před spuštěním

### Obsah
- [ ] Všechny texty zkontrolované (gramatika, pravopis)
- [ ] Všechny obrázky optimalizované
- [ ] Všechny odkazy funkční
- [ ] Kontaktní údaje správné

### Technické
- [ ] Responzivní na všech zařízeních
- [ ] Rychlé načítání (< 3 sekundy)
- [ ] SEO optimalizované
- [ ] GDPR compliant
- [ ] SSL certifikát

### Funkčnost
- [ ] Formuláře fungují
- [ ] CTA tlačítka vedou na správné místo
- [ ] Navigace funguje
- [ ] Mobilní menu funguje

### Testování
- [ ] Testováno na různých prohlížečích
- [ ] Testováno na různých zařízeních
- [ ] Testováno s různými uživateli
- [ ] A/B testování připraveno

---

**Poznámka:** Tato doporučení jsou založena na analýze konkurence a best practices. Přizpůsobte je specifickým potřebám MaxHair.cz.

