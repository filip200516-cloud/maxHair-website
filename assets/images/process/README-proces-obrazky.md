# Obrázky sekce „Jak probíhá transplantace?“ (proces)

Sekce je na stránce **Transplantace vlasů pro muže**, soubor: `pages/transplantace-vlasu-muzi.json`.

## Aktuální zdroj

Všech 5 obrázků je **vlastní grafika vygenerovaná nástrojem** (AI image generation):

| Krok | Soubor | Popis |
|------|--------|--------|
| 1 Konzultace | `proces-1-konzultace.png` | Online konzultace, video hovor |
| 2 Příprava | `proces-2-priprava.png` | Cesta, kufr, ubytování |
| 3 Zákrok | `proces-3-zakrok.png` | Zákrok na klinice |
| 4 Hojení | `proces-4-hojeni.png` | Hojení a péče |
| 5 Růst | `proces-5-rust.png` | Výsledek, růst vlasů (používá se u **žen**) |
| 5 Růst (muži) | `proces-5-rust-muzi.png` | Výsledek u muže – **stejná barva pozadí jako kroky 1–4** (světlé) |

- **Muži** (`transplantace-vlasu-muzi.json`): krok 5 → `proces-5-rust-muzi.png`.
- **Ženy** (`transplantace-vlasu-zeny.json`): krok 5 → `proces-5-rust.png`.

V kódu stránky jsou použité URL z WordPress (např. `.../2026/02/proces-5-rust-muzi.png`).

## Kam soubory umístit

- **Lokální / vlastní server:** Zkopírujte všech 5 PNG do složky **`assets/`** v kořeni projektu (vedle `pages/`). Na webu pak musí být tato složka dostupná pod cestou `/assets/`.
- **WordPress (produkce):** Nahrajte všech 5 obrázků do **Média** (např. do složky `proces` nebo `2026/02`). V Bricks u každého obrázku v sekci Proces vyměňte `src` za URL z WordPress Media (např. `https://maxhair.cz/wp-content/uploads/2026/02/proces-1-konzultace.png`).

## Jak znovu vygenerovat nebo vyměnit

- Nové obrázky lze vytvořit nástrojem pro generování obrázků (v Cursoru např. „vygeneruj obrázek pro krok X podle textu na stránce“).
- **Krok 5 pro muže** (`proces-5-rust-muzi.png`): musí mít **stejnou barvu pozadí** jako kroky 1–4 (světlé pozadí), aby vizuálně ladil s ostatními obrázky v sekci.
- V Bricks nebo v JSON pak upravte atribut `src` u příslušného `<img>`.

## Nasazení (web-builder)

Po úpravě stránky pushněte změny: z adresáře Fellaship-web-builder-tool spusťte `node sync.js push` nebo `push-all`. Viz `.cursor/agents/web-builder.md` a skill **fellaship-web-builder**.
