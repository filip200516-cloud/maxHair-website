# Reference sekce - Příklady z VitaSignum

## 📋 Přehled sekcí

Z reference projektu VitaSignum máme tyto sekce:

1. **Hero sekce** - Maják, text box, logo
2. **Služby sekce** - 3 karty s ikonami
3. **Proces sekce** - Textový karusel s SVG
4. **Recenze sekce** - 3 karty s fotkami klientů
5. **FAQ sekce** - Accordion s otázkami a odpověďmi

## 🎯 Klíčové prvky z reference

### Hero sekce
- Maják vlevo (pozice: top 20vh, spod pod kontejnerem)
- Text box uprostřed (max-width 35vw, 5vh doprava, 8vw marže z prava)
- Logo vpravo dole
- Mouse tracking pro maják (gentle parallax)
- Dynamické škálování textu podle šířky

### Služby sekce
- Grid 3 karty
- Hover efekty s transformací
- Animace při načtení (fadeInUp)
- Ikony v SVG
- Cena a CTA tlačítko

### Proces sekce
- SVG s textovými slides
- Navigace šipkami (dole uprostřed)
- Slide approach (skrývání/zobrazování)
- Keyboard navigace (šipky)
- Swipe podpora (touch)
- Auto-redirect na slide 5 (kontakt)

### Recenze sekce
- Grid 3 karty
- Bílé karty s border
- Hvězdičky (★★★★★)
- Avatar kruhy pod kartami
- Bílý background kruh + fotka
- Jméno a věk v bílém obdélníčku

### FAQ sekce
- Accordion design
- Expandable answers
- Plus ikona (rotuje na X)
- Top border animace při hover
- CTA box na konci
- URL hash support (#faq-2)

## 💡 Techniky použité v reference

### Dynamické škálování
```css
font-size: clamp(15px, 4vw, 65px);
padding: clamp(12px, 2vw, 16px) clamp(32px, 5vw, 56px);
```

### Animace
```css
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
```

### JavaScript pozicování
- Měření šířky textu
- Výpočet pozice podle viewportu
- Resize listener pro přepočítání

### Hover efekty
- Transform translateY
- Box shadow změna
- Border color změna
- Scale transform

## 📝 Poznámky pro MaxHair

- Použij podobný přístup k dynamickému škálování
- Respektuj design systém (barvy, fonty)
- Implementuj smooth animace
- Všechny sekce na 100vw × 100vh
- Pouze PC verze (mobil později)

