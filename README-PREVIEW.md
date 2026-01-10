# 🚀 MaxHair.cz - Lokální Preview Systém

Tento systém umožňuje vizualizovat a upravovat JSON soubory z Bricks Builder v reálném čase bez nutnosti je nahrávat do WordPressu.

## 📋 Jak to funguje

1. **JSON soubory** - Všechny sekce jsou uloženy jako JSON soubory (Bricks Builder formát)
2. **Parser** - JavaScript parser extrahuje HTML, CSS a JS z JSON struktury
3. **Router** - Jednoduchý router pro navigaci mezi stránkami
4. **Lokální server** - Statický HTTP server pro načítání souborů

## 🚀 Spuštění

### Windows:
```bash
start-server.bat
```

### Mac/Linux:
```bash
chmod +x start-server.sh
./start-server.sh
```

### Nebo ručně:
```bash
# Python 3
python -m http.server 8000

# Nebo Python 2
python -m SimpleHTTPServer 8000

# Nebo Node.js
npx http-server -p 8000
```

## 🌐 Přístup

Otevři v prohlížeči: **http://localhost:8000**

## 📁 Struktura

```
MaxHair/
├── index.html          # Hlavní HTML soubor
├── preview.js          # JavaScript parser a router
├── preview.css         # Základní styly
├── header-maxhair.json # Header komponenta
├── footer-maxhair.json # Footer komponenta
├── homepage-maxhair.json # Homepage hero
├── sections/           # Sekce homepage
│   ├── 02-problem.json
│   ├── 03-vyhody.json
│   └── ...
└── pages/              # Podstránky
    ├── vop.json
    ├── gdpr.json
    └── ...
```

## 🔄 Jak to funguje

1. **Homepage** (`/`) - Načte header, homepage hero a všechny sekce ze složky `sections/`
2. **Podstránky** (`/vop`, `/kontakt`, atd.) - Načte header, obsah stránky a footer

## ✏️ Úpravy

1. Uprav JSON soubory v editoru
2. Obnov stránku v prohlížeči (F5)
3. Změny se okamžitě projeví

## 🎯 Dostupné stránky

- `/` - Homepage
- `/vop` - Všeobecné obchodní podmínky
- `/gdpr` - GDPR (když bude vytvořeno)
- `/kontakt` - Kontakt (když bude vytvořeno)
- `/o-nas` - O nás (když bude vytvořeno)
- atd.

## ⚠️ Poznámky

- **CORS**: Pokud máš problémy s načítáním souborů, použij lokální server (ne otevření souboru přímo)
- **JavaScript**: Některé JS funkce mohou potřebovat úpravy pro lokální prostředí
- **Obrázky/Videa**: URL obrázků a videí musí být dostupné (externí URL fungují)

## 🔧 Řešení problémů

### Soubory se nenačítají
- Zkontroluj, že server běží na správném portu
- Zkontroluj konzoli prohlížeče (F12) pro chyby

### Styling nefunguje
- Zkontroluj, že CSS je správně extrahováno z JSON
- Zkontroluj, že Google Fonts jsou načteny

### JavaScript nefunguje
- Zkontroluj konzoli prohlížeče pro chyby
- Některé funkce mohou potřebovat úpravy pro lokální prostředí

## 📝 Přidání nové stránky

1. Vytvoř JSON soubor v `pages/nazev.json`
2. Přidej route do `preview.js`:
```javascript
this.routes = {
    '/nazev': 'nazev',
    // ...
};
```
3. Obnov stránku

---

**Tip:** Pro nejlepší zkušenost používej Live Reload extension v prohlížeči nebo automatické obnovování.

