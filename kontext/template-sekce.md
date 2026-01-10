# Template pro novou sekci

## JSON struktura

```json
{
  "id": "unique-section-id",
  "name": "section",
  "parent": 0,
  "children": ["unique-container-id"],
  "settings": {
    "_width": "100vw",
    "_height": "100vh"
  },
  "label": "Název sekce | PC"
},
{
  "id": "unique-container-id",
  "name": "container",
  "parent": "unique-section-id",
  "children": ["unique-code-id"],
  "settings": {
    "_width": "100vw",
    "_height": "100vh"
  }
},
{
  "id": "unique-code-id",
  "name": "code",
  "parent": "unique-container-id",
  "children": [],
  "settings": {
    "executeCode": true,
    "signature": "generated-hash",
    "user_id": 1,
    "time": 1234567890,
    "code": "<!-- HTML -->\n<style>/* CSS */</style>\n<script>/* JS */</script>"
  },
  "themeStyles": []
}
```

## HTML template

```html
<!-- NÁZEV SEKCE -->
<section class="section-name" id="sectionId">
  <div class="section-container">
    <!-- Obsah sekce -->
  </div>
</section>

<style>
  /* ==================== COLOR VARIABLES ==================== */
  :root {
    --primary-orange: #E8956F;
    --primary-orange-dark: #D97860;
    --secondary-blue: #87CEEB;
    --accent-beige: #E8D4C0;
    --text-dark: #333333;
    --text-light: #666666;
    --bg-white: #FFFFFF;
    --bg-light-orange: #FFF4E6;
    
    --font-heading: 'Poppins', sans-serif;
    --font-body: 'Inter', sans-serif;
  }

  /* ==================== RESET ==================== */
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  /* ==================== SECTION ==================== */
  .section-name {
    width: 100%;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-white);
    padding: clamp(20px, 3vw, 40px);
  }

  .section-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
  }

  /* ==================== RESPONSIVE ==================== */
  @media (max-width: 1024px) {
    /* Tablet */
  }

  @media (max-width: 768px) {
    /* Mobile - pouze pokud potřebuješ */
  }
</style>

<script>
  // JavaScript pro interaktivitu a dynamické škálování
  document.addEventListener('DOMContentLoaded', () => {
    // Kód zde
  });
</script>
```

## Checklist

- [ ] Unikátní ID pro všechny elementy
- [ ] executeCode: true v CODE bloku
- [ ] CSS variables z design systému
- [ ] Dynamické škálování (clamp, vw, vh)
- [ ] Google Fonts import (Poppins, Inter)
- [ ] Responzivní jednotky
- [ ] Komentáře v kódu
- [ ] Testování na různých velikostech

