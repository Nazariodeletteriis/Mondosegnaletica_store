# Mondo Segnaletica — Progetto E-commerce WooCommerce

> **Per nuove sessioni: leggi prima [HANDOFF.md](HANDOFF.md)** — contiene lo stato corrente e i task immediati. Questo file è il brief completo di riferimento, non va riletto ogni volta.

---

## 1. Cliente & Brand

- **Nome**: Mondo Segnaletica Soc. Coop.
- **Settore**: segnaletica stradale e soluzioni per la viabilità
- **Sede**: provincia di Lucca, Toscana
- **Target B2B**: Enti Pubblici, Imprese di costruzione, Professionisti della viabilità
- **Posizionamento**: tutti i prodotti omologati secondo il **Codice della Strada italiano**
- **Repo GitHub**: https://github.com/Nazariodeletteriis/Mondosegnaletica_store
- **Repo locale**: `/var/www/Mondosegnaletica_store/`
- **Tono**: serio, autoritativo, normativo, italiano. **No vivacità consumer**.
- **Logo**: esistente, monogramma "MS" cromato con strada nella M e rullo nella S. Si tiene così com'è per Fase A; refresh opzionale come Fase B.

---

## 2. Direzione di design — "Sistema Strada"

Direzione visiva approvata e bloccata: fusione tra **rigore editoriale tipografico italiano** (Vignelli, Otl Aicher) e **atmosfera cinematica documentaria** della strada italiana (Roger Deakins). **Industrial-editorial autorevole**. Atmosfera **scura cinematica continua** su tutto il sito.

### Palette (vincolante)
- **Nero asfalto** `#0A0A0A` — sfondo dominante
- **Bianco strada** `#F5F4F0` — tipografia primaria
- **Giallo segnaletico** `#FFCC00` — accent unico (CTA, label numeriche)
- **Rosso vermiglio CdS** `#C8102E` — solo per errori/divieti/out-of-stock o dentro foto cartelli reali

### Tipografia (vincolante)
- **Display H1/H2**: grottesco condensato pesante. Riferimenti: Söhne Breit, Inter Display, Neue Haas Grotesk Display, Monument Extended Bold. Tracking stretto, leading 1.0-1.05, UPPERCASE, tutto bianco su nero.
- **Body**: sans neutro (Inter, IBM Plex Sans, Söhne).
- **Monospace**: per label tecniche, codici prodotto, FIG., coordinate. Riferimenti: JetBrains Mono, IBM Plex Mono. **Giallo `#FFCC00`** per label di sezione tipo `01 / CATALOGO`.
- **Lingua sito**: italiano corretto, no slop ("seamless", "next-gen", ecc.).

### Spacing scale (base 4px)
4, 8, 12, 16, 24, 32, 48, 64, 96, 128, 192, 256.

### Geometria
- Border radius **max 2-4px** (quasi squadrato, niente arrotondamenti consumer)
- Bordi 1px hairline bianco opacità 15-20% per separatori
- **No** glassmorphism, gradient, ombre morbide diffuse, decorazioni SVG astratte, blob, particelle, glow

### Vocabolario visivo signature
- Label numeriche di sezione `01 / CATALOGO`, `02 / SOLUZIONI`, ecc. in monospace giallo
- Marginalia HUD tecnica bottom: coordinate GPS Lucca `43.8438° N · 10.5061° E`, codici FIG. CdS, certificazioni ANAS
- Parallax differenziato cinematico nell'hero animato

---

## 3. Architettura e-commerce

### Categorie merceologiche (6 — vincolanti)
| Codice | Categoria | ~Prodotti | Prezzo da |
|---|---|---|---|
| CAT-01 | Segnaletica Verticale | 412 | € 18,40 |
| CAT-02 | Segnaletica Orizzontale | 156 | € 32,00 |
| CAT-03 | Coni & Transenne | 184 | € 9,80 |
| CAT-04 | Delineatori & Paletti | 96 | € 12,00 |
| CAT-05 | Cantieristica | 312 | € 24,50 |
| CAT-06 | Dissuasori & Accessori | 245 | € 45,00 |

### Regole e-commerce (vincolanti)
- Prezzi sempre `€ X,XX` **IVA esclusa B2B**
- SKU monospace giallo formato `MS-XXX-XXX-NNN`
- Stato disponibilità: pallino verde "Disponibile" / giallo "Limitata" / rosso "Esaurito"
- **Sconti quantità** sempre mostrati nelle PDP (tabella a fasce)
- CTA primaria "Aggiungi al carrello" + secondaria "Richiedi preventivo per quantità" (B2B)
- Filtri con conteggio dinamico (`Pericolo (47)`)
- Spedizione: 24/48h da Lucca

### Stack tecnico previsto
- **CMS**: WordPress
- **E-commerce**: WooCommerce
- **Tema**: custom (NON page builder per la home — pena animazioni rotte). Sezioni standard del catalogo possono usare blocchi o tema solido (Blocksy/Kadence) con design tokens del sistema.
- **Local dev**: **DDEV** (Docker-based, riproducibile) — da configurare quando design è validato
- **Animazioni**: GSAP ScrollTrigger + Lenis smooth scroll
- **Video asset**: Higgsfield / Runway per img2video del hero

---

## 4. Stato avanzamento (aggiornare ogni sessione)

### ✅ Completato
- [x] Direzione "Sistema Strada" approvata
- [x] Decisione logo (Fase A: si tiene attuale; Fase B refresh opzionale)
- [x] Moodboard reference (5 immagini validate via ChatGPT/DALL-E):
  - 1.1 Poster editoriale CdS "Segnali di Pericolo" (tipografia/wayfinding)
  - 1.2 Sistema pittogrammi geometrici 8 elementi
  - 2.1 Strada Toscana crepuscolo POV (asset hero)
  - 2.2 Cantiere stradale notturno italiano
  - 3.1 v2 Hero cinematico stratificato (target hero del sito)
- [x] Mega-prompt Stitch consegnato all'utente
- [x] Asset utente preparati: foto strada, logo cromato, mockup titolo, set 3 pulsanti
- [x] Video di test in `assets/video/test_segnaletica.mp4` e `test_segnaletica_2.mp4`
- [x] MCP Stitch configurato in `~/.config/Code/User/mcp.json` (richiede riavvio VSCode)

### ✅ Completato 25.05.2026 (sessione Anglus + Claude Opus)
- [x] Critique Anglus v1 di home/listing/pdp (14 issue trasversali, 9 decisioni operative aperte)
- [x] Design system "Sistema Strada v2" creato su Stitch (asset `3883062151844380501`): Anton headline / IBM Plex body / JetBrains Mono label / primary `#FFCC00` / bg `#0A0A0A` / roundness 4px
- [x] Catalogo prodotti reali estratto da epanza.com → [assets/epanza-products.md](assets/epanza-products.md) (23 SKU mappati a 6 categorie MS)
- [x] 15 prompt Stitch master scritti da Anglus → [assets/stitch-prompts-batch1.md](assets/stitch-prompts-batch1.md)

### 🟡 In corso (vedi [MORNING-INSTRUCTIONS.md](MORNING-INSTRUCTIONS.md))
- [ ] Eseguire 15 prompt manualmente su Stitch web (MCP timeout nella sessione 25.05 notte)
- [ ] Rispondere alle 9 decisioni aperte da critique Anglus
- [ ] Ruotare API key Stitch esposta in chat il 24.05

### ⏳ Prossimi passi
1. Eseguire i 15 prompt manualmente su Stitch web
2. Critique Anglus v2 sulle nuove schermate generate
3. Estrarre design tokens finali da Stitch per handoff
4. Setup WordPress locale con DDEV
5. **Handoff ad Akille** per build tema custom WordPress + WooCommerce
6. Piano animazioni hero shot-per-shot per Higgsfield
7. Fase B opzionale: refresh logo

---

## 5. Asset disponibili nel repo

```
/var/www/Mondosegnaletica_store/
├── assets/
│   └── video/
│       ├── test_segnaletica.mp4       (test animazione 1)
│       └── test_segnaletica_2.mp4     (test animazione 2)
└── CLAUDE.md                          (questo file)
```

Le 5 reference moodboard ChatGPT e le immagini hero (foto strada, logo cromato, blocco titolo, 3 pulsanti) sono **nella conversazione utente**, non ancora salvate nel repo. Quando si arriverà al build vanno scaricate in `assets/images/` e `assets/brand/`.

---

## 6. Agenti — chi fa cosa

- **Anglus** (`~/.claude/agents/anglus.md`) — Senior Design / UX/UI / art direction. Decide direzione visiva, propone, critica, scrive prompt per generatori (Stitch, Higgsfield, ChatGPT), estrae design tokens. **NON scrive codice di produzione**. Italiano col developer. Modello: **Opus**.
- **Akille** (`~/.claude/agents/akille.md`) — Senior Front-End. Implementa tema WordPress custom, integra WooCommerce, scrive GSAP/Lenis per animazioni, ottimizza performance, accessibility. Riceve handoff da Anglus con tokens + screen + animation specs.

Quando ti scrive il developer su questo progetto, **valuta se è una richiesta di design/direzione (→ Anglus) o di implementazione (→ Akille)** e proponi l'agente giusto.

---

## 7. Regole sessione (token-safe + modello)

### Knowledge graph (graphify)
- Quando il repo avrà struttura tema WordPress scaffoldata, suggerire all'utente `/graphify .` per creare l'indice
- Da quel momento in poi: **prima di leggere file raw, consultare `./graphify-out/GRAPH_REPORT.md`** o usare `/graphify query "<domanda>"`

### Gestione modello
- **Opus** per: critique design, decisioni estetiche, art direction, ragionamento strategico
- **Sonnet** per: scrivere prompt testuali, fare codice ripetitivo, chat di pianificazione
- Suggerire cambio modello (`/model sonnet` o `/model opus`) quando la fase non richiede potenza extra
- Per riavvii: `settings.json .model` su `sonnet` (chat/plan) o `opus` (coding/design)

### MCP attivi (richiede riavvio VSCode dopo modifica)
- `stitch` su `https://stitch.googleapis.com/mcp` (X-Goog-Api-Key in `~/.config/Code/User/mcp.json`)
- ⚠️ **API key Stitch esposta in chat 2026-05-24 → da ruotare**

---

## 8. Riferimenti rapidi

- **Brief originale cliente**: e-commerce B2B segnaletica, home "strepitosa" con animazioni, store standard, dati da epanza.com/it (solo sezione segnaletica stradale)
- **Competitor di riferimento (battere)**: https://epanza.com/it
- **Sede**: 43.8438° N · 10.5061° E (Lucca)

---

## 9. Per riprendere il lavoro

1. Apri VSCode su `/var/www/Mondosegnaletica_store/`
2. Apri nuova chat Claude
3. Verifica con `/model` di essere su Opus se devi fare design critique, Sonnet se devi fare prompt/testi
4. Verifica che lo Stitch MCP risponda (cerca tool `stitch*` disponibili)
5. Leggi questo file
6. Vai alla sezione "Stato avanzamento" e riprendi dal primo task in 🟡 o ⏳

---

*Ultimo aggiornamento: 2026-05-25 · Sessione Anglus*
