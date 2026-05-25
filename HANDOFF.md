# HANDOFF — Mondo Segnaletica
> Sessione 25.05.2026 — Akille. Leggi solo questo per riprendere.

---

## Dove siamo

1. **Tailwind CSS v4 integrato** — plugin Vite, token Sistema Strada in `@theme`, build 41KB CSS.
2. **Layout full-bleed fixato** — rimossi `wp-block-library` e `global-styles` CSS che strozziavano il layout. Reset esplicito su `#page, .site, .site-content`.
3. **Hero section completata (v1)**:
   - Video background locale (`assets/video/mondosegnaletica_video.mp4`) con `playbackRate=0.75`
   - Gradient overlay full-height via `.hero__media::after` (copre TUTTA la sezione, non solo il basso)
   - Headline Anton animata char-by-char in ingresso da sinistra (vanilla JS)
   - Subtitle + CTA con fade-in progressivo
   - Tag glass card bottom-right (desktop) — `max-width` e font-size clamp per no overflow
   - Header fixed: solido `rgba(10,10,10,1)` con `backdrop-filter:blur(0px)` di default → semi-trasparente blur(16px) sull'hero (IntersectionObserver). Transizione pulita, no ghosting.
4. **Material Symbols** caricato in enqueue.php → icona carrello funzionante.
5. **Favicon 512×512** generato (placeholder — da sostituire con logo reale).

---

## Task immediato (prossima sessione)

**Scendere sezione per sezione sotto l'hero e sistemare il layout:**

1. **Sezione Categorie** (`02 / CATALOGO`)
   - Grid 3×2 — verificare che le card siano wide e visibili
   - Card senza immagine WooCommerce: aggiungere placeholder decente

2. **Sezione Bestseller** (`03 / BESTSELLER`)
   - Grid 4-col — verificare prodotti demo visibili

3. **Sezione Performance Stats** (`04 / NUMERI`)
   - 4 colonne con numeri Anton giallo — verificare allineamento

4. **Footer** — struttura colonne

5. **Prodotti reali** — importare i 23 SKU da `assets/epanza-products.md`

---

## File chiave

| File | Ruolo |
|---|---|
| `template-parts/home/hero.php` | Hero HTML |
| `assets/src/js/hero.js` | Animazioni hero (char, fadein, header observer) |
| `assets/src/css/pages/home.css` | Stili homepage |
| `assets/src/css/components/header.css` | Header fixed + HUD strip |
| `assets/src/css/base.css` | Reset, container, liquid-glass |
| `assets/src/css/main.css` | Entry Tailwind + @theme tokens |
| `inc/enqueue.php` | Font, CSS, JS loading |
| `inc/setup.php` | Theme support, block assets disabilitati |
| `vite.config.js` | Entry points Vite (main.js, hero.js) |

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
| Fix overlay hero full-height | ✅ FATTO questa sessione |
| Fix header ghosting su scroll | ✅ FATTO questa sessione |
| Fix tag card overflow destra | ✅ FATTO questa sessione |
| Material Symbols (icone) | ✅ fatto |
| Logo reale caricato in WP | ⏳ utente deve caricarlo dal Customizer |
| Sezioni homepage (categorie, bestseller, stats) | 🔴 PROSSIMO |
| Prodotti reali da epanza.com (23 SKU) | ⏳ dopo |
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
- Favicon placeholder (ID 18) — sostituire con ritaglio MS logo reale quando caricato
- Video hero: `mondosegnaletica_video.mp4` nel tema, ma nel repo git è ignorato (>100MB?). Verificare `.gitignore`.
- WP_DEBUG disabilitato. Per debug: `ddev wp config set WP_DEBUG true --raw`
