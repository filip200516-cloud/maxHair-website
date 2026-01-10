# 🎨 Branding MaxHair.cz

**Datum vytvoření:** 14. prosince 2025  
**Aktualizováno:** 14. prosince 2025

---

## 🎯 Design styl

### Obecný styl
- **Moderní, čistý design**
- Inspirace: `maxhair-template.json` (design, který se klientovi líbí)
- Profesionální vzhled kliniky
- Luxusní, ale přístupný
- Elegantní, premium vzhled

### Design prvky z template
- Hero sekce s badge, statistikami
- Karty služeb s ikonami a hover efekty
- Čisté, minimalistické rozvržení
- Generous whitespace
- Smooth animace (fadeInUp, fadeInLeft, fadeInRight)
- Badge prvky pro sekce
- Statistiky s velkými čísly

---

## 🎨 Barevná paleta

### Primární zlatá (světlejší)
**Hex kód:** `#E5C158` (upraveno - světlejší než původní #D4AF37)  
**RGB:** rgb(229, 193, 88)  
**Použití:**
- Hlavní akcentní barva
- Nadpisy (H1, H2)
- Tlačítka (primární CTA)
- Ikony
- Zvýraznění důležitých informací
- Hrany prvků
- Hover efekty

### Sekundární zlatá
**Hex kód:** `#A67C00`  
**RGB:** rgb(166, 124, 0)  
**Použití:**
- Jemnější akcentní barva
- Drobné detaily
- Hover efekty (tmavší varianta)
- Oddělovače
- Popisy ikon
- Sekundární prvky

### Tmavě hnědá
**Hex kód:** `#5A452C`  
**RGB:** rgb(90, 69, 44)  
**Použití:**
- Texty (nadpisy, odstavce)
- Menší detaily
- Navigační prvky
- Rámečky
- Patka webu (footer)
- Hlavní textový obsah

### Černá
**Hex kód:** `#000000`  
**RGB:** rgb(0, 0, 0)  
**Použití:**
- Velmi omezeně
- Silný kontrast
- Důležité CTA (Call To Action) tlačítka
- Výrazné ikonografie
- Logo (pokud je černé)

### Bílá
**Hex kód:** `#FFFFFF`  
**RGB:** rgb(255, 255, 255)  
**Použití:**
- Pozadí stránek a sekcí
- Texty na tmavém pozadí
- Výplně prvků, které mají dýchat
- Karty, boxy
- Formuláře

### Světle šedá
**Hex kód:** `#F5F5F5`  
**RGB:** rgb(245, 245, 245)  
**Použití:**
- Alternativní pozadí pro sekce
- Vizuální oddělení obsahu
- Jemné oddělovače
- Pozadí formulářů
- Alternativní pozadí karet

### Středně šedá
**Hex kód:** `#CCCCCC`  
**RGB:** rgb(204, 204, 204)  
**Použití:**
- Nevýrazný text
- Pomocné informace
- Okraje
- Pozadí tlačítek v pasivním stavu
- Disabled prvky

---

## 📐 CSS Variables

```css
:root {
  /* Zlaté barvy */
  --primary-gold: #E5C158;
  --primary-gold-dark: #A67C00;
  --primary-gold-light: #F0D88A;
  
  /* Neutrální barvy */
  --dark-brown: #5A452C;
  --black: #000000;
  --white: #FFFFFF;
  --light-gray: #F5F5F5;
  --medium-gray: #CCCCCC;
  
  /* Textové barvy */
  --text-dark: #5A452C;
  --text-light: #CCCCCC;
  --text-on-dark: #FFFFFF;
  
  /* Background barvy */
  --bg-white: #FFFFFF;
  --bg-light: #F5F5F5;
  --bg-dark: #5A452C;
}
```

---

## 🎨 Použití barev v komponentách

### Tlačítka

**Primární CTA:**
```css
background: var(--primary-gold);
color: var(--white);
hover: var(--primary-gold-dark);
```

**Sekundární CTA:**
```css
background: transparent;
border: 2px solid var(--primary-gold);
color: var(--primary-gold);
hover: background var(--primary-gold);
```

**Černé CTA (důležité):**
```css
background: var(--black);
color: var(--white);
hover: var(--dark-brown);
```

### Nadpisy

**H1, H2:**
```css
color: var(--primary-gold);
```

**H3, H4:**
```css
color: var(--dark-brown);
```

### Karty

**Pozadí:**
```css
background: var(--white);
border: 1px solid var(--primary-gold);
```

**Hover:**
```css
border-color: var(--primary-gold-dark);
box-shadow: 0 4px 12px rgba(229, 193, 88, 0.2);
```

### Formuláře

**Input:**
```css
background: var(--bg-light);
border: 1px solid var(--medium-gray);
focus: border-color: var(--primary-gold);
```

---

## 📝 Typografie

### Font loga
**Guton** (vlastní font)
- Umístění: `D:\maxhair\Font\`
- Dostupné varianty:
  - Guton-Regular.otf
  - Guton-Medium.otf
  - Guton-SemiBold.otf
  - Guton-Bold.otf
  - Guton-ExtraBold.otf
  - Guton-Black.otf
- **Použití:** POUZE pro logo, ne pro obsah stránky

### Fonty pro web (clean)
- **Heading:** `Poppins` (Google Fonts) - čistý, moderní
  - Weights: 300, 400, 500, 600, 700, 800
- **Body:** `Inter` (Google Fonts) - maximálně čitelný
  - Weights: 300, 400, 500, 600, 700

### Velikosti
- Používat `clamp()` pro responzivní škálování
- Hierarchie: H1 → H6

### Barvy textu
- **Hlavní text:** `#5A452C` (tmavě hnědá)
- **Sekundární text:** `#CCCCCC` (středně šedá)
- **Nadpisy:** `#E5C158` (primární zlatá)
- **Text na tmavém:** `#FFFFFF` (bílá)

---

## 🖼️ Média a fotografie

### Dostupné fotografie
**Zdroj:** `media.txt`

**Prostory kliniky:**
1. **Recepce:** 
   - https://mediumseagreen-gaur-406121.hostingersite.com/wp-content/uploads/2025/12/0c8482ee-7c62-4bc2-85e6-ff374f727869-scaled.jpg
2. **Recepce (druhý pohled):**
   - https://mediumseagreen-gaur-406121.hostingersite.com/wp-content/uploads/2025/12/9a3d6798-e4a7-4479-9563-6cfd303cd15f-scaled.jpg
3. **Video z ordinace:**
   - https://mediumseagreen-gaur-406121.hostingersite.com/wp-content/uploads/2025/12/e638fe11-5724-4879-8264-d81b33a85de1.mp4
4. **Ordinace foto:**
   - https://mediumseagreen-gaur-406121.hostingersite.com/wp-content/uploads/2025/12/IMG_6580-scaled.jpg

**Videa z operací:**
1. **Hlavní video operace:**
   - Hlavní doktor skenuje vlasy, kreslí na hlavu, pacient jde do sálu, holí se mu hlava, sestry začínají pracovat
   - https://mediumseagreen-gaur-406121.hostingersite.com/wp-content/uploads/2025/12/WhatsApp-Video-2025-12-13-at-14.56.52_6de6a47b.mp4
2. **Anestezie (dermojet shots):**
   - Proces aplikace anestezie do hlavy
   - https://mediumseagreen-gaur-406121.hostingersite.com/wp-content/uploads/2025/12/vid-7.mov
3. **Transplantace (choi pen):**
   - Proces transplantace pomocí choi pen
   - https://mediumseagreen-gaur-406121.hostingersite.com/wp-content/uploads/2025/12/vid-5.mov

**Doktoři:**
1. **Zindan, Merve and Emine:**
   - Tři doktoři vedle sebe (žena - muž - žena)
   - https://mediumseagreen-gaur-406121.hostingersite.com/wp-content/uploads/2025/12/Zindan-Merve-and-Emine.jpg
2. **Dr Merve Altun:**
   - Profilová fotka doktorky s překříženýma rukama a úsměvem
   - https://mediumseagreen-gaur-406121.hostingersite.com/wp-content/uploads/2025/12/Dr-Merve-Altun.jpg
3. **Celý tým:**
   - Všichni doktoři vedle sebe, s překříženýma rukama, úsměvy, hlavní doktor uprostřed
   - https://mediumseagreen-gaur-406121.hostingersite.com/wp-content/uploads/2025/12/staff.jpg
4. **Dr Seyit Şahin:**
   - Hlavní doktor
   - https://mediumseagreen-gaur-406121.hostingersite.com/wp-content/uploads/2025/12/Dr-Seyit-Sahin.jpg

**Logo:**
1. **Pouze ikona:**
   - https://mediumseagreen-gaur-406121.hostingersite.com/wp-content/uploads/2025/12/MaxHair_logo_goldshadow.png
2. **Ikona a pod ní text (hero):**
   - https://mediumseagreen-gaur-406121.hostingersite.com/wp-content/uploads/2025/12/MaxHair_logo_hero_goldshadow-scaled.png
3. **Ikona a vpravo od ní text (side):**
   - https://mediumseagreen-gaur-406121.hostingersite.com/wp-content/uploads/2025/12/MaxHair_logo_side_goldshadow-scaled.png

**Před a po:**
- Stále zpracovává (klient dodává)

**Poznámka:** Pokud budou potřeba další konkrétní fotografie, není problém požádat klienta o další snímky.

---

## 🎯 Design principy

### 1. Luxusní, ale přístupný
- Zlatá barva = luxus, kvalita
- Čistý design = přístupnost
- Profesionální = důvěryhodnost

### 2. Moderní a čistý
- Minimalistický design
- Generous whitespace
- Jasná hierarchie

### 3. Důvěryhodnost
- Profesionální vzhled
- Kvalitní fotografie
- Transparentní informace

### 4. Konverze
- Výrazné CTA tlačítka (zlatá)
- Jasná navigace
- Snadný kontakt

---

## 📋 Checklist brandingu

### Barvy
- [x] Primární zlatá (#E5C158) - světlejší
- [x] Sekundární zlatá (#A67C00)
- [x] Tmavě hnědá (#5A452C)
- [x] Černá (#000000)
- [x] Bílá (#FFFFFF)
- [x] Světle šedá (#F5F5F5)
- [x] Středně šedá (#CCCCCC)

### Design
- [x] Moderní, čistý styl
- [x] Inspirace: maxhair-template.json
- [x] Logo (3 varianty k dispozici)
- [ ] Favicon (vytvořit z loga)

### Média
- [x] Přečten media.txt
- [x] Zkontrolovány dostupné fotografie
- [x] Prostory kliniky (4 fotky/videa)
- [x] Videa z operací (3 videa)
- [x] Doktoři (4 fotky)
- [x] Logo (3 varianty)
- [ ] Před/po fotky (klient stále zpracovává)
- [ ] Požádáno o další fotografie (pokud bude potřeba)

---

## 🔄 Aktualizace

**14. prosince 2025:**
- Přidána barevná paleta
- Upravena primární zlatá na světlejší (#E5C158)
- Přidány CSS variables
- Přidány design principy
- Přidány informace z maxhair-template.json
- Přidány všechny dostupné fotografie a videa z media.txt
- Přidány informace o logu (3 varianty)
- Přidány informace o doktorech

---

**Poznámka:** 
- Design inspirace: `maxhair-template.json` (přečteno, moderní čistý design)
- Média: `media.txt` (přečteno, všechny URL k dispozici)
- Před/po fotky: Klient stále zpracovává
- Logo: 3 varianty k dispozici (ikona, hero, side)

