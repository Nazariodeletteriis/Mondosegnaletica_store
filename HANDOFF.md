# HANDOFF — Mondo Segnaletica
> Sessione 25.05.2026 — Akille. Leggi solo questo per riprendere.

---

## Dove siamo

1. **Tailwind CSS v4 integrato** — plugin Vite, token Sistema Strada in `@theme`, build 49KB CSS.
2. **Layout full-bleed fixato** — rimossi `wp-block-library` e `global-styles` CSS che strozziavano il layout.
3. **Hero section completata (v1)** — video background, overlay cinematico, headline animata char-by-char, header fixed con IntersectionObserver.
4. **Catalogo prodotti importato** — 68 prodotti WooCommerce (148 variable products + 400 variazioni strutturati nel CSV, import parziale). Categorie corrette e allineate.
5. **Fix ms_get_template_part** — bug scope risolto: le product card ora ricevono `$product` correttamente via `include` invece di `get_template_part`.
6. **Spacing sezioni** — `gap` tra `label-section` e `section-title` aumentato a 24px.

---

## Task immediato (prossima sessione)

### 1. Verificare homepage con prodotti reali
- Aprire `http://mondosegnaletica.ddev.site`
- Controllare che il carousel bestseller mostri le card prodotto
- Controllare che le tab categorie mostrino i prodotti
- Controllare nuovi arrivi

### 2. Fix PHP in VSCode (da fare prima)
```bash
sudo apt-get update && sudo apt-get install -y php-cli
```
Poi riavviare VSCode — il PHP Language Features trova `/usr/bin/php` automaticamente.

### 3. Completare import prodotti
- Il CSV `assets/woocommerce-import.csv` (549 righe, 148 prodotti padre + 400 variazioni) è pronto ma solo 68 prodotti sono stati importati.
- Re-importare da WP Admin → Prodotti → Importa con "Aggiorna prodotti esistenti" spuntato.
- ⚠️ Le immagini puntano a `epanza.com` — WooCommerce tenta di scaricarle all'import. Se fallisce, usare un plugin media importer.

### 4. Sezioni homepage ancora da rifinire
- **Sezione Categorie** — tab attiva, grid prodotti, card senza immagine WC (placeholder)
- **Sezione Bestseller** — carousel funzionante ma da verificare visivamente
- **Sezione Nuovi Arrivi** — idem
- **Sezione Performance Stats** — 4 colonne numeri Anton giallo
- **Footer** — struttura colonne

### 5. Sezione Soluzioni
- `template-parts/home/solutions.php` — template presente, da riempire con contenuto reale

---

## File chiave

| File | Ruolo |
|---|---|
| `template-parts/home/hero.php` | Hero HTML |
| `template-parts/home/categories.php` | Sezione 02 tab categorie |
| `template-parts/home/bestseller.php` | Sezione 03 carousel |
| `template-parts/home/new-arrivals.php` | Sezione 04 carousel |
| `template-parts/home/performance-stats.php` | Sezione stats |
| `template-parts/product/card.php` | Product card riusabile |
| `functions.php` | Helper: `ms_get_template_part`, `ms_format_price` |
| `assets/src/js/modules/carousel.js` | Logica carousel |
| `assets/src/js/modules/category-tabs.js` | Logica tab categorie |
| `assets/src/css/pages/home.css` | Stili homepage |
| `assets/woocommerce-import.csv` | CSV import WooCommerce (148 prodotti, 400 variazioni) |

---

## Stato avanzamento

| Step | Stato |
|---|---|
| Design direction "Sistema Strada" | ✅ bloccato |
| Design system v2 su Stitch | ✅ fatto |
| Scaffold WordPress + tema custom | ✅ fatto |
| DDEV locale | ✅ `http://mondosegnaletica.ddev.site` |
| WooCommerce + 6 categorie | ✅ fatto |
| Tailwind v4 integrato | ✅ fatto |
| Layout full-bleed | ✅ fatto |
| Hero section v1 | ✅ fatto |
| Fix ms_get_template_part (scope bug) | ✅ FATTO questa sessione |
| Catalogo epanza fetchato (471 prodotti, 148 gruppi) | ✅ FATTO questa sessione |
| CSV WooCommerce import (148 variable + 400 variazioni) | ✅ FATTO questa sessione |
| Merge categorie duplicate (-2) | ✅ FATTO questa sessione |
| Spacing label/section-title | ✅ FATTO questa sessione |
| PHP CLI in WSL per VSCode | ⏳ richiede sudo — vedi istruzioni sopra |
| Import completo prodotti (68/148 padre) | 🔴 PROSSIMO |
| Verifica visiva homepage con prodotti reali | 🔴 PROSSIMO |
| Sezioni homepage (categorie, bestseller, stats) | 🟡 in corso |
| Animazioni GSAP/Lenis hero | ⏳ fase 2 |

---

## Come avviare

```bash
export PATH="$HOME/.local/bin:$PATH"
ddev start

cd public/wp-content/themes/mondosegnaletica
pnpm build

# URL
http://mondosegnaletica.ddev.site
http://mondosegnaletica.ddev.site/wp-admin  (admin / Admin1234!)
```

---

## Note urgenti

- ⚠️ Ruotare API key Stitch — esposta in chat 24.05 → Google Cloud Console
- Favicon placeholder (ID 18) — sostituire con ritaglio MS logo reale
- Video hero: `mondosegnaletica_video.mp4` nel tema, ignorato da git (>100MB)
- WP_DEBUG disabilitato. Per debug: `ddev wp config set WP_DEBUG true --raw`
- `assets/images/` e `assets/img/` escluse da git (immagini locali pesanti)
