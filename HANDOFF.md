# HANDOFF — Mondo Segnaletica
> Stato al **2026-07-13** (sessione 11, checkpoint in corso). **Leggi SOLO questo file per ripartire.** Tutto lo storico precedente è superato e rimosso.

---

## 🟢 2026-07-13 — Sessione 11: bug CSS di layout, titoli/slug leggibili, verdetto su Epanza. IN CORSO.

Ultimo commit pushato: **`c54a208`**.

- **Griglia catalogo attaccata → token CSS orfani.** `--space-5` e `--space-10` **non esistono** nella scala: `gap: var(--space-5)` era una dichiarazione invalida → gap a 0. Sostituiti con token validi. Nuovo `public/wp-content/themes/mondosegnaletica/scripts/check-tokens.mjs` agganciato a `pnpm build`: **un token orfano ora blocca la build**.
- **Paginazione in colonna.** `paginate_links(type => 'list')` emette `<ul><li>` che nessuno stilava; le classi del tema (`.pagination__item--current/--dots`) **non esistevano nell'HTML** → regole morte. Passato a `'plain'` e stilate le classi vere (`.page-numbers`, `.current`, `.dots`).
- **Prodotti correlati**: da griglia 3 colonne (4 prodotti = 1 orfano a capo) a **carosello**, riusando `carousel.js` già in home. Portati a 8.
- **Nome card tagliato**: `line-clamp` 2 righe con `line-height: 1.15` su Anton rifilava i glifi → 1.3 + `min-height`. Rimossa la **doppia definizione di `.products-grid`** (vinceva solo per ordine di import).
- **TITOLI.** La colonna ARTICOLO del listino (fino a **338 caratteri**) finiva nell'H1. Aggiunto campo **`nome_breve`** in `normalize.py` (max 90 car, accumula i segmenti separati da trattino: tagliare al **primo** trattino dava lo stesso nome a **67 prodotti su 175**). Nuovo **`tools/import-listini/apply_nomi.php`**: accorcia i titoli su Woo e salva la riga completa in descrizione come *"Denominazione a listino"*. **169 titoli accorciati**, max 90 car, idempotente. `link_figures.py` e `import.php` aggiornati a `nome_breve` (senza, `apply_images` **rimetteva i nomi lunghi**).
- **SLUG.** Nuovo **`tools/import-listini/fix_slug.php`**: **363 slug** rigenerati dai titoli brevi (erano 150+ car → ora max 111, media 41).
- **⚠️ BUG NOTO — `fix_slug.php` non è idempotente in una passata**: 39 slug prendono un suffisso `-2/-3/-4` da WordPress e vanno riscritti al giro dopo; converge solo alla **terza** esecuzione. **DA CORREGGERE.**

### 🔴 VERDETTO: Epanza è un vicolo cieco per le immagini
Scritto `tools/import-listini/scrape_epanza.py` (aggancio per codice figura estratto dallo slug + somiglianza del nome). **Misurato**: il loro catalogo ha **527 prodotti** (noi 1.236), solo **234** con codice figura. Abbinamenti per figura: **13 su 626** immagini mancanti. Per solo nome: 30 sopra 0.6 di somiglianza e **già sbagliati** (Paletto Ø89 agganciato al loro Ø60). Recupero realistico **20-40 su 626**, con **rischio legale** (le foto prodotto sono asset loro; i pittogrammi CdS invece sono standard di legge). **Non hanno le immagini che ci mancano. Da riferire al cliente.**

### TODO PRIORITARIO
1. **IMMAGINI DAI NOSTRI DATI (il filone grosso, in corso)**: `part_d.json` è marcato `type: "altro"` invece di `listino` (126 figure, pagine VER 22-24) → **saltato sia da `normalize` sia da `figure_ocr`**; pagine **VER 26-28 assenti da `extract/`** → vanno riestratte.
2. **Correggere l'idempotenza di `fix_slug.php`** (vedi bug noto sopra).
3. **PDP incompleta**: i meta `_ms_specs` / `_ms_downloads` / `_ms_fig_cds` / `_ms_qty_discounts` **non sono mai popolati** → specifiche tecniche assenti.
4. **Selettore varianti assente** sui prodotti senza prezzo.
5. **Categorie**: una catch-all fantasma con tutti i 1.236 prodotti + 3 categorie vuote.
6. **SKU stampato due volte** in PDP.
7. **Graphify STALE** → `/graphify . --update`.

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
