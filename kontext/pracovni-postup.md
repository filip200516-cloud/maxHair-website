# Pracovní postup

## 🚀 Jak vytvářet novou sekci

### 1. Vytvoření struktury v Bricks
1. Otevři Bricks Builder v WordPressu
2. Přidej novou **SEKCI**
3. Nastav rozměry: `100vw × 100vh` (nebo podle potřeby)
4. Přidej **KONTEJNER** do sekce
5. Nastav kontejner: `100vw × 100vh`
6. Přidej **CODE** blok do kontejneru

### 2. Vývoj kódu
1. Napiš HTML strukturu
2. Přidej CSS styly s dynamickým škálováním
3. Implementuj JavaScript pro interaktivitu
4. Testuj na různých velikostech obrazovek

### 3. Export a dokumentace
1. Exportuj JSON strukturu z Bricks
2. Ulož do projektu s popisným názvem
3. Dokumentuj specifické funkce

## 📋 Checklist pro každou sekci

- [ ] Sekce má správné rozměry (100vw × 100vh)
- [ ] Kontejner má správné rozměry
- [ ] Code blok má `executeCode: true`
- [ ] Použito dynamické škálování (clamp, vw, vh)
- [ ] JavaScript funguje správně
- [ ] Barvy odpovídají design systému
- [ ] Fonty jsou správně naimportované
- [ ] Animace jsou plynulé
- [ ] Kód je čistý a komentovaný

## 🎨 Best practices

### CSS
- Používej CSS custom properties (variables)
- Responzivní jednotky: `clamp()`, `vw`, `vh`
- Flexbox/Grid pro layout
- Smooth transitions pro interakce

### JavaScript
- Event listeners s cleanup
- Debounce pro resize events
- Console.log pro debugging (odstraň před produkci)
- Komentáře pro složitější logiku

### HTML
- Sémantické tagy
- Accessibility (aria-labels, alt texty)
- Strukturované komentáře

## 🔧 Debugging

### Časté problémy
1. **Code neběží:** Zkontroluj `executeCode: true`
2. **Škálování nefunguje:** Zkontroluj viewport jednotky
3. **JavaScript chyby:** Otevři konzoli prohlížeče
4. **Styly se neaplikují:** Zkontroluj CSS specificitu

### Nástroje
- Chrome DevTools pro debugging
- Bricks Builder preview
- Responsive Design Mode

