# HANDOFF — Mondo Segnaletica
> Stato al **2026-07-13** (sessione 12 IN CORSO). **Leggi SOLO questo file per ripartire.** Tutto lo storico precedente è superato e rimosso.

---

## 🟡 2026-07-13 — Sessione 12 (CHIUSA): **il pozzo epanza è esaurito**. 143 su 146 = ~98% di tutto il prendibile. Codice scritto, NON committato.

> **RETTIFICA DEFINITIVA della chiusura della sessione 11.** L'ipotesi "il codice figura sta nel titolo della pagina epanza, non solo nell'URL → la copertura sale ben oltre 143" è **FALSA**. Misurata, non assunta.

- **Sondaggio random, 25 schede epanza senza figura nell'URL → 0/25** hanno un codice figura nel contenuto. Non sono cartelli: sono **scarpe antinfortunistiche, guanti, tute, gilet U-Power, DPI**. Epanza vende soprattutto **altro**.
- **Sondaggio mirato, 40 schede che dal nome *sembrano* cartelli → 1/40.** Le altre 39 sono **PANNELLI INTEGRATIVI**: il Codice della Strada li numera per **MODELLO** (mod. 3/d, mod. 6/g), **non per figura**. Il codice non "manca": **non gli spetta**.
- **NUMERO DEFINITIVO** (dal log dello scraper): epanza ha **146 CODICI FIGURA DISTINTI** in tutto il catalogo (2.128 schede, 240 con fig nell'URL → si riducono a **146 codici unici**). Noi ne agganciamo già **143** = **~98% di tutto ciò che epanza può darci**. Nostri: **1.236** prodotti, **1.036** con figura, **494** codici distinti.

> 🔴 **IL POZZO EPANZA È ESAURITO. Non cercare altre leve lì dentro: non ce ne sono.**
> **Conseguenza strategica:** le immagini per gli altri **~350 codici figura** NON verranno da epanza. Vanno **chieste al fornitore** (già in `ANOMALIE.md` punto 1) o prese da **un'altra fonte**.

**Codice scritto (non committato):**
- `scrape_epanza.py`: nuovo **`--sonda-html`** (legge la figura dall'**H1** delle 170 schede sospette; cache in `out/epanza_figure_html.json` — il `''` in cache significa *"ho già guardato, non c'è"*). **Fix**: se l'immagine è già su disco, ora registra comunque `x['file']` nella proposta — senza, al secondo run l'apply non la trovava. Docstring aggiornata col **terzo errore pagato**.
- **`apply_epanza.php` — NUOVO.** Applica le foto a Woo. Idempotente via meta **`_ms_epanza_file`**, **deliberatamente diverso** da `_ms_figura_file`: se riusasse quel meta, `apply_images.php` al giro dopo **rimetterebbe il disegno di listino SOPRA la fotografia**.
- `apply_images.php`: **paletto** — se il prodotto ha `_ms_epanza_file`, non ci rimette sopra il disegno.

**IN CORSO alla chiusura:** `scrape_epanza.py --sonda-html --scarica` gira **detached** (`setsid`, **PID 3635165**, sopravvive alla chat). Log: `tools/import-listini/scrape_epanza.log`. Scarica **~143 foto** in `tools/import-listini/epanza-img/`. Durata ~8 min.

### TODO PRIORITARIO — primo passo della prossima sessione
1. `tail tools/import-listini/scrape_epanza.log` → verifica che sia finito e **quante** immagini ha preso.
2. 👁️ **GUARDARE A OCCHIO 3-4 immagini in `epanza-img/`**: sono davvero **fotografie** dei cartelli, o `og:image` ha restituito **placeholder/logo**? **Non applicare niente** prima di averlo verificato con gli occhi.
3. `wp eval-file tools/import-listini/apply_epanza.php dry-run` → poi **senza** dry-run.
4. Verificare la **resa sul sito**: le foto devono stare meglio dei disegni dentro le card scure.
5. **Committare tutto** (nulla è ancora committato in questa sessione).

### 🔴 RESTA APERTO — prioritario subito dopo le immagini
- **Checkout NON funziona**: 0 gateway di pagamento, 0 zone di spedizione.
- **SMTP assente** → le richieste di preventivo **si perdono**.
- **Legali incompleti**: no T&C, no cookie banner.
- **Categoria fantasma** catch-all.

---

## 🟢 2026-07-13 — Sessione 11 (CHIUSA): bug CSS di layout, titoli/slug leggibili, pista immagini EPANZA aperta. DEPLOYATO.

Commit pushati: **`c54a208`** · **`7ba9c9d`** · **`a0532a8`** · **`1625fbc`** su `main`. Working tree pulito.

- **Griglia catalogo attaccata → token CSS orfani.** `--space-5` e `--space-10` **non esistono** nella scala: `gap: var(--space-5)` era una dichiarazione invalida → gap a 0. Sostituiti con token validi. Nuovo `public/wp-content/themes/mondosegnaletica/scripts/check-tokens.mjs` agganciato a `pnpm build`: **un token orfano ora blocca la build**.
- **Paginazione in colonna.** `paginate_links(type => 'list')` emette `<ul><li>` che nessuno stilava; le classi del tema (`.pagination__item--current/--dots`) **non esistevano nell'HTML** → regole morte. Passato a `'plain'` e stilate le classi vere (`.page-numbers`, `.current`, `.dots`).
- **Prodotti correlati**: da griglia 3 colonne (4 prodotti = 1 orfano a capo) a **carosello**, riusando `carousel.js` già in home. Portati a 8.
- **Nome card tagliato**: `line-clamp` 2 righe con `line-height: 1.15` su Anton rifilava i glifi → 1.3 + `min-height`. Rimossa la **doppia definizione di `.products-grid`** (vinceva solo per ordine di import).
- **TITOLI.** La colonna ARTICOLO del listino (fino a **338 caratteri**) finiva nell'H1. Aggiunto campo **`nome_breve`** in `normalize.py` (max 90 car, accumula i segmenti separati da trattino: tagliare al **primo** trattino dava lo stesso nome a **67 prodotti su 175**). Nuovo **`tools/import-listini/apply_nomi.php`**: accorcia i titoli su Woo e salva la riga completa in descrizione come *"Denominazione a listino"*. **169 titoli accorciati**, max 90 car, idempotente. `link_figures.py` e `import.php` aggiornati a `nome_breve` (senza, `apply_images` **rimetteva i nomi lunghi**).
- **SLUG.** Nuovo **`tools/import-listini/fix_slug.php`**: **363 slug** rigenerati dai titoli brevi (erano 150+ car → ora max 111, media 42, **zero duplicati**). Scrive in **due passate** (parcheggio su `ms-tmp-<id>`, poi slug definitivo) perché altrimenti WordPress accoda `-2` e servono **tre** esecuzioni per convergere. **Idempotenza verificata** sporcando 30 slug in rotazione: una sola passata li ricostruisce identici. ✅ Bug chiuso.

### 🖼️ IMMAGINI — EPANZA È LA PISTA BUONA (pronta, non ancora applicata)

> **RETTIFICA di quanto scritto prima in questo file: il verdetto "EPANZA vicolo cieco" era SBAGLIATO.** Era basato sulla sola categoria 130 (527 prodotti). La **sitemap** dice che il catalogo vero è di **2.128 prodotti**. E **EPANZA è un sito affiliato del cliente**: l'utente ha dato l'**ok esplicito** a prendere le immagini → **nessun vincolo legale**, la nota sui "loro asset" è annullata.

**Perché serve.** Oggi **610 prodotti su 1.236 hanno un'immagine, ma sono i DISEGNI ritagliati dal listino** (pittogrammi al tratto su **fondo bianco**): dentro le card scure del tema si vedono male. **L'utente vuole sostituirle con FOTOGRAFIE vere.**

**Stato del lavoro** — `tools/import-listini/scrape_epanza.py` **riscritto**:
- legge la **sitemap** (2.128 prodotti), aggancia per **CODICE FIGURA letto dall'URL** (`…-fig-412a-…`);
- scarica **solo** le immagini agganciate, **1 richiesta/secondo**, in `tools/import-listini/epanza-img/` (gitignored).
- **MISURATO: 143** nostri prodotti hanno un codice figura coperto da epanza → **24** oggi senza immagine (prendono la prima), **119** col disegno di listino (**passano alla fotografia**).
- Output: `out/epanza_proposte.json` · URL completi in `out/epanza_urls.txt`.

**LEVA NON ANCORA TIRATA — probabilmente vale molto più di 143.** Il codice figura lo cerco
oggi solo nell'**URL**, e ce l'hanno appena 240 prodotti su 2.128. Ma il titolo della loro
scheda lo contiene comunque (l'H1 verificato dice `… Classe 1 **Fig. 412/a** Lamiera
d'acciaio zincato`): il codice c'è, semplicemente non finisce nello slug. Scaricando le 2.128
schede (35 minuti a una richiesta al secondo) e leggendo il codice **dal contenuto della
pagina**, la copertura può salire di parecchio. È il primo esperimento da fare, prima di
applicare qualsiasi cosa.

**Prossimo passo (è il TODO #1):** `python3 scrape_epanza.py --scarica`, poi scrivere l'**apply** che le carica su Woo — **riusare la logica idempotente di `apply_images.php`**, che registra su ogni prodotto il meta `_ms_figura_file`.

**🪤 TRAPPOLA MISURATA — NON agganciare per somiglianza del NOME.** Il punteggio sulla **copertura** del nostro nome dà **0.90** a *"Lamiera di Ferro 10/10"* contro *"cartello attraversamento tramviario"* → spazzatura. E anche i match "buoni" sono **sbagliati**: *"Gilet Classe 2"* pesca il loro *"Gilet Classe 3"*, *"Paletto Ø 89"* pesca il loro *"Ø 60"*. **Solo codice figura esatto.**

### 🔴 VICOLI CIECHI CONFERMATI — NON RITENTARLI
1. **PART_D** (pagine VER 22-24, `type: "altro"`, 126 figure). `figure_ocr.py` ora **ritaglia da ogni pagina con figure** e `normalize.py` ripesca per codice figura → **+6 ritagli, ZERO immagini prodotto in più**. Le figure di quelle pagine (II 100/102/166) **non sono** quelle dei prodotti censiti lì (II 224-231). Filone esaurito.
2. **200 prodotti senza alcun pittogramma nel listino e senza codice figura** → per quelli **né i ritagli né epanza possono fare nulla**: servono **foto dal fornitore** oppure un **aggancio manuale** su epanza.

### STATO STORE (verificato a fine sessione)
**1.236 prodotti · 36.182 varianti intatti** · **610 immagini** tutte coerenti (0 incompatibili, ma sono disegni su fondo bianco) · titoli entro **90 car** · slug puliti e **idempotenti** · tutte le pagine **200**.

### TODO PRIORITARIO — in ordine
1. **🖼️ IMMAGINI EPANZA — scaricare le 143 e applicarle allo store.** È quello che l'utente vuole **subito**. (Dettaglio nel blocco qui sopra.)
2. **🔴 BLOCCANTI VENDITA — il checkout oggi NON funziona**: zero **gateway di pagamento** attivi, zero **zone di spedizione**, **indirizzo negozio vuoto**.
3. **SMTP assente**: i form preventivo/contatti usano `wp_mail` nudo → in produzione le richieste di preventivo (**165 prodotti vendono solo a preventivo**) **si perdono**.
4. **LEGALI**: Privacy Policy in bozza, **Termini e Condizioni inesistenti**, **cookie banner assente**, doppione "Refund and Returns Policy" vs "Spedizioni e Resi".
5. **CATEGORIA FANTASMA**: "Segnaletica Stradale, Cantieristica e Accessori" contiene **tutti i 1.236 prodotti** (catch-all dell'import) + **3 categorie vuote** → inquinano shop e filtri. Da eliminare.
6. **MENU inesistente**: la navigazione è un **array hardcoded** in `nav-primary.php` → il cliente non può modificarla da admin.
7. **PDP**: i meta `_ms_specs` / `_ms_downloads` / `_ms_fig_cds` / `_ms_qty_discounts` **non sono popolati da nessuno script** → la tabella specifiche cade sempre sul fallback `wc_display_product_attributes`, badge FIG. e certificazioni **non compaiono mai**.
8. **Selettore varianti assente sui prodotti senza prezzo**: in `single-product.php`, `woocommerce_template_single_add_to_cart()` è **dentro `if ($ha_prezzo)`** → se `get_price()` è vuoto il menu non viene mai emesso.
9. **GSAP/Lenis mai installati**: animazioni hero ferme alla fase 1 (vanilla JS).
10. **SKU stampato due volte** (`card.php` + hook `ms_show_sku_in_loop`) · **"Pagina di esempio"** WP ancora pubblicata · **WooCommerce 10.7 → 10.9.4** · un prodotto ha slug `inizio` (residuo di parsing) · **Graphify STALE** → `/graphify . --update`.

---

## 🟢 2026-07-12 — Sessione 10: aggancio ritaglio↔figura via OCR locale + 3 bug di fondo corretti. DEPLOYATO.

Ultimo commit pushato: **`1880ea2`** · working tree pulito.

- **LOTTO 6 a vista NON eseguito e DECADUTO.** La lettura delle immagini con un modello **non serve più, mai più** → costo zero. Codici figura e nomi erano **già** in `extract/*.json`: la lettura a vista serviva solo a **riallineare ritaglio↔figura**, perché lo zip per posizione slittava (misurato: sbaglia il **9%**; banco di prova = le 611 letture a vista dei lotti 0-5).
- **Nuovo `tools/import-listini/figure_ocr.py`**: OCR locale gratuito (`rapidocr-onnxruntime`) legge la **didascalia dentro ogni ritaglio**; **assegnamento ottimo cella↔figura per pagina** (Hungarian, `scipy`) su due segnali (codice figura + testo del cartello); soglia **0.55**. **Esattezza misurata 99,4%** (99,7% sulla sola confidenza alta). `verifica_figure_ocr.py` è il test riproducibile contro le 611 letture.
- Caduto il vincolo `celle == figure`: recuperate **14 pagine** che `crop_figures.py` scartava → **+116 figure**.
- **3 bug di fondo corretti**:
  1. `normalize.py` scartava `part_a..part_e` (pagine VER 15-25) perché filtrava il tag dal **nome file** invece che dal campo `pg['tag']` → **185 prodotti e ~9.000 varianti sparivano a ogni rigenerazione**. Aggiunto **guard-rail**: conserva gli SKU che l'estrazione non ricostruisce più, invece di cancellarli in silenzio.
  2. `link_figures.py` agganciava per codice figura **globale**: in ACCESSORI la figura è una **lettera valida solo dentro la pagina** (la "A" di pag. 3 è un segnale di velocità, quella di pag. 6 è una tuta). Ora l'aggancio è per **(listino, pagina, posizione)** via `normalize`.
  3. La colonna **FIG. a volte contiene un elenco** ("E - E1", "466 / 467"): 129 righe non trovavano la figura. Aggiunta `elenco_figure()` che **non spezza i codici veri con barra** (`1/A`, `60/B`, `309/P`), e `scegli_figura()` disambigua due figure con lo stesso codice sulla stessa pagina confrontando il nome figura con l'articolo della riga.
- Nuovo **`purge_immagini.php`**: rimuove le immagini che la mappa non giustifica (residui degli agganci vecchi).

### STATO STORE (verificato)
- **1.236 prodotti · 36.182 varianti INTATTI**
- **610 prodotti con immagine valida** (erano ~300)
- Zero riferimenti rotti · zero nomi corrotti · carrello + placeholder OK · tutte le pagine **200**

### TODO PRIORITARIO
1. **IMMAGINI RESTANTI — 626 prodotti senza foto**, due gruppi distinti:
   - **(A) 200 prodotti NON hanno alcun pittogramma nel listino** (77 Cantieristica · 72 Verticale · 29 Dissuasori · 14 Orizzontale · 8 Coni) → **servono foto dal fornitore**: richiesta da girare al cliente.
   - **(B) ~426 hanno codice figura ma nessun ritaglio**, per tre cause tecniche risolvibili:
     - pagine **VER 26-28 ASSENTI da `extract/`** (mai committate) → 33 SKU non più ricostruibili, **vanno riestratte**;
     - `part_d.json` marcato `type: "altro"` invece di `listino` → **126 figure inutilizzate**;
     - `CAN_030` senza celle vettoriali.
2. **Anomalie fornitore aggiornate** in `tools/import-listini/ANOMALIE.md` (ora **10 voci**): aggiunte CAN pag. 32 (tre codici in una cella → SKU e nome degradati) e CAN pag. 32 (due cartelli diversi entrambi `FIG. A`).
3. **Graphify è STALE** → rilanciare `/graphify . --update`.

### Note di ambiente
- **venv** con `pymupdf` · `pillow` · `numpy` · `scipy` · `rapidocr-onnxruntime` in `<scratchpad>/venv` — **temporaneo, va ricreato** nelle sessioni future.
- `naming/ocr_cache.json` e `crops-raw.old/` sono **gitignored**. La **prima** passata di `figure_ocr.py` costa **~9 min** di OCR; poi la cache la rende istantanea.

---

## ⛔ ERRORE DA NON RIPETERE (letto prima di tutto)

**Nella sessione 9 ho esaurito i crediti lanciando 7 subagenti IN PARALLELO, ognuno con ~105 immagini da leggere (700+ letture immagine in un colpo).** Le immagini costano moltissimo. **Ora è irrilevante**: l'OCR locale ha eliminato del tutto la lettura a vista.

Gerry monitora il **contesto della finestra** — **NON i crediti di sessione**. Sono due cose diverse: il contesto può essere vuoto mentre i crediti sono finiti.

---

## ✅ LO STORE FUNZIONA

- Sito: **http://mondosegnaletica.ddev.site** — admin / `Admin1234!`
- **1.236 prodotti · 36.182 variazioni · 0 errori**
- 674 variabili · 405 semplici · **165 senza prezzo** → CTA "Prezzo su richiesta" + preventivo
- Categorie: Verticale 518 · Dissuasori&Accessori 307 · Cantieristica 246 · Orizzontale 68 · Coni&Transenne 63 · Delineatori 34
- Attributi varianti: **Dimensione × Materiale × Classe rifrangenza × Fissaggio × Versione**
- Verificato end-to-end: PDP con menu varianti popolati · acquisto reale 3 × € 11,00 = € 33,00 + IVA 22% € 7,26 = **€ 40,26** · checkout con campo P.IVA · zero errori PHP.

---

## 📞 DA CHIEDERE AL FORNITORE / CLIENTE (non risolvibile da noi)
Dettaglio completo (10 voci) in `tools/import-listini/ANOMALIE.md`.

1. **200 prodotti senza alcun pittogramma nel listino** → servono **foto dal fornitore**.
2. **PDF LISTINO CANTIERISTICA con celle prezzo VUOTE alla fonte** (verificato a 400 DPI): pagg. 27-38 = 107 righe ma solo 19 prezzi; pag. 132 marcata "PREZZI NETTI" con colonna interamente bianca. **Esiste una versione con i prezzi?**
3. Due articoli diversi con lo **stesso codice**: `1200PRCPB0001` ≡ `1200PRCPG0001`
4. Codice `1200TR0010100` quotato **€ 1,20** a pag. 064 e **€ 1,50** a pagg. 069/071
5. ~190 articoli con **"CHIEDERE PREVENTIVO"** al posto del prezzo
6. CAN pag. 32: **tre codici in una sola cella** → SKU e nome degradati
7. CAN pag. 32: **due cartelli diversi entrambi marcati `FIG. A`**

---

## 🔒 DECISIONI UTENTE GIÀ PRESE (non ridiscutere)
- Prezzi **IVA ESCLUSA**. **NESSUNO sconto quantità** (rimossi quelli finti che il tema applicava di default).
- **Un prodotto per cartello (FIGURA)**, varianti Dimensione × Materiale × Classe.
- **I listini sono la FONTE UNICA**: i 215 prodotti del vecchio import sono archiviati in **DRAFT** (reversibili).
- **Homepage allineamento Stitch: NON prioritario**, rimandato.

---

## 🪤 TRAPPOLE TECNICHE (già pagate — non ricascarci)
- `wp eval-file` **non accetta il flag `--`**, solo argomenti posizionali. E **non ammette `declare(strict_types)`**.
- WooCommerce vuole gli **ID** dei termini in `WC_Product_Attribute::set_options()`, ma lo **SLUG** nel meta `attribute_pa_*` della variazione. Passando gli slug a entrambi → termini duplicati e **menu varianti VUOTI**.
- `wipe.php` deve cancellare **anche i TERMINI attributo**, non solo i post.
- Il filtro `woocommerce_placeholder_img` riceve `$size` anche come **ARRAY**: tipizzarlo `string` manda il carrello in **fatal error** su ogni prodotto senza immagine.
- L'aggancio ritaglio→figura **per POSIZIONE è inaffidabile** (sbaglia il 9%): va fatto per **codice letto dalla didascalia dentro l'immagine** (OCR) + assegnamento ottimo per pagina.
- Il codice figura **NON è globale**: è valido solo **dentro la pagina** del listino.
- Filtrare le pagine dal **nome file** invece che dal campo `pg['tag']` fa **sparire prodotti in silenzio**.

---

## 📁 FILE CHIAVE

```
tools/import-listini/
├── extract/              JSON estratti dalle pagine (⚠️ VER 26-28 MANCANTI)
├── normalize.py          normalizzazione → out/prodotti.json (+ guard-rail SKU)
├── import.php            import WooCommerce
├── wipe.php              reset (post + termini attributo)
├── figure_ocr.py         ritaglio celle + OCR locale + assegnamento Hungarian ⭐
├── verifica_figure_ocr.py test riproducibile vs le 611 letture a vista
├── link_figures.py       aggancio figura → prodotto (listino, pagina, posizione)
├── apply_images.php      applica immagini+nomi a WC (idempotente)
├── purge_immagini.php    rimuove immagini non giustificate dalla mappa
├── crops-raw/            ritagli sorgente (rigenerati da figure_ocr.py)
├── naming/               figure_ocr.json · ocr_cache.json (gitignored)
├── out/prodotti.json     dataset finale
├── SPEC.md
└── ANOMALIE.md           10 anomalie da girare al fornitore
```

- Backup DB pre-import: `backups/pre-import-listini-20260712-1019.sql.gz`
- PDF sorgente: `Prodotti/` (gitignored, 46 MB)
