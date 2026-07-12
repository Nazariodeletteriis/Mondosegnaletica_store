# HANDOFF — Mondo Segnaletica
> Stato al **2026-07-12** (sessione 9). **Leggi SOLO questo file per ripartire.** Tutto lo storico precedente è superato e rimosso.

---

## ⛔ ERRORE DA NON RIPETERE (letto prima di tutto)

**Ho esaurito i crediti di sessione lanciando 7 subagenti IN PARALLELO, ognuno con ~105 immagini da leggere (700+ letture immagine in un colpo).** Le immagini costano moltissimo.

Gerry monitora il **contesto della finestra** (era al 45%, ampio) — **NON i crediti di sessione**. Sono due cose diverse: il contesto può essere vuoto mentre i crediti sono finiti.

**REGOLA**: letture massive di immagini **a scaglioni, max 2 agenti alla volta**. Mai tutte insieme.

---

## ✅ STATO: LO STORE FUNZIONA

- Sito: **http://mondosegnaletica.ddev.site** — admin / `Admin1234!`
- Ultimo commit pushato: **`922c400`** · working tree pulito

### Catalogo reale importato dai 5 listini PDF del fornitore
- **1.236 prodotti · 35.209 variazioni · 0 errori**
- 674 variabili · 405 semplici · **165 senza prezzo** → CTA "Prezzo su richiesta" + preventivo
- Categorie: Verticale 518 · Dissuasori&Accessori 307 · Cantieristica 246 · Orizzontale 68 · Coni&Transenne 63 · Delineatori 34
- Attributi varianti: **Dimensione × Materiale × Classe rifrangenza × Fissaggio × Versione**
- **302 prodotti con immagine** · 174 rinominati dal pittogramma

### Verificato end-to-end
PDP con menu varianti popolati · acquisto reale 3 × € 11,00 = € 33,00 + IVA 22% € 7,26 = **€ 40,26** · checkout con campo P.IVA · 11 URL a 200 · zero errori PHP.

---

## 🎯 UNICO TASK APERTO — LOTTO 6 del naming immagini

**102 ritagli non ancora letti.** Elenco file: `tools/import-listini/lotti/lote_06`. Gli altri 6 lotti sono fatti (`tools/import-listini/naming2/fig_00..05.json`).

**Come chiudere:**

1. **UN SOLO agente** (⚠️ non 7) che legge i 102 ritagli in `tools/import-listini/crops-raw/` e scrive `tools/import-listini/naming2/fig_06.json`.
   Schema di output (identico agli altri lotti, vedi `fig_00.json`):
   ```json
   [
     { "file": "VER_038_04.png", "figura": null, "nome": "Limite massimo di velocità (disco per veicoli, alluminio)", "confidenza": "alta", "scarto": false }
   ]
   ```
   `figura` = numero FIG. letto **dentro** l'immagine (null se assente) · `scarto: true` se il ritaglio non è un cartello (celle di intestazione tabella, ecc.).

2. Rigenera i link figura→prodotto:
   ```bash
   cd <scratchpad> && MS_SCRATCH=$(pwd) python tools/import-listini/link_figures.py
   ```
   (serve venv con `pymupdf` + `pillow`; se non c'è, ricrearlo)

3. Applica a WooCommerce:
   ```bash
   ddev exec wp --path=/var/www/html/public eval-file tools/import-listini/apply_images.php
   ```
   **`apply_images.php` è IDEMPOTENTE**: salta i prodotti che hanno già immagine, non sovrascrive i nomi veri.

---

## 📞 DA CHIEDERE AL FORNITORE (non risolvibile da noi)
Dettaglio in `tools/import-listini/ANOMALIE.md`.

1. **PDF LISTINO CANTIERISTICA con celle prezzo VUOTE alla fonte** (verificato a 400 DPI): pagg. 27-38 = 107 righe ma solo 19 prezzi; pag. 132 marcata "PREZZI NETTI" con colonna interamente bianca. **Esiste una versione con i prezzi?**
2. Due articoli diversi con lo **stesso codice**: `1200PRCPB0001` ≡ `1200PRCPG0001`
3. Codice `1200TR0010100` quotato **€ 1,20** a pag. 064 e **€ 1,50** a pagg. 069/071
4. ~190 articoli con **"CHIEDERE PREVENTIVO"** al posto del prezzo

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
- `wipe.php` deve cancellare **anche i TERMINI attributo**, non solo i post: i duplicati restano appesi e rompono anche un re-import corretto.
- Il filtro `woocommerce_placeholder_img` riceve `$size` anche come **ARRAY**: tipizzarlo `string` manda il carrello in **fatal error** su ogni prodotto senza immagine.
- L'aggancio ritaglio→figura **per POSIZIONE è inaffidabile** (il rilevatore scambia le celle di intestazione tabella per cartelli). Va fatto **per CODICE letto dalla didascalia dentro l'immagine**.

---

## 📁 FILE CHIAVE

```
tools/import-listini/
├── extract/            JSON estratti dalle 142 pagine
├── normalize.py        normalizzazione → out/prodotti.json
├── import.php          import WooCommerce
├── wipe.php            reset (post + termini attributo)
├── crop_figures.py     ritaglio pittogrammi
├── link_figures.py     aggancio ritaglio → prodotto (per codice)
├── apply_images.php    applica immagini+nomi a WC (idempotente)
├── crops-raw/          ritagli sorgente
├── naming2/            fig_00..05.json (fatti) · fig_06.json (DA FARE)
├── lotti/              lote_00..06 (elenchi file per agente)
├── out/prodotti.json   dataset finale
├── SPEC.md
└── ANOMALIE.md         anomalie da girare al fornitore
```

- Backup DB pre-import: `backups/pre-import-listini-20260712-1019.sql.gz`
- PDF sorgente: `Prodotti/` (gitignored, 46 MB)
