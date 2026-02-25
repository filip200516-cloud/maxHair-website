# 🔑 Proč Templates potřebují jiný způsob pushnutí než Pages

## ✅ Co fungovalo

Po reverse-engineeringu ručně vytvořeného template jsme zjistili **klíčový rozdíl**:

### Pro Header Templates:
Bricks ukládá obsah do **`_bricks_page_header_2`** místo jen `_bricks_page_content_2`!

### Pro Footer Templates:
Bricks ukládá obsah do **`_bricks_page_footer_2`** místo jen `_bricks_page_content_2`!

## 📊 Rozdíly: Pages vs. Templates

### Pages (normální stránky)

**Meta klíče:**
- `_bricks_page_content` - obsah stránky
- `_bricks_page_content_2` - backup obsah stránky
- `_bricks_editor_mode = 'bricks'`
- `_bricks_page_content_type = 'bricks'`

**Post Type:** `page`

**Endpoint:** `POST /wp-json/bricks/v1/page/{id}/content`

**Workflow:**
1. Načíst lokální JSON soubor
2. Extrahovat `content` pole
3. Uložit do `_bricks_page_content` a `_bricks_page_content_2`
4. Regenerovat podpisy

### Templates (Header/Footer)

**Meta klíče:**
- `_bricks_page_content` - obsah template (standardní)
- `_bricks_page_content_2` - backup obsah template (standardní)
- **`_bricks_page_header_2`** - **KLÍČOVÉ pro header templates!**
- **`_bricks_page_footer_2`** - **KLÍČOVÉ pro footer templates!**
- `_bricks_template_type = 'header'/'footer'`
- `_bricks_template_active = true`
- `_bricks_template_conditions = []`
- `_bricks_editor_mode = 'bricks'`

**Post Type:** `bricks_template`

**Endpoint:** `POST /wp-json/bricks/v1/template/{id}/content`

**Workflow:**
1. Načíst lokální JSON soubor
2. Extrahovat `content` pole
3. Uložit do `_bricks_page_content` a `_bricks_page_content_2` (standardní)
4. **DŮLEŽITÉ:** Uložit také do `_bricks_page_header_2` (pro header) nebo `_bricks_page_footer_2` (pro footer)
5. Regenerovat podpisy
6. **DŮLEŽITÉ:** V Bricks Settings → Templates → Request Signatures (manuálně)

## 🔍 Proč to nefungovalo předtím

**Problém:**
- Obsah byl uložený pouze do `_bricks_page_content` a `_bricks_page_content_2`
- Bricks editor pro header/footer templates hledá obsah v **`_bricks_page_header_2`** resp. **`_bricks_page_footer_2`**
- Proto struktura nebyla viditelná v editoru, i když obsah byl správně uložený!

**Řešení:**
- Ukládáme obsah do **všech tří meta klíčů**:
  1. `_bricks_page_content` (standardní)
  2. `_bricks_page_content_2` (standardní)
  3. `_bricks_page_header_2` nebo `_bricks_page_footer_2` (specifický pro template typ)

## 💡 Jak to zjistit

1. Vytvoř template ručně v Bricks editoru
2. Pullni ho z WordPressu
3. Zkontroluj meta klíče v databázi nebo přes API
4. Uvidíš, že Bricks ukládá obsah do specifického meta klíče podle typu template!

## 📝 Implementace

V `bricks-api-endpoint.php` funkce `bricks_update_template_content`:

```php
// Určit správný meta klíč podle typu template
$specific_meta_key = null;
if ($template_type === 'header') {
    $specific_meta_key = '_bricks_page_header_2';
} elseif ($template_type === 'footer') {
    $specific_meta_key = '_bricks_page_footer_2';
}

// Uložit do standardních meta klíčů
update_post_meta($template_id, '_bricks_page_content_2', $content_array);
update_post_meta($template_id, '_bricks_page_content', $content_array);

// DŮLEŽITÉ: Uložit také do specifického meta klíče pro header/footer!
if ($specific_meta_key) {
    update_post_meta($template_id, $specific_meta_key, $content_array);
}
```

## ⚠️ DŮLEŽITÉ: Request Signatures v Bricks Settings

Po pushnutí templates je potřeba **manuálně requestnout podpisy** v Bricks Settings:

1. Bricks → Settings → Templates
2. Klikni na template (Header/Footer)
3. Klikni na "Request Signatures" nebo podobné tlačítko

**Proč?**
- Automatické podepisování funguje, ale Bricks Settings může potřebovat explicitní request
- Toto je specifické pro templates, u pages to není potřeba

