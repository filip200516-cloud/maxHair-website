# 📦 Instalace Bricks API Endpoint

Pro správnou funkci synchronizace je potřeba nainstalovat custom WordPress plugin, který umožní práci s Bricks meta daty přes REST API.

## 🔧 Instalace

### Metoda 1: Přes WordPress Admin (doporučeno)

1. **Zkopírujte soubor:**
   - Zkopírujte `bricks-api-endpoint.php` do složky WordPress pluginů
   - Cesta: `wp-content/plugins/bricks-api-endpoint/bricks-api-endpoint.php`

2. **Přes Hostinger hPanel:**
   - Přihlaste se do hPanel
   - Přejděte na **Files → File Manager**
   - Přejděte do: `public_html/wp-content/plugins/`
   - Vytvořte složku: `bricks-api-endpoint`
   - Nahrajte soubor `bricks-api-endpoint.php` do této složky

3. **Aktivujte plugin:**
   - Přihlaste se do WordPress adminu
   - Přejděte na **Plugins → Installed Plugins**
   - Najděte **"Bricks API Endpoint"**
   - Klikněte **"Activate"**

### Metoda 2: Přes FTP/SFTP

1. Připojte se k serveru přes FTP klienta (FileZilla, WinSCP, atd.)
2. Přejděte do: `wp-content/plugins/`
3. Vytvořte složku: `bricks-api-endpoint`
4. Nahrajte soubor `bricks-api-endpoint.php`
5. Aktivujte plugin v WordPress adminu

## ✅ Ověření instalace

Po instalaci otestujte endpoint:

```bash
# Test endpointu (vyžaduje autentizaci)
curl -u "username:password" \
  https://darkgray-caribou-733262.hostingersite.com/wp-json/bricks/v1/pages
```

Nebo otevřete v prohlížeči (po přihlášení):
```
https://darkgray-caribou-733262.hostingersite.com/wp-json/bricks/v1/pages
```

## 🔌 Dostupné endpointy

### 1. Získat Bricks obsah stránky
```
GET /wp-json/bricks/v1/page/{id}/content
```

### 2. Aktualizovat Bricks obsah stránky
```
POST /wp-json/bricks/v1/page/{id}/content
Body: { "content": "..." }
```

### 3. Získat všechny stránky s Bricks obsahem
```
GET /wp-json/bricks/v1/pages
```

## 🔐 Bezpečnost

Plugin vyžaduje oprávnění `edit_posts` pro přístup k endpointům. To znamená, že:
- Uživatel musí být přihlášen
- Uživatel musí mít oprávnění editovat stránky
- Autentizace přes Application Password nebo standardní heslo

## ⚠️ Důležité

- Plugin musí být aktivní pro fungování synchronizace
- Pokud plugin není nainstalován, skript použije alternativní metody (které nemusí fungovat)
- Plugin je kompatibilní s WordPress 5.0+

## 🐛 Řešení problémů

### Endpoint vrací 404

**Příčina:** Plugin není aktivní nebo permalinks nejsou aktualizovány.

**Řešení:**
1. Zkontrolujte, zda je plugin aktivní
2. Přejděte na **Settings → Permalinks**
3. Klikněte **"Save Changes"** (i bez změn)

### Endpoint vrací 401 (Unauthorized)

**Příčina:** Chybí autentizace nebo uživatel nemá oprávnění.

**Řešení:**
1. Zkontrolujte username a password v `config.json`
2. Zkontrolujte, zda má uživatel oprávnění `edit_posts`
3. Zkuste použít Application Password

### Endpoint vrací 500 (Internal Server Error)

**Příčina:** Chyba v PHP kódu nebo konflikt s jiným pluginem.

**Řešení:**
1. Zkontrolujte WordPress error logy
2. Zkontrolujte, zda je PHP verze kompatibilní (PHP 7.4+)
3. Deaktivujte ostatní pluginy a otestujte

---

**POZNÁMKA:** Tento plugin je nutný pro správnou funkci synchronizace. Bez něj nemusí pull/push fungovat správně.


