# 📋 Co dál - Instrukce pro dokončení setupu

## ✅ Co je hotovo

1. ✅ Základní struktura projektu vytvořena
2. ✅ Konfigurační soubor (`config.json`)
3. ✅ WordPress REST API klient (`wp-api.js`)
4. ✅ Bricks handler (`bricks-handler.js`)
5. ✅ Hlavní synchronizační skript (`sync.js`)
6. ✅ Custom WordPress plugin pro Bricks API (`bricks-api-endpoint.php`)
7. ✅ Dokumentace

## 🔧 Co musíte udělat

### 1. Nainstalovat Node.js závislosti

```bash
cd C:\Users\YourUser\Documents\Fellaship-Web-Builder-Tool
npm install
```

### 2. Nastavit Application Password nebo heslo

**DŮLEŽITÉ:** Musíte nastavit přihlašovací údaje v `config.json`.

#### Možnost A: Application Password (doporučeno)

1. Přihlaste se do WordPress adminu: `https://darkgray-caribou-733262.hostingersite.com/wp-admin`
2. Přejděte na: **Uživatelé → Váš profil**
3. Scrollujte na sekci **"Application Passwords"**
   - Pokud sekci nevidíte, možná máte starší verzi WordPress nebo je potřeba plugin
4. Zadejte název: "Fellaship Web Builder Tool"
5. Klikněte **"Add New Application Password"**
6. Zkopírujte zobrazené heslo (zobrazí se jen jednou!)
7. Otevřete `config.json` a nastavte:
   ```json
   {
     "wordpress": {
       "username": "vas-username",
       "applicationPassword": "zkopirovane-heslo-z-aplikace"
     }
   }
   ```

#### Možnost B: Standardní heslo

Pokud Application Password není dostupný, můžete použít standardní WordPress heslo:

```json
{
  "wordpress": {
    "username": "vas-username",
    "password": "vas-standardni-heslo"
  }
}
```

**⚠️ VAROVÁNÍ:** Standardní heslo je méně bezpečné. Použijte ho jen pokud Application Password není dostupný.

### 3. Nainstalovat Bricks API Endpoint plugin

**DŮLEŽITÉ:** Tento plugin je nutný pro správnou funkci synchronizace!

1. **Přes Hostinger hPanel:**
   - Přihlaste se do hPanel
   - Přejděte na **Files → File Manager**
   - Přejděte do: `public_html/wp-content/plugins/`
   - Vytvořte složku: `bricks-api-endpoint`
   - Nahrajte soubor `bricks-api-endpoint.php` do této složky

2. **Aktivovat plugin:**
   - Přihlaste se do WordPress adminu
   - Přejděte na **Plugins → Installed Plugins**
   - Najděte **"Bricks API Endpoint"**
   - Klikněte **"Activate"**

3. **Aktualizovat permalinks:**
   - Přejděte na **Settings → Permalinks**
   - Klikněte **"Save Changes"** (i bez změn)

Více informací v `INSTALACE.md`.

### 4. Otestovat setup

```bash
npm run setup
```

Tento příkaz:
- Otestuje připojení k WordPress API
- Zkontroluje konfiguraci
- Zkontroluje lokální soubory
- Zkontroluje, zda je Bricks nainstalován

### 5. Otestovat pull (stáhnutí)

```bash
npm run pull
```

Tento příkaz stáhne aktuální Bricks obsah ze všech stránek z WordPressu.

**POZNÁMKA:** Pokud dostanete chybu o Bricks obsahu, ujistěte se, že:
- Bricks API Endpoint plugin je nainstalován a aktivní
- Permalinks jsou aktualizovány

### 6. Otestovat push (nahrání)

```bash
npm run push
```

Tento příkaz nahraje všechny lokální JSON soubory do WordPressu.

## 🔍 Kontrolní seznam

- [ ] Node.js závislosti nainstalovány (`npm install`)
- [ ] Application Password nebo heslo nastaveno v `config.json`
- [ ] Bricks API Endpoint plugin nainstalován a aktivní
- [ ] Permalinks aktualizovány
- [ ] Setup test proběhl úspěšně (`npm run setup`)
- [ ] Pull test proběhl úspěšně (`npm run pull`)
- [ ] Push test proběhl úspěšně (`npm run push`)

## 🐛 Řešení problémů

### Chyba: "Cannot connect to WordPress API"

**Řešení:**
1. Zkontrolujte URL v `config.json`
2. Zkontrolujte username a password
3. Otestujte REST API: `https://darkgray-caribou-733262.hostingersite.com/wp-json/wp/v2`

### Chyba: "Bricks content not found"

**Řešení:**
1. Ujistěte se, že Bricks API Endpoint plugin je nainstalován
2. Zkontrolujte, zda je plugin aktivní
3. Aktualizujte permalinks (Settings → Permalinks → Save)

### Chyba: "Application Passwords not found"

**Řešení:**
1. Zkontrolujte verzi WordPress (vyžaduje 5.6+)
2. Nebo použijte standardní heslo (méně bezpečné)
3. Nebo nainstalujte plugin pro Application Passwords

## 📞 Co když něco nefunguje?

1. **Zkontrolujte logy** - všechny chyby jsou zobrazeny v konzoli
2. **Otestujte REST API** - otevřete `https://darkgray-caribou-733262.hostingersite.com/wp-json/wp/v2` v prohlížeči
3. **Zkontrolujte plugin** - ujistěte se, že Bricks API Endpoint je aktivní
4. **Zkontrolujte oprávnění** - uživatel musí mít oprávnění `edit_posts`

## 🎯 Další kroky po úspěšném setupu

1. **Nastavte automatickou synchronizaci** (volitelné)
   - Můžete použít cron job nebo GitHub Actions
   - Nebo spouštět manuálně před/po změnách

2. **Vytvořte backup** před prvním push
   - Zálohujte WordPress databázi
   - Zálohujte lokální soubory

3. **Otestujte na staging** (pokud máte)
   - Nejdřív otestujte na testovacím prostředí
   - Pak použijte na produkci

---

**Potřebujete pomoc?** Zkontrolujte:
- `README.md` - hlavní dokumentace
- `INSTALACE.md` - instrukce pro instalaci pluginu
- Logy v konzoli při spuštění skriptů


