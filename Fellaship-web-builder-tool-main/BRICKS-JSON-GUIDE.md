# 📐 Průvodce tvorbou Bricks JSON struktury

Tento dokument popisuje, jak správně vytvářet JSON soubory pro Bricks Builder, aby fungovaly bez nutného zásahu uživatele.

## 🎯 Základní struktura

Bricks JSON soubor musí mít tuto strukturu:

```json
{
  "content": [
    // Pole elementů - TOTO je to, co Bricks skutečně používá
  ],
  "source": "bricksCopiedElements",
  "version": "2.0"
}
```

**DŮLEŽITÉ:**
- `content` je **pole elementů**, ne objekt
- Každý element má: `name`, `settings`, `children` (volitelné)
- Tool automaticky extrahuje pouze `content` pole při pushnutí

## 📦 Struktura elementu

**DŮLEŽITÉ:** Bricks používá strukturu s `id`, `parent` a `children` jako pole stringů (ID), ne pole objektů!

```json
{
  "id": "hero_section",
  "name": "section",
  "parent": 0,
  "children": ["hero_container"],
  "settings": {
    "_width": "100vw",
    "padding": "20px",
    "background": "#ffffff"
  },
  "label": "Hero Section"
},
{
  "id": "hero_container",
  "name": "container",
  "parent": "hero_section",
  "children": ["hero_heading"],
  "settings": {
    "maxWidth": "1200px"
  }
},
{
  "id": "hero_heading",
  "name": "heading",
  "parent": "hero_container",
  "children": [],
  "settings": {
    "text": "Nadpis",
    "tag": "h1"
  }
}
```

**Klíčové vlastnosti:**
- `id` - **POVINNÉ** - unikátní ID elementu (string, např. "hero_section")
- `name` - **POVINNÉ** - typ elementu ("section", "container", "heading", "text", "button", "code", atd.)
- `parent` - **POVINNÉ** - ID rodiče (0 pro root elementy, nebo string ID rodiče)
- `children` - **POVINNÉ** - pole stringů s ID dětí (ne pole objektů!), prázdné pole [] pokud nemá děti
- `settings` - **POVINNÉ** - objekt s nastavením elementu
- `label` - volitelné - popisek pro lepší orientaci v editoru

## 🔧 Typy elementů

### Section (sekce)
```json
{
  "name": "section",
  "settings": {
    "width": "100%",
    "padding": "20px 0",
    "background": "#f5f5f5"
  },
  "children": []
}
```

### Container (kontejner)
```json
{
  "name": "container",
  "settings": {
    "maxWidth": "1200px",
    "padding": "0 20px"
  },
  "children": []
}
```

### Heading (nadpis)
```json
{
  "name": "heading",
  "settings": {
    "text": "Nadpis stránky",
    "tag": "h1",
    "fontSize": "48px",
    "fontWeight": "700"
  }
}
```

### Text (text)
```json
{
  "name": "text",
  "settings": {
    "text": "Lorem ipsum dolor sit amet..."
  }
}
```

### Image (obrázek)
```json
{
  "name": "image",
  "settings": {
    "image": {
      "url": "https://example.com/image.jpg",
      "alt": "Popis obrázku"
    },
    "width": "100%"
  }
}
```

### Button (tlačítko)
```json
{
  "name": "button",
  "settings": {
    "text": "Klikni zde",
    "link": {
      "url": "https://example.com",
      "target": "_blank"
    },
    "background": "#007bff",
    "color": "#ffffff",
    "padding": "12px 24px",
    "borderRadius": "4px"
  }
}
```

### Code (kód - HTML/CSS/JS) - **POUŽÍVEJ PRO DESIGN!**

**⚠️ DŮLEŽITÉ: Pro design VŽDY používej code elementy, ne normální Bricks elementy!**

```json
{
  "id": "hero_code",
  "name": "code",
  "parent": "hero_container",
  "children": [],
  "settings": {
    "code": "<!-- HERO SECTION -->\n<section class=\"hero-section\">\n  <div class=\"hero-content\">\n    <h1 class=\"hero-title\">Nadpis</h1>\n    <p class=\"hero-description\">Popis...</p>\n    <a href=\"/kontakt\" class=\"btn-primary\">Tlačítko</a>\n  </div>\n</section>\n\n<style>\n  .hero-section {\n    width: 100%;\n    min-height: 100vh;\n    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\n    display: flex;\n    align-items: center;\n    justify-content: center;\n    padding: 80px 20px;\n  }\n  .hero-content {\n    max-width: 1200px;\n    text-align: center;\n    color: #fff;\n  }\n  .hero-title {\n    font-size: clamp(32px, 5vw, 64px);\n    font-weight: 800;\n    margin: 0 0 24px 0;\n  }\n  /* ... další CSS ... */\n</style>",
    "executeCode": true,
    "signature": "",  // Vygeneruje se automaticky při pushnutí
    "user_id": 0,     // Vyplní se automaticky
    "time": 0         // Vyplní se automaticky
  }
}
```

**DŮLEŽITÉ pro Code elementy:**
- **VŽDY používej code elementy pro design** - obsahují celý HTML/CSS/JS kód sekce
- Struktura: `section` → `container` → `code` (s HTML/CSS/JS)
- `signature`, `user_id`, `time` se vygenerují automaticky při pushnutí
- Bez podpisu se code element nespustí
- Tool automaticky podepíše všechny code elementy
- V `code` poli je celý HTML kód včetně `<style>` a `<script>` tagů

## 🏗️ Typická struktura stránky

```json
{
  "content": [
    {
      "name": "section",
      "settings": {
        "background": "#ffffff",
        "padding": "80px 0"
      },
      "children": [
        {
          "name": "container",
          "settings": {
            "maxWidth": "1200px"
          },
          "children": [
            {
              "name": "heading",
              "settings": {
                "text": "Nadpis sekce",
                "tag": "h2"
              }
            },
            {
              "name": "text",
              "settings": {
                "text": "Text sekce..."
              }
            },
            {
              "name": "button",
              "settings": {
                "text": "Více informací",
                "link": {
                  "url": "/contact"
                }
              }
            }
          ]
        }
      ]
    }
  ],
  "source": "bricksCopiedElements",
  "version": "2.0"
}
```

## 📝 Pravidla pro AI při tvorbě JSON

**⚠️ DŮLEŽITÉ: Pro design používej CODE ELEMENTY, ne normální Bricks elementy!**

### Správná struktura pro design:

```
section (root)
  └── container
      └── code (s HTML/CSS/JS)
```

**NEPOUŽÍVEJ:** section → container → heading/text/button (to je špatně!)

**POUŽÍVEJ:** section → container → code (s celým HTML/CSS/JS uvnitř)

### Pravidla:

1. **VŽDY začni s `content` polem** - to je pole elementů
2. **Každý element MUSÍ mít:**
   - `id` - unikátní ID (string, např. "hero_section")
   - `name` - typ elementu ("section", "container", "code")
   - `parent` - ID rodiče (0 pro root, nebo string ID)
   - `children` - **pole stringů s ID dětí** (ne pole objektů!), prázdné [] pokud nemá děti
   - `settings` - objekt s nastavením
3. **Použij `inspirace.json` jako referenci** - obsahuje správnou strukturu s code elementy
4. **Vnořené elementy:** `children` je pole stringů s ID, ne pole objektů!
5. **Code elementy:**
   - **VŽDY používej code elementy pro design** - obsahují HTML/CSS/JS
   - `signature`, `user_id`, `time` se automaticky vygenerují při pushnutí
   - V `code` poli je celý HTML/CSS/JS kód sekce
6. **Struktura pro každou sekci:**
   - `section` (root, parent: 0)
   - `container` (parent: section_id)
   - `code` (parent: container_id) - obsahuje HTML/CSS/JS

## 🎨 Typické layouty

### Hero sekce
```json
{
  "name": "section",
  "settings": {
    "background": "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
    "padding": "120px 0",
    "minHeight": "600px",
    "display": "flex",
    "alignItems": "center"
  },
  "children": [
    {
      "name": "container",
      "settings": {
        "maxWidth": "1200px"
      },
      "children": [
        {
          "name": "heading",
          "settings": {
            "text": "Hlavní nadpis",
            "tag": "h1",
            "color": "#ffffff",
            "fontSize": "64px"
          }
        },
        {
          "name": "text",
          "settings": {
            "text": "Popis...",
            "color": "#ffffff"
          }
        },
        {
          "name": "button",
          "settings": {
            "text": "Začít",
            "background": "#ffffff",
            "color": "#667eea"
          }
        }
      ]
    }
  ]
}
```

### Grid layout
```json
{
  "name": "section",
  "settings": {
    "padding": "80px 0"
  },
  "children": [
    {
      "name": "container",
      "settings": {
        "maxWidth": "1200px"
      },
      "children": [
        {
          "name": "div",
          "settings": {
            "display": "grid",
            "gridTemplateColumns": "repeat(3, 1fr)",
            "gap": "30px"
          },
          "children": [
            {
              "name": "div",
              "settings": {
                "padding": "20px",
                "background": "#f5f5f5"
              },
              "children": [
                {
                  "name": "heading",
                  "settings": {
                    "text": "Karta 1",
                    "tag": "h3"
                  }
                }
              ]
            },
            {
              "name": "div",
              "settings": {
                "padding": "20px",
                "background": "#f5f5f5"
              },
              "children": [
                {
                  "name": "heading",
                  "settings": {
                    "text": "Karta 2",
                    "tag": "h3"
                  }
                }
              ]
            },
            {
              "name": "div",
              "settings": {
                "padding": "20px",
                "background": "#f5f5f5"
              },
              "children": [
                {
                  "name": "heading",
                  "settings": {
                    "text": "Karta 3",
                    "tag": "h3"
                  }
                }
              ]
            }
          ]
        }
      ]
    }
  ]
}
```

## ⚠️ Časté chyby

### ❌ Špatně - chybí content pole
```json
{
  "section": {
    "settings": {...}
  }
}
```

### ✅ Správně - content je pole
```json
{
  "content": [
    {
      "name": "section",
      "settings": {...}
    }
  ]
}
```

### ❌ Špatně - element není objekt
```json
{
  "content": [
    "section",
    "container"
  ]
}
```

### ❌ Špatně - chybí id, parent, children jako pole stringů
```json
{
  "content": [
    {
      "name": "section",
      "settings": {...},
      "children": [
        {
          "name": "container",
          "settings": {...}
        }
      ]
    }
  ]
}
```

### ✅ Správně - každý element má id, parent, children jako pole stringů
```json
{
  "content": [
    {
      "id": "hero_section",
      "name": "section",
      "parent": 0,
      "children": ["hero_container"],
      "settings": {...}
    },
    {
      "id": "hero_container",
      "name": "container",
      "parent": "hero_section",
      "children": [],
      "settings": {...}
    }
  ]
}
```

## 🔄 Workflow pro AI

1. **Uživatel řekne:** "Vytvoř stránku 'About' s hero sekcí a třemi kartami"
2. **AI vytvoří JSON s POVINNOU strukturou (section → container → code):**
   ```json
   {
     "content": [
       {
         "id": "hero_section",
         "name": "section",
         "parent": 0,
         "children": ["hero_container"],
         "settings": {
           "_width": "100vw",
           "_height": "100vh"
         },
         "label": "Hero Section"
       },
       {
         "id": "hero_container",
         "name": "container",
         "parent": "hero_section",
         "children": ["hero_code"],
         "settings": {
           "_width": "100vw",
           "_height": "100vh"
         }
       },
       {
         "id": "hero_code",
         "name": "code",
         "parent": "hero_container",
         "children": [],
         "settings": {
           "code": "<!-- HERO SECTION -->\n<section class=\"hero-section\">\n  <div class=\"hero-content\">\n    <h1 class=\"hero-title\">Nadpis</h1>\n    <p class=\"hero-description\">Popis...</p>\n    <a href=\"/kontakt\" class=\"btn-primary\">Tlačítko</a>\n  </div>\n</section>\n\n<style>\n  .hero-section {\n    width: 100%;\n    min-height: 100vh;\n    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\n    display: flex;\n    align-items: center;\n    justify-content: center;\n    padding: 80px 20px;\n  }\n  .hero-content {\n    max-width: 1200px;\n    text-align: center;\n    color: #fff;\n  }\n  .hero-title {\n    font-size: clamp(32px, 5vw, 64px);\n    font-weight: 800;\n    margin: 0 0 24px 0;\n  }\n  /* ... další CSS ... */\n</style>",
           "executeCode": true,
           "signature": "",
           "user_id": 0,
           "time": 0
         }
       }
     ],
     "source": "bricksCopiedElements",
     "version": "2.0"
   }
   ```
   
   **⚠️ DŮLEŽITÉ:** Používej code elementy s HTML/CSS/JS, ne normální Bricks elementy (heading, text, button)!
3. **AI uloží do:** `pages/about.json`
4. **AI SPUSTÍ LOKÁLNÍ SERVER PRO PREVIEW:**
   ```bash
   node local-server.js
   # nebo
   npm run dev
   ```
   - Server běží na `http://localhost:3000`
   - AI automaticky otevře prohlížeč
   - Uživatel vidí preview stránky
5. **Uživatel upravuje stránku lokálně** (JSON soubor v Cursoru)
6. **Když uživatel řekne "pushni to":**
   - AI pushne: `node sync.js push`
   - Tool automaticky extrahuje `content` pole
   - Tool automaticky podepíše code elementy

**⚠️ DŮLEŽITÉ:** AI NIKDY nepushuje stránku bez předchozího spuštění lokálního serveru a zobrazení preview!

## 📚 Reference

- Bricks Builder dokumentace: https://bricksbuilder.io/docs/
- Tool automaticky zpracuje JSON - stačí správná struktura
- Code elementy se podepíší automaticky - nemusíš řešit

