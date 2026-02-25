## Cursor Cloud specific instructions

This is the **MaxHair.cz** website project — a Czech hair transplant clinic site built with **WordPress + Bricks Builder**. The codebase contains Bricks Builder JSON templates and the **Fellaship Web Builder Tool** for syncing content to the live site on Hostinger.

### Branch policy

Always work on the `master` branch unless told otherwise.

### Services

| Service | Port | Command | Purpose |
|---|---|---|---|
| Static preview | 8000 | `npm start` (from `/workspace`) | Quick HTML/CSS/JS preview of JSON templates |
| Bricks local server | 3000 | `npm run dev` (from `/workspace/Fellaship-web-builder-tool-main`) | Full Bricks JSON renderer with header/footer |

### Fellaship Web Builder Tool

Located at `/workspace/Fellaship-web-builder-tool-main/`. This tool syncs Bricks Builder JSON to the live WordPress site at https://maxhair.cz.

**Setup (already done in the update script):**
```bash
cd /workspace/Fellaship-web-builder-tool-main && npm install
```

**Config:** The tool requires a `config.json` in its directory. Create it from `config-original.json` with `local.projectPath` set to `/workspace`. The `config.json` is in `.gitignore`.

**Key commands (run from `/workspace/Fellaship-web-builder-tool-main`):**
- `node sync.js setup` — test WordPress API connection
- `node sync.js push` — push pages to WordPress
- `node sync.js push-templates` — push Header/Footer templates
- `node sync.js push-all` — push pages + templates
- `node sync.js pull` — pull from WordPress

See `CURSOR-AI-GUIDE.md` and `QUICK-START-AI.md` in the tool directory for the full workflow.

### Hostinger cache

After every successful push to the live site, **you must clear the cache** for changes to appear on www.maxhair.cz. The Bricks API endpoint plugin provides a cache-purge REST endpoint:

```bash
curl -s -X POST -u "$WP_USER:$WP_APP_PASSWORD" "https://maxhair.cz/wp-json/bricks/v1/purge-all-cache"
```

This purges LiteSpeed Cache, WordPress object cache, Bricks transients, and opcache in one call.

### No lint/test/build pipeline

This project has no automated tests, linter, or build step. Verification is done by checking the live site and the local preview servers.

### Credentials

The WordPress API credentials and SSH credentials for Hostinger are stored in `config-original.json` (committed). The active `config.json` is generated from it with adapted paths. If credentials are rotated, update `config-original.json` and recreate `config.json`.
