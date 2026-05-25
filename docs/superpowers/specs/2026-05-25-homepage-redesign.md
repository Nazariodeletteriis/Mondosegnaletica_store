# Homepage Redesign — Spec
> 2026-05-25 · Approvato dall'utente

## Obiettivo
Allineare la homepage al design Stitch, aggiungere sezioni mancanti, dare più spazio ai prodotti (e-commerce first), aggiornare con dati reali.

---

## Struttura homepage finale

| # | Sezione | Stato | Azione |
|---|---|---|---|
| 01 | Hero video | ✅ invariato | — |
| 02 | Categorie + tabs prodotti | 🔴 rebuild | Tabs interattivi per 6 categorie, mostra 8 prodotti per tab |
| 03 | Bestseller carosello | ⚠️ migliorare | Vero slider orizzontale con prev/next |
| 04 | Nuovi Arrivi | 🔴 nuovo | Grid 4 prodotti per data |
| 05 | Soluzioni | 🔴 nuovo | 2-col layout: headline Anton + 3 blocchi servizi |
| 06 | Numeri/Stats | ⚠️ update | Dati reali: 15+a, tel, CdS |
| 07 | Contatti + Footer | ⚠️ update | Dati reali azienda |
| — | Noise overlay | 🔴 nuovo | `div.cinematic-noise` fisso, SVG grain, opacity 0.03 |

---

## 02 / CATALOGO — Tabs interattivi

**Layout:**
- Label sezione `02 / CATALOGO` + titolo Anton
- Tab bar: 6 tab (una per categoria), con nome e count in monospace giallo
- Tab attivo: border-bottom giallo, testo bianco
- Tab inattivo: testo muted, hover bianco
- Contenuto tab: grid 4-col di product cards (8 prodotti max)
- Footer tab: link "Vedi tutti i 412 prodotti →" che porta alla listing della categoria
- Switch tab via JS (no page reload) — classe `is-active` sul tab e sul pannello

**PHP (template-parts/home/categories.php):**
- Render tutti i 6 pannelli nascosti (`hidden`)
- JS toggle visibilità pannelli + classe active sul tab
- WP_Query per categoria: `posts_per_page=8`, `tax_query[product_cat]`
- Fallback: se 0 prodotti, mostra card categoria con messaggio "Catalogo in arrivo"

**JS (assets/src/js/modules/category-tabs.js):**
- Nuovo modulo: event delegation sui tab
- Mostra/nasconde pannelli
- Aggiorna ARIA (`aria-selected`, `aria-hidden`)
- Primo tab attivo di default

---

## 03 / BESTSELLER — Carosello

**Layout:**
- Header row: label + titolo + frecce prev/next (`.carousel-nav`)
- Track: `display:flex`, `overflow-x:hidden`, `scroll-behavior:smooth`
- Card width: `calc(25% - gap)` desktop, `calc(50% - gap)` tablet, `calc(85% - gap)` mobile
- Prev/next: `scrollBy` di una card-width

**JS (assets/src/js/modules/carousel.js):**
- Nuovo modulo generico: `initCarousel(el)`
- Gestisce prev/next, disable quando a inizio/fine
- Usato sia da bestseller che da nuovi arrivi

---

## 04 / NUOVI ARRIVI

**PHP (template-parts/home/new-arrivals.php):** nuovo file
- WP_Query: `orderby=date`, `posts_per_page=8`
- Stessa struttura di bestseller (carosello)
- Label `04 / NUOVI ARRIVI`, titolo "Appena arrivati."

---

## 05 / SOLUZIONI

**PHP (template-parts/home/solutions.php):** nuovo file
- Layout 2 colonne: sinistra titolo Anton grande (`ENGINEERING DELLA VIABILITÀ.`), destra 3 blocchi testo
- Blocchi: `CANTIERI TEMPORANEI`, `VIABILITÀ URBANA`, `STRADE RURALI`
- Bordo sinistro giallo 2px su ogni blocco
- Label `05 / SOLUZIONI`

---

## Dati reali da aggiornare

**front-page.php:**
- Tel: `0583 1646327`
- Indirizzo: Via Carlo Angeloni 360, Lucca / Magazzino Viale Europa 50, Lammari (LU)
- Email: `info@mondosegnaletica.it`
- P.IVA: `02629010460`
- Orari: Lun–Ven 08:00–18:00

**performance-stats.php:**
- `15+ anni di esperienza`
- Rimuovere dato generico "30+ anni"

**footer-columns.php:**
- Aggiornare con dati reali

---

## Noise overlay (globale)

In `inc/setup.php` o in header.php, aggiungere dopo `<body>`:
```html
<div class="cinematic-noise" aria-hidden="true"></div>
```

In `base.css`:
```css
.cinematic-noise {
  position: fixed; inset: 0; pointer-events: none;
  opacity: 0.03; z-index: 9999;
  background-image: url("data:image/svg+xml,..."); /* SVG grain inline */
  background-repeat: repeat;
}
```

---

## File modificati / creati

| File | Tipo |
|---|---|
| `template-parts/home/categories.php` | Rebuild completo |
| `template-parts/home/bestseller.php` | Aggiunta markup carosello |
| `template-parts/home/new-arrivals.php` | Nuovo |
| `template-parts/home/solutions.php` | Nuovo |
| `front-page.php` | Aggiunge new-arrivals + solutions + dati reali |
| `assets/src/js/modules/category-tabs.js` | Nuovo |
| `assets/src/js/modules/carousel.js` | Nuovo (o aggiorna main.js) |
| `assets/src/css/pages/home.css` | Aggiunge stili tabs + carosello + soluzioni |
| `assets/src/css/base.css` | Aggiunge `.cinematic-noise` |
| `inc/setup.php` o `header.php` | Aggiunge div noise |
| `template-parts/home/performance-stats.php` | Dati reali |
| `template-parts/footer/footer-columns.php` | Dati reali |

---

## Fuori scope

- Animazioni GSAP/Lenis (fase 2)
- Import prodotti reali da epanza (task separato)
- Immagini categorie WooCommerce (richiedono upload manuale da admin)
- Immagini hero per le categorie: useremo foto da mondosegnaletica.it come `fallback_image` nel PHP
