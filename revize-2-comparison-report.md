# MaxHair Revize Webu 2 – Comparison Report

**Date:** 2026-02-06  
**Test URL (local):** http://localhost:3000  
**Production URL:** https://maxhair.cz  
**Source:** MaxHairDocs/MaxHair - revize webu 2.pdf

---

## Executive Summary

| Category | Implemented | Pending | Notes |
|----------|-------------|---------|-------|
| Horní banner | 2/4 | 2 | Reference→Výsledky done, Transplantace obočí removed |
| Barvy & Design | 3/3 | 0 | ✅ Complete |
| Hero | 2/3 | 1 | Missing floating WhatsApp |
| Služby | 4/4 | 0 | ✅ Complete |
| Proč MaxHair | 2/2 | 0 | ✅ Complete |
| Ceník | 4/4 | 0 | ✅ Complete |
| FAQ | 1/2 | 1 | "Zobrazit všechny reference" still visible |
| Kontakt | 7/8 | 1 | Newsletter check needed |
| O nás | 4/10 | 6 | Multiple changes pending |

**Overall:** ~29/40 requirements implemented (~73%)

---

## Detailed PDF vs Current State

### 1. HORNÍ BANNER

| ID | Requirement | Status | Current State |
|----|-------------|--------|---------------|
| hb-1 | Add text next to logo | ⚠️ PARTIAL | Logo has "MAX HAIR" text – verify if PDF means additional tagline |
| hb-2 | Remove Transplantace obočí from services | ✅ DONE | Not in Služby dropdown (only Muži, Ženy, Vousy, PRP) |
| hb-3 | Rename Reference → Výsledky | ✅ DONE | Nav shows "Výsledky" |
| hb-4 | FAQ in top banner | ❌ PENDING | FAQ only in footer; consider adding to header |

### 2. BARVY & DESIGN

| ID | Requirement | Status | Current State |
|----|-------------|--------|---------------|
| bd-1 | Palette: black, gray, white | ✅ DONE | Dark theme with light sections |
| bd-2 | Gold/yellow accents | ✅ DONE | Gold (#E5C158, #D4AF37) used consistently |
| bd-3 | No brown color | ✅ DONE | No brown in palette |

### 3. HERO PAGE

| ID | Requirement | Status | Current State |
|----|-------------|--------|---------------|
| h-1 | Video: not overpower text, recognizable | ✅ DONE | Overlay + opacity; video visible |
| h-2 | WhatsApp icon: always visible + bottom right | ❌ PENDING | No floating WhatsApp widget in corner |
| h-3 | Hero text with 95% uchycení štěpů | ⚠️ PARTIAL | Text OK; "95% uchycení štěpů" in stats, not in paragraph |

### 4. NAŠE SLUŽBY

| ID | Requirement | Status | Current State |
|----|-------------|--------|---------------|
| s-1 | Section = services (not methods) | ✅ DONE | "Naše služby" section |
| s-2 | Headline: Individuální řešení transplantace vlasů | ✅ DONE | Matches |
| s-3 | Services: Muži, Ženy, Vousy | ✅ DONE | 3 cards with photos |
| s-4 | Photo per service | ✅ DONE | Each card has image |

### 5. PROČ MAXHAIR

| ID | Requirement | Status | Current State |
|----|-------------|--------|---------------|
| pm-1 | Headline: Kompletní péče bez prostředníků | ✅ DONE | Matches |
| pm-2 | Subheadline + 6 benefits | ✅ DONE | All content present |

### 6. CENÍK

| ID | Requirement | Status | Current State |
|----|-------------|--------|---------------|
| c-1 | 69 900 Kč / 2 850€, 75 500 Kč / 3 100€ | ✅ DONE | Prices correct |
| c-2 | Explanatory text about letenka | ✅ DONE | "Stejný zákrok, dvě možnosti ceny..." |
| c-3 | Zpáteční letenka Turkish Airlines in flight option | ✅ DONE | In S letenkou card |
| c-4 | Same bullets both columns | ✅ DONE | Matching lists |

### 7. FAQ

| ID | Requirement | Status | Current State |
|----|-------------|--------|---------------|
| faq-1 | Hide "Zobrazit všechny otázky" | ❌ PENDING | Testimonials still have "Zobrazit všechny reference →" |
| faq-2 | "Nenašli jste odpověď?" → contact form | ✅ DONE | Link present |

### 8. KONTAKT

| ID | Requirement | Status | Current State |
|----|-------------|--------|---------------|
| k-1 | Emphasize WhatsApp | ✅ DONE | Primary CTA |
| k-2 | Subheadline: Nejrychlejší cesta je WhatsApp | ✅ DONE | Matches |
| k-3 | WhatsApp number visible | ✅ DONE | +420 601 515 323 |
| k-4 | Email info@maxhair.cz | ✅ DONE | Present |
| k-5 | Green CTA: Napsat na WhatsApp | ✅ DONE | Green button |
| k-6 | Remove newsletter signup | ⚠️ CHECK | Footer – verify no newsletter |
| k-7 | Social: WhatsApp, IG, FB only | ✅ DONE | Only these 3 |
| k-8 | Virtual address ČR | ✅ DONE | Praha, Česká republika |

### 9. O NÁS PAGE

| ID | Requirement | Status | Current State |
|----|-------------|--------|---------------|
| on-1 | Headline: Přímé spojení na prémiovou kliniku | ✅ DONE | Matches |
| on-2 | Remove "Jak jsme vznikli a v čem jsme jiní" | ❌ PENDING | "Kdo jsme a jak pracujeme" present – verify if same |
| on-3 | Zakladatel: 12 → 8 years | ✅ DONE | Mahmoud: "8 let" |
| on-4 | V čem jsme jiní: 15+ → 8 years | ❌ PENDING | Still "15+ let zkušeností" |
| on-5 | Merve Altun: new bio (DHI/Sapphire, 7+ years) | ❌ PENDING | Current: "Specialistka na ženy" – different text |
| on-6 | Remove numbers from team cards | ❌ PENDING | 5000+, 15+, 3000+, etc. still shown |
| on-7 | Replace stats with "Bezplatná lékařská analýza" | ⚠️ PARTIAL | Has "Bezplatná konzultace" – wording differs |
| on-8 | Remove certifikace, replace with photo | ❌ PENDING | Certifikace section still present |
| on-9 | Remove "Naše čísla mluví za nás" | ❌ PENDING | Section still present |
| on-10 | Prohlédnout reference → Prohlédnout výsledky | ✅ DONE | Link text updated |

---

## Previous UX Report (Hostinger) – Critical Issues

From `ux-ui-test-report.json` (production URL):

1. **Mobile navigation broken** – No hamburger at 320–768px; desktop nav overflows  
2. **Content layout on mobile** – Content shifted left, clipped, white gap on right  
3. **GDPR checkbox** – Appears readonly in accessibility tree  

**Local server note:** Local layout may differ; re-test on Hostinger after deploy.

---

## Priority Fix List (PDF Revize 2)

### High priority

1. **Floating WhatsApp** – Add fixed WhatsApp icon bottom-right on all pages  
2. **O nás – Merve Altun** – Update bio per PDF (DHI/Sapphire, 7+ years, ženy, vlasová linie)  
3. **O nás – numbers** – Remove stats from Seyit Şahin and Merve Altun cards  
4. **O nás – "15+ let"** – Change to "8 let" in "Osvědčené výsledky"  
5. **O nás – "Naše čísla mluví za nás"** – Remove section  
6. **O nás – Certifikace** – Remove, replace with photo  

### Medium priority

7. **FAQ – "Zobrazit všechny reference"** – Hide or replace with "Nenašli jste odpověď?" (testimonials section)  
8. **O nás – "Jak jsme vznikli"** – Confirm removal/merge with "Kdo jsme"  
9. **Hero text** – Add "95% uchycení štěpů" to hero paragraph if required  

### Low priority

10. **FAQ in banner** – Consider adding FAQ link to header  
11. **Logo text** – Confirm if extra tagline next to logo is needed  

---

## Testing Instructions

### Local (before push)

```bash
cd D:\Software\Apps\MaxHair.cz-main\Fellaship-web-builder-tool-main
node local-server.js
```

Then open: http://localhost:3000

### Production (after push to Hostinger)

Test at: https://maxhair.cz

**Viewports to test:** 320px, 375px, 768px, 1024px, 1920px

---

## Files to Modify

| Requirement | File(s) |
|-------------|---------|
| Floating WhatsApp | `footer-maxhair.json` or new global component |
| Merve Altun bio | `pages/o-nas.json` |
| Team numbers removal | `pages/o-nas.json` |
| "15+ let" → "8 let" | `pages/o-nas.json` |
| Remove "Naše čísla" | `pages/o-nas.json` |
| Certifikace → photo | `pages/o-nas.json` |
| "Zobrazit všechny reference" | `pages/homepage.json` (testimonials) |
| Hero text | `pages/homepage.json` |
