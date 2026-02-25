## Cursor Cloud specific instructions

This is a static website preview system for **MaxHair.cz** (Czech hair transplant clinic). It renders Bricks Builder JSON templates as HTML in a browser. There is no build step, no test framework, and no linter configured.

### Running the dev server

```bash
npm start
# or equivalently: npx http-server -p 8000 -c-1
```

The preview is served at `http://localhost:8000`. The app uses `fetch()` to load JSON files, so a static HTTP server is required (opening `index.html` directly via `file://` will fail due to CORS).

### Project structure

- `index.html`, `preview.js`, `preview.css` — the local preview system
- `sections/` — 12 homepage section JSON files (Bricks Builder format)
- `pages/` — 15 subpage JSON files
- `header-maxhair.json`, `footer-maxhair.json` — shared header/footer components
- `kontext/`, `zadani/` — documentation and client briefs (not code)

### Key caveats

- No automated tests, linting, or build pipeline exist in this repo.
- All content is in Czech. The JSON files contain HTML/CSS/JS embedded in Bricks Builder `code` blocks.
- Changes to JSON files are visible after a browser refresh (no hot-reload).
- See `README.md` and `QUICK-START.md` for full project documentation.
