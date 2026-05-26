# HANDOFF — Mondo Segnaletica
> Sessione 26.05.2026 (6ª) — Akille. Leggi solo questo per riprendere.

---

## Dove siamo

1. **PDP (`woocommerce/single-product.php`)** — ✅ allineata a Stitch

2. **Listing page (`archive-product.php`)** — ✅ allineata + filtri funzionanti (sessione 6ª):
   - Sidebar filtri: `form GET` con `input[type=checkbox]` reali, stile on-brand
   - Attributi WC attivi: `pa_tipologia` (84 Ind, 18 Pre, 12 Per), `pa_formato`, `pa_classe-rifrangenza`
   - 114 prodotti Segnaletica Verticale con attributi assegnati
   - Select "Ordina per" dark (`color-scheme:dark`)
   - Hero: label `CAT-01 / CATALOGO` in monospace giallo
   - Grid 3 colonne (era 4), sidebar 260px

3. **Categorie WC** — ✅ struttura 4 macro + 6 sotto (sessione 6ª):
   - **Segnaletica Stradale, Cantieristica e Accessori** (109) — 215 prodotti
     - Segnaletica Verticale (16) — 114p
     - Segnaletica Orizzontale (17) — 5p
     - Coni e Transenne (18) — 19p
     - Delineatori e Paletti (19) — 10p
     - Cantieristica (20) — 44p
     - Dissuasori e Accessori (21) — 23p
   - **Segnaletica di Sicurezza** (107) — 0 prodotti (da riempire)
   - **Segnaletica Aziendale, Privata e Accessori** (108) — 0 prodotti (da riempire)
   - **ADR e Segnaletica per Mezzi da Lavoro** (110) — 0 prodotti (da riempire)

4. **Caroselli homepage** — ✅ autoplay loop 3.5s

5. **Homepage** — struttura completa ma **non ancora allineata a Stitch**.

---

## Task immediato (prossima sessione)

### 1. Prodotti con variazioni
Il PDP deve mostrare variazioni: `Dimensione` (60cm, 90cm...) × `Classe Rifrangenza` (CL1/CL2).
**Approccio**:
- Converti prodotti esistenti in "variable" con varianti Dimensione+Classe
- Oppure importa CSV da tuttosegnaletica.it (scraper in `/tmp/scrape_v2.py`)
- Fonte: https://tuttosegnaletica.it — prodotti e descrizioni (immagini pubbliche, no copyright issue)
- Esempio URL prodotto: `https://tuttosegnaletica.it/caduta-massi-a-ferro-cl-1-60cm-2`
- Pattern slug: `{nome}-{materiale}-cl-{1|2}-{dim}cm-{N}`

### 2. Import prodotti per 3 categorie vuote
- Segnaletica di Sicurezza: antincendio, emergenza, divieto (da tuttosegnaletica.it)
- Segnaletica Aziendale: proprietà privata, parcheggio, ecc.
- ADR: merci pericolose, limiti km lavoro, ecc.

### 3. Homepage allineamento a Stitch
Screen ID: `3014af5957f043b9adb4a8795d0faaad` (12656px).

### 4. Animazioni GSAP/Lenis hero
- `assets/src/js/modules/hero.js` usa solo CSS transitions ora
- Lenis smooth scroll + GSAP ScrollTrigger parallax
- Video: `assets/video/mondosegnaletica_video.mp4`

---

## Lezione importante (NON ripetere)

- **Non patchare incrementalmente** — prima leggere il file completo, poi riscrivere da zero se la struttura diverge.
- **Verificare sempre il file dopo la modifica** (curl o Read) prima di dire "fatto".
- **Fare il build Vite** dopo ogni modifica CSS/JS — senza build le modifiche non appaiono.
- **Proporre nuova sessione** quando si superano ~30 tool call cumulativi.
- **Commit + push** a fine ogni chat — mai lasciare lavoro non committato.

---

## Screen Stitch — Progetto 2719905914431451721

| Titolo | Screen ID | Note |
|---|---|---|
| Home Page - Mondo Segnaletica v2 | `3014af5957f043b9adb4a8795d0faaad` | 12656px — **prossima priorità** |
| Scheda Prodotto - Cartello STOP | `d7e3b0e73b664bb18a166957e55e5c7e` | PDP — ✅ allineata |
| Listing Categoria - Segnaletica Verticale v2 | `c230f456284d4aadba66f1152b286bb3` | ✅ allineata |
| Listing Categoria - Segnaletica Verticale | `5e7b533230dd435090cbfaf558b64a43` | v1 — sorpassata |
| Richiesta Preventivo B2B | `52ebe092e1df4d1cb7ea9405ca13eaae` | form preventivo |
| Checkout B2B - Mondo Segnaletica v2 | `2c05dc1413ea4a0fa6c7a61493a4cabb` | checkout v2 |
| Carrello - Mondo Segnaletica v2 | `865421d4ed434139940c9568dda36ea3` | cart |
| Conferma Ordine - Mondo Segnaletica v2 | `2f56489683644124884ffae1f1a49e36` | order confirmation |
| Dashboard Account B2B | `69deb0a413d34361b0b604f0cf143d5d` | area cliente |
| Login / Registrazione v2 | `889637681df448a995aea6d8e0d3a9e8` | login |
| Contatti | `3b596443753043128c073eb44cb43dd3` | pagina contatti |
| Soluzioni / Viabilità Urbana | `fa55cd01c31443dbbd7944a487c9d941` | soluzioni |
| Cantieri / Segnaletica Temporanea | `3d16dd0c702148d18bd93805914c2215` | cantieri |
| Azienda / Chi Siamo | `50b429f3925543aabfb880e3e722f157` | chi siamo |
| 404 Not Found | `28ead8b27be44353853c74577976d169` | 404 |
| Mondo Segnaletica Components | `7ba17205f8b344808ac1eb99b8b9bd39` | libreria componenti (JSON) |

**Design system Stitch ID**: `3883062151844380501`

---

## File chiave

| File | Ruolo |
|---|---|
| `woocommerce/single-product.php` | PDP — riscritta 26.05, classi `.pdp-*` |
| `woocommerce/archive-product.php` | Listing — riscritta 26.05 (5ª), filtri dinamici |
| `woocommerce/global/quantity-input.php` | Override stepper quantità WC |
| `template-parts/product/quantity-table.php` | Tabella sconti 3 fasce `.qty-tiers__grid` |
| `template-parts/product/card.php` | Product card — CTA CONFIGURA |
| `template-parts/home/categories.php` | 02/CATALOGO tab |
| `template-parts/home/bestseller.php` | 03/BESTSELLER carousel autoplay |
| `template-parts/home/new-arrivals.php` | 04/NUOVI ARRIVI carousel autoplay |
| `assets/src/css/pages/single-product.css` | Stili PDP |
| `assets/src/css/pages/archive.css` | Stili listing |
| `assets/src/css/components/product-card.css` | Card + CTA CONFIGURA |
| `assets/src/css/base.css` | Container globale, variables |
| `inc/woocommerce.php` | Hook WC + filtri attributo (sezione 10) |
| `assets/src/js/modules/carousel.js` | Autoplay loop 3.5s |
| `assets/src/js/modules/hero.js` | Hero (da animare con GSAP) |

---

## Stato avanzamento

| Step | Stato |
|---|---|
| Design direction "Sistema Strada" | ✅ |
| Design system v2 su Stitch | ✅ |
| 22 screen Stitch complete | ✅ |
| Scaffold WordPress + tema custom | ✅ |
| DDEV locale | ✅ `http://mondosegnaletica.ddev.site` |
| WooCommerce + 6 categorie + 215 prodotti | ✅ |
| **Listing page allineata a Stitch** | ✅ completata 26.05 (5ª) |
| **PDP allineata a Stitch** | ✅ completata 26.05 (4ª) |
| **Caroselli autoplay loop** | ✅ completati 26.05 (5ª) |
| Homepage allineata a Stitch | ⏳ |
| Animazioni GSAP/Lenis hero | ⏳ |
| Checkout / Cart / Account B2B | ⏳ fase 3 |
| Pagine statiche (Contatti, Azienda, Soluzioni, 404) | ⏳ fase 3 |

---

## Come avviare

```bash
export PATH="$HOME/.local/bin:$PATH"
ddev start

cd public/wp-content/themes/mondosegnaletica
npm run build   # SEMPRE dopo modifiche CSS/JS

# URL
http://mondosegnaletica.ddev.site
http://mondosegnaletica.ddev.site/wp-admin  (admin / Admin1234!)
```

---

## Note urgenti

- ⚠️ Ruotare API key Stitch — esposta in chat 24.05 → Google Cloud Console
- Favicon placeholder — sostituire con ritaglio MS logo reale
- Video hero: `mondosegnaletica_video.mp4` nel tema, ignorato da git
- WC coming-soon: deve essere `no` — verificare con `ddev exec wp --path=/var/www/html/public option get woocommerce_coming_soon`
