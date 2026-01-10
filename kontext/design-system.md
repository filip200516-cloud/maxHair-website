# Design System - MaxHair.cz

**Aktualizováno:** 14. prosince 2025  
**Barvy:** Zlatá paleta (luxusní, premium)

---

## 🎨 Barevná paleta

### Zlaté barvy (primární)
```css
--primary-gold: #E5C158;        /* Světlejší zlatá - hlavní akcent */
--primary-gold-dark: #A67C00;  /* Tmavší zlatá - hover, detaily */
--primary-gold-light: #F0D88A; /* Světlá zlatá - jemné akcenty */
```

### Neutrální barvy
```css
--dark-brown: #5A452C;         /* Tmavě hnědá - texty, navigace */
--black: #000000;              /* Černá - důležité CTA, kontrast */
--white: #FFFFFF;              /* Bílá - pozadí, texty na tmavém */
--light-gray: #F5F5F5;         /* Světle šedá - alternativní pozadí */
--medium-gray: #CCCCCC;        /* Středně šedá - pomocné prvky */
```

### Textové barvy
```css
--text-dark: #5A452C;          /* Hlavní text (tmavě hnědá) */
--text-light: #CCCCCC;         /* Sekundární text (středně šedá) */
--text-on-dark: #FFFFFF;       /* Text na tmavém pozadí (bílá) */
--text-gold: #E5C158;          /* Zlatý text (nadpisy) */
```

### Background barvy
```css
--bg-white: #FFFFFF;           /* Bílé pozadí */
--bg-light: #F5F5F5;           /* Světle šedé pozadí */
--bg-dark: #5A452C;            /* Tmavě hnědé pozadí */
--bg-gold: #E5C158;            /* Zlaté pozadí (akcenty) */
```

## 📝 Typografie

### Font loga (vlastní)
```css
@font-face {
  font-family: 'Guton';
  src: url('fonts/Guton-Bold.otf') format('opentype');
  font-weight: 700;
  font-display: swap;
}
```
- **Guton** - font použitý v logu MaxHair
- Umístění: `D:\maxhair\Font\`
- Varianty: Regular, Medium, SemiBold, Bold, ExtraBold, Black
- **POUZE pro logo** - nepoužívat na stránce

### Fonty pro web (clean)
- **Heading:** `Poppins` (Google Fonts)
  - Weights: 300, 400, 500, 600, 700, 800
  - Použití: Nadpisy (H1-H6), velké texty
- **Body:** `Inter` (Google Fonts)
  - Weights: 300, 400, 500, 600, 700
  - Použití: Odstavce, popisky, navigace

### Google Fonts import
```css
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap');
```

### Velikosti
- Používej `clamp()` pro responzivní velikosti
- Příklad: `font-size: clamp(24px, 4vw, 42px);`

### Barvy textu
- **H1, H2:** `var(--primary-gold)` nebo `var(--dark-brown)`
- **H3, H4:** `var(--dark-brown)`
- **Hlavní text:** `var(--dark-brown)`
- **Sekundární text:** `var(--medium-gray)` nebo opacity 0.7
- **Text na tmavém:** `var(--white)`

### Font weights
- **H1:** 800 (extra bold)
- **H2:** 800 (extra bold)
- **H3:** 700 (bold)
- **Body:** 400 (regular)
- **CTA:** 600 (semi-bold)

## 🎯 Komponenty

### Tlačítka

**Primární CTA:**
```css
background: var(--primary-gold); /* #E5C158 */
color: var(--black);
hover: var(--primary-gold-dark); /* #A67C00 */
shadow: 0 4px 20px rgba(229, 193, 88, 0.3);
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

### Karty
```css
background: var(--white);
border: 1px solid var(--light-gray);
border-radius: 16px;
shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
hover: 
  border-color: var(--primary-gold);
  box-shadow: 0 12px 40px rgba(229, 193, 88, 0.15);
  transform: translateY(-8px);
```

### Badge (štítky)
```css
background: var(--primary-gold);
color: var(--black);
padding: 6px 20px;
border-radius: 30px;
font-size: 11px;
font-weight: 600;
letter-spacing: 2px;
text-transform: uppercase;
```

## 📐 Škálování

### Dynamické škálování
- Používej viewport jednotky (vw, vh)
- Kombinuj s `clamp()` pro min/max hodnoty
- JavaScript pro složitější logiku škálování

### Příklad
```css
font-size: clamp(15px, 4vw, 65px);
padding: clamp(12px, 2vw, 16px) clamp(32px, 5vw, 56px);
```

