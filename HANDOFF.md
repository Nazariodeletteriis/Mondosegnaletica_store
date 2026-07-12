# HANDOFF — Mondo Segnaletica
> Sessione 26.05.2026 (6ª) — Akille. Leggi solo questo per riprendere.

## 🟢 2026-07-12 (8ª) — Fix massivo pre-completamento: italianizzazione, carrello/cassa sbloccati, 123 prodotti resi acquistabili, IVA attivata. COMPLETATA E PUSHATA.

**Commit `f6d0fd0` + `13e4b11` su `origin/main`. Working tree pulito.**
Verifica finale: 19 URL testati — status corretti, 0 errori PHP, `<div>` bilanciati.

### Localizzazione
`WPLANG` era `en_US` → `it_IT` + language pack WooCommerce, timezone `Europe/Rome`, formato data `d/m/Y`, valuta € `left_space`. Slug italianizzati: `/negozio/` `/carrello/` `/cassa/` `/mio-account/` `/prodotto/` `/categoria-prodotto/`.

### BLOCCANTE risolto
Carrello(7) e Cassa(8) erano pagine a **blocchi Gutenberg**: i template PHP override del tema non venivano MAI eseguiti, il sito serviva la UI React chiara di WooCommerce su fondo nero. Convertite a shortcode `[woocommerce_cart]` / `[woocommerce_checkout]` / `[woocommerce_my_account]`. Da lì tutto il resto è diventato correggibile.

### Tema (commit `13e4b11`)
- `cart.php`: classi WC standard, link "rimuovi articolo" (prima si svuotava solo con qty 0), "Aggiorna carrello" che era morto, CTA preventivo
- `cart-empty.php` nuovo; `form-checkout.php` gate invertito corretto + grid inline rimossa
- `myaccount/`: 6 override (era il default WC — form bianco su sito nero)
- `get_header()`/`get_footer()` nei template WC chiudevano `</body></html>` **prima** di `</main>`: documento malformato su ogni pagina WC. Risolto.
- `page.php`: rimossa `.tab-panel__content` (classe mai definita nel CSS)
- `search.php`: bug DOM, `</div>` orfano con 0 risultati
- Form contatti/preventivo: nonce + honeypot + Post/Redirect/Get. **Il consenso privacy era required SOLO lato browser, mai validato sul server** → un POST diretto lo saltava. Chiuso.
- skip-link + `.screen-reader-text` (non esistevano); `quantity.js` delega su `document` (gli stepper morivano dopo l'AJAX del carrello); `woo-custom.js` negli entry Vite
- `categories.php`: i conteggi cadevano sui numeri **stimati** del brief (412, 156…) se una categoria aveva 0 prodotti → ora solo dati reali
- `solutions.php`: link categoria puntavano a `/negozio/<slug>` = 404 → `get_term_link()`
- `archive.css` `.archive-main { min-width: 0 }`; `404.php` flex-wrap
- Hero (`f6d0fd0`): parallax da `object-position` fuori range a `transform: translate3d` dentro il margine di `scale(1.12)` + guard `prefers-reduced-motion`; `.gitignore` non versionava più `hero-bg.webp`

### Dati WooCommerce (nel DB, NON nel repo — backup pre-fix: `/tmp/pre-fix-backup.sql.gz`)
- **123 prodotti** (non 5!) avevano il **dropdown varianti VUOTO = non acquistabili**. Causa: l'importer creava UN solo termine il cui nome era la lista pipe-joined (`"Maschio|Femmina"`) invece di N termini; le variazioni puntavano agli slug singoli. Fix: 170 termini creati, 4 attributi custom→tassonomia, 8 meta variazione riallineati. Verificato: **0 dropdown vuoti**.
- **IVA ATTIVATA**: `calc_taxes=yes`, aliquota IT 22% standard. Carrello reale: Subtotale € 37,80 → IVA 22% € 8,32 → Totale € 46,12. Prezzi IVA esclusa, IVA voce separata (corretto B2B).
- 29 variable con 1 sola variazione → convertiti a simple (variable 151→122, simple 64→93). Nessuno SKU toccato.
- `Uncategorized` eliminata (WC la ricrea da sola al prossimo update, è by design)
- Attivato `woocommerce_enable_myaccount_registration=yes` (senza, la colonna registrazione non esisteva)
- 9 pagine create e riempite: `contatti`(1102) `azienda`(1103) `soluzioni`(1104) `cantieri`(1105) `richiedi-preventivo`(1106) `faq`(1107) `spedizioni`(1108) `download`(1109) `cookie-policy`(1110)

### ⚠️ Nota Git
Il push era fallito **403**: ci sono **due account `gh`** configurati e quello attivo (`nazariodeletteriis-it`) non ha permessi. Risolto con `gh auth switch --user Nazariodeletteriis`. Se ricapita, è quello.

### 🔵 DECISIONI APERTE PER L'UTENTE (nessuna bloccante per il funzionamento)
1. **34 prodotti "senza attributi" NON sono incompleti**: sono un **primo import superato**. Hanno SKU legacy (`MS-VRT-*`, `MS-CON-*`, `MS-DLP-*`) e sono duplicati del CSV più recente — #92≡#86 (100%), #35≡#775 (100%), #72≡#318 (83%), #29≡#720 (80%), coni #154-160 ≡ #682-687, cavalletti #110-116 ≡ varianti di #838. **Nulla è stato cancellato.** Decidere dedupe/merge — probabilmente risolve anche il punto 3.
2. **4 prodotti senza immagine** (744, 745, 747, 14): la colonna immagine del CSV è **vuota all'origine**. Nessuna immagine assegnata (i candidati in media library sono di altri prodotti).
3. **SKU**: 185/215 fuori dallo schema dichiarato `MS-XXX-XXX-NNN`.
4. **Stock non gestito su NESSUN prodotto** (`_manage_stock=no` ovunque) → i 3 stati del design (verde Disponibile / giallo Limitata / rosso Esaurito) non si attivano mai: tutto è sempre "Disponibile".
5. **Dati societari** nelle pagine nuove: REA/PEC/SDI e condizioni di reso sono **segnaposto espliciti**, da validare col cliente.

### TODO PRIORITARIO
1. **Homepage**: allineamento a Stitch screen `3014af5957f043b9adb4a8795d0faaad` (unica area funzionale rimasta)
2. Import prodotti nelle 3 categorie vuote (Sicurezza 107, Aziendale 108, ADR 110)
3. Prendere le decisioni 1-5 qui sopra
4. **Ruotare API key Stitch** (esposta in chat il 24.05, ancora aperta)
5. `/graphify . --update` (il grafo è stale)

---

## 2026-06-28 — Checkpoint automatico 10min — nessuna modifica, in attesa input utente. IN CORSO.

- Sessione aperta, nessuna modifica al codice in questa finestra
- Stato invariato: hero WebP + parallax + overlay completati, verifica visiva pendente
- Nessun commit effettuato

### TODO PRIORITARIO
1. Verifica visiva parallax + overlay su desktop e mobile
2. Commit git delle 4 modifiche (hero-bg.webp, hero.php, home.css, hero.js)
3. Homepage allineamento a Stitch screen `3014af5957f043b9adb4a8795d0faaad`

---

## 2026-06-28 — Hero refactor: video rimosso, WebP + parallax implementato. IN CORSO.

- hero-bg.webp aggiunto in theme/assets/images/ (218KB, -90% vs PNG originale)
- hero.php: video rimosso, immagine WebP con parallax via object-position
- home.css: overlay scurito + classe .hero__bg-img aggiunta
- hero.js: parallax rAF scroll object-position -60% a 60%, codice video morto rimosso
- Vite rebuild completato con successo
- Nessun commit effettuato — da fare nella prossima sessione

### TODO PRIORITARIO
1. Commit git delle 4 modifiche (hero-bg.webp, hero.php, home.css, hero.js)
2. Verifica visiva mobile del parallax
3. Continuare sviluppo tema

- Nessuna modifica aggiuntiva in questa finestra
- Stato: hero WebP + parallax + overlay completati, verifica visiva pendente
- Nessun commit effettuato

### TODO PRIORITARIO
1. Verifica visiva parallax + overlay su desktop e mobile
2. Commit dopo verifica visiva
3. Homepage allineamento a Stitch screen `3014af5957f043b9adb4a8795d0faaad`
4. Prodotti con variazioni (Dimensione x Classe Rifrangenza)
5. Import prodotti 3 categorie vuote (Sicurezza, Aziendale, ADR)

---

## 2026-06-28 — Hero ottimizzato: WebP + overlay + parallax. IN CORSO.

- background_new.png convertito in WebP 218KB (-90% rispetto a 2.3MB PNG)
- Video hero rimosso dalla homepage, sostituito con immagine WebP
- Overlay hero scurito da 45% a 95% gradiente per leggibilita titolo
- Parallax bg image: object-position animato da -60% a 60% via rAF scroll (vanilla JS, hero.js sezione 4)
- Vite rebuild completato, nessun commit effettuato (verifica visiva pendente)

### TODO PRIORITARIO
1. Verifica visiva parallax + overlay su desktop e mobile
2. Commit dopo verifica visiva
3. Homepage allineamento a Stitch screen `3014af5957f043b9adb4a8795d0faaad`
4. Prodotti con variazioni (Dimensione x Classe Rifrangenza)
5. Import prodotti 3 categorie vuote (Sicurezza, Aziendale, ADR)

---

## 2026-06-28 — Parallax hero + overlay scurito. IN CORSO.

- Parallax bg image hero: object-position animato da -60% a 60% via rAF scroll
- Overlay hero scurito per leggibilità titolo
- CSS aggiornato, Vite rebuild ok
- Nessun commit effettuato (verifica visiva pendente)

### TODO PRIORITARIO
1. Verifica visiva parallax su desktop e mobile
2. Commit dopo verifica
3. Prodotti con variazioni (Dimensione x Classe Rifrangenza)
4. Import prodotti 3 categorie vuote (Sicurezza, Aziendale, ADR)
5. Homepage allineamento a Stitch screen `3014af5957f043b9adb4a8795d0faaad`

---

## 2026-06-28 — Checkpoint 10min: WebP hero completato. IN CORSO.

- background_new.png convertito in WebP: 2.3MB → 218KB (-90%)
- Video hero rimosso dalla homepage
- Immagine WebP impostata come bg fisso nella hero section
- CSS aggiornato, Vite rebuild completato
- Nessun commit ancora effettuato (verifica visiva pendente)

### TODO PRIORITARIO
1. Verifica visiva render hero WebP su desktop e mobile
2. Commit dopo verifica
3. Prodotti con variazioni (Dimensione x Classe Rifrangenza)
4. Import prodotti 3 categorie vuote (Sicurezza, Aziendale, ADR)
5. Homepage allineamento a Stitch screen `3014af5957f043b9adb4a8795d0faaad`

---

## 2026-06-28 — Checkpoint: sostituzione video hero con immagine WebP. IN CORSO.

- Modifica in corso: hero homepage — video background sostituito con immagine statica WebP
- Scopo: riduzione dipendenza da file video pesanti, miglior compatibilita browser/mobile
- Nessun commit effettuato al momento del checkpoint

### TODO PRIORITARIO (aggiornato al checkpoint)
1. Completare sostituzione video con WebP nella hero homepage
2. Verificare render corretto su desktop e mobile
3. Commit dopo verifica visiva

---

## 2026-06-28 — Checkpoint Gerry 10min. NESSUNA MODIFICA.

- Checkpoint automatico: nessuna modifica al codice in questa chat
- Stato codebase identico all'ultimo commit (`9432af6`)
- DDEV live su http://mondosegnaletica.ddev.site

### TODO PRIORITARIO
1. Prodotti con variazioni (Dimensione x Classe Rifrangenza)
2. Import prodotti 3 categorie vuote (Sicurezza, Aziendale, ADR)
3. Homepage allineamento a Stitch screen `3014af5957f043b9adb4a8795d0faaad`

## 2026-06-28 — Sessione 7: DDEV riavviato, sito live. IN CORSO.

- DDEV era in stato "exited", riavviato con successo
- Sito live su http://mondosegnaletica.ddev.site
- Gerry configurato con checkpoint token ogni 10min
- Nessuna modifica al codice PHP/template questa sessione

### TODO PRIORITARIO
1. Riprendere sviluppo tema custom WordPress da dove lasciato (vedi sessione 6)
2. Verificare che WooCommerce risponda correttamente su ddev.site
3. Prossimi step Akille: implementazione PDP, filtri, animazioni GSAP

## 🟡 2026-06-28 — Checkpoint automatico 10min. NESSUNA MODIFICA.

- Sessione aperta, nessun file modificato in questa chat
- Stato codebase identico all'ultimo commit (`9432af6`)
- Prossima azione: vedi Task immediato sotto

### TODO PRIORITARIO
1. Prodotti con variazioni (Dimensione x Classe Rifrangenza) — scraper `/tmp/scrape_v2.py` o import CSV
2. Import prodotti 3 categorie vuote (Sicurezza, Aziendale, ADR)
3. Homepage allineamento a Stitch screen `3014af5957f043b9adb4a8795d0faaad`

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
