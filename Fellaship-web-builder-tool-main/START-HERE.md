# 🚀 START HERE - Rychlý start

Vítejte! Tento dokument vás provede prvním spuštěním synchronizačního systému.

## ⚡ Rychlý start (5 minut)

### Krok 1: Nainstalovat závislosti

Otevřete PowerShell nebo Command Prompt a spusťte:

```bash
cd C:\Users\YourUser\Documents\Fellaship-Web-Builder-Tool
npm install
```

### Krok 2: Nastavit přihlašovací údaje

Otevřete `config.json` a nastavte:

```json
{
  "wordpress": {
    "username": "VAS-USERNAME",
    "applicationPassword": "VAS-APPLICATION-PASSWORD"
  }
}
```

**Jak získat Application Password:**
1. Přihlaste se do WordPress: https://darkgray-caribou-733262.hostingersite.com/wp-admin
2. Uživatelé → Váš profil
3. Scrollujte na "Application Passwords"
4. Zadejte název: "Fellaship Web Builder Tool"
5. Klikněte "Add New Application Password"
6. Zkopírujte heslo a vložte do `config.json`

**Pokud Application Password nevidíte:**
- Použijte standardní heslo v `config.json` jako `"password": "vas-heslo"`

### Krok 3: Nainstalovat Bricks API Endpoint plugin

**DŮLEŽITÉ:** Bez tohoto pluginu synchronizace nebude fungovat správně!

1. Přes Hostinger hPanel:
   - Files → File Manager
   - Přejděte do: `public_html/wp-content/plugins/`
   - Vytvořte složku: `bricks-api-endpoint`
   - Nahrajte soubor: `bricks-api-endpoint.php`

2. Aktivovat plugin:
   - WordPress Admin → Plugins → Installed Plugins
   - Najděte "Bricks API Endpoint"
   - Klikněte "Activate"

3. Aktualizovat permalinks:
   - Settings → Permalinks → Save Changes

Více detailů v `INSTALACE.md`.

### Krok 4: Otestovat

```bash
npm run setup
```

Pokud vše proběhne úspěšně, uvidíte:
```
✅ Připojení úspěšné
✅ Konfigurace OK
✅ Nalezeno X lokálních stránek
```

### Krok 5: První synchronizace

**Stáhnout z WordPressu:**
```bash
npm run pull
```

**Nahrát do WordPressu:**
```bash
npm run push
```

## 📚 Další dokumentace

- **README.md** - Kompletní dokumentace
- **INSTALACE.md** - Detailní instrukce pro instalaci pluginu
- **CO-DAL.md** - Co dál po setupu

## ❓ Problémy?

### "Cannot connect to WordPress API"
- Zkontrolujte username a password v `config.json`
- Otestujte REST API: https://darkgray-caribou-733262.hostingersite.com/wp-json/wp/v2

### "Bricks content not found"
- Ujistěte se, že Bricks API Endpoint plugin je aktivní
- Aktualizujte permalinks (Settings → Permalinks → Save)

### "Application Passwords not found"
- Použijte standardní heslo v `config.json`
- Nebo nainstalujte plugin pro Application Passwords

## ✅ Kontrolní seznam

- [ ] `npm install` proběhl úspěšně
- [ ] Username a password nastaveny v `config.json`
- [ ] Bricks API Endpoint plugin nainstalován a aktivní
- [ ] Permalinks aktualizovány
- [ ] `npm run setup` proběhl úspěšně
- [ ] `npm run pull` funguje
- [ ] `npm run push` funguje

---

**Hotovo?** Gratulujeme! 🎉 Nyní můžete synchronizovat Bricks obsah mezi lokálním projektem a WordPressem.


