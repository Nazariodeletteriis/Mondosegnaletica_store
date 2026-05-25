# Istruzioni per domattina — Mondo Segnaletica

> Sessione del 25.05.2026 notte. L'MCP Stitch non ha risposto (timeout su tutte le generazioni — probabile rate limit / quota / API key vecchia). Hai scelto **Opzione A**: esecuzione manuale via Stitch web. Tutto è pronto qui sotto.

---

## Cosa fare appena ti svegli (15-30 min)

### Step 1. Apri il progetto Stitch sul browser
🔗 https://stitch.withgoogle.com/projects/2719905914431451721

### Step 2. Verifica che il design system "Sistema Strada v2" sia attivo
Nel selettore design system del progetto, scegli **"Sistema Strada v2"** (asset ID `3883062151844380501`).

Caratteristiche v2 (già configurato):
- Headline font: **Anton** (al posto di Bebas Neue)
- Body: IBM Plex Sans
- Label: JetBrains Mono
- Primary: `#FFCC00` (giallo segnaletico)
- Background base: `#0A0A0A` (nero asfalto)
- Roundness: 4px max

### Step 3. Genera le 15 schermate copiando i prompt
Apri il file [assets/stitch-prompts-batch1.md](assets/stitch-prompts-batch1.md).

Per ogni schermata (in ordine):
1. Clic su **"Generate new screen"** in Stitch
2. Imposta device **DESKTOP**
3. Imposta design system **Sistema Strada v2**
4. Copia il `Prompt Stitch` dal markdown e incollalo
5. Clic Generate, aspetta 2-3 min
6. Passa alla prossima

**Ordine consigliato (priorità decrescente)**:

| # | Schermata | Batch | Priorità |
|---|---|---|---|
| 1 | HOME v2 | 1 | 🔴 CRITICA |
| 2 | LISTING SEGNALETICA VERTICALE | 1 | 🔴 CRITICA |
| 3 | PDP CARTELLO STOP | 1 | 🔴 CRITICA |
| 4 | CARRELLO | 2 | 🟡 ALTA |
| 5 | CHECKOUT | 2 | 🟡 ALTA |
| 6 | CONFERMA ORDINE | 2 | 🟡 ALTA |
| 10 | AZIENDA / CHI SIAMO | 4 | 🟡 ALTA |
| 11 | CONTATTI | 4 | 🟡 ALTA |
| 12 | RICHIESTA PREVENTIVO B2B | 4 | 🟡 ALTA |
| 13 | SOLUZIONI VIABILITÀ URBANA | 5 | 🟢 MEDIA |
| 14 | CANTIERI | 5 | 🟢 MEDIA |
| 7 | LOGIN / REGISTRAZIONE | 3 | 🟢 MEDIA |
| 8 | ACCOUNT DASHBOARD | 3 | 🟢 MEDIA |
| 9 | STORICO ORDINI | 3 | 🟢 MEDIA |
| 15 | 404 NOT FOUND | 5 | ⚪ BASSA |

Se Stitch web ti dà rate limit anche lì, ferma e contatta supporto Google / verifica account billing.

### Step 4. Quando hai 3-4 schermate generate
Riapri Claude Code, dimmi *"ho generato N schermate, fai critique Anglus"* e procediamo con iterazione + decisioni mancanti rimaste in sospeso dalla critique precedente:

**9 decisioni Anglus aperte:**
1. Anton OK come font headline finale (al posto di Söhne Breit) o vuoi pagare la licenza Klim?
2. Schema token semantico (vedi critique Anglus, sezione E.2) — confermare per handoff Akille
3. H1 listing categoria = nome categoria, non frase brand — confermare pattern
4. Densità listing: 4 col + toggle tabella, o 3 col rifinita?
5. Microcopy HUD italiana (esempi nel file prompt) — confermare batch
6. Riferimenti normativi CdS: serve il foglio mapping prodotto → FIG → ART validato dal cliente
7. Icone: Material Symbols Google o switch a Lucide?
8. Background: `#0A0A0A` body / `#141313` solo per surface, confermare?
9. Variante "test" su singola HOME prima di rifare tutte? (ora obsoleta — opzione A salta questo)

### Step 5. Quando il batch è soddisfacente
Sblocchiamo handoff Akille per scaffold WordPress/tema custom con design tokens estratti.

---

## File creati in questa sessione

- [CLAUDE.md](CLAUDE.md) — brief progetto (esistente, da aggiornare con stato sessione)
- [assets/stitch-prompts-batch1.md](assets/stitch-prompts-batch1.md) — **15 prompt Stitch pronti** ⭐
- [assets/epanza-products.md](assets/epanza-products.md) — catalogo 23 prodotti reali con SKU/prezzi
- [assets/stitch/](assets/stitch/) — screenshot v1 (home/listing/pdp) + HTML download per riferimento
- [MORNING-INSTRUCTIONS.md](MORNING-INSTRUCTIONS.md) — questo file

---

## Stato decisioni bloccate

- ✅ Design system v2 creato su Stitch
- ✅ Catalogo prodotti reali estratto da epanza.com (autorizzato dal cliente)
- ✅ Critique Anglus v1 completata (14 issue + 9 decisioni aperte)
- ✅ 15 prompt Stitch master scritti da Anglus
- ⏸️ Generazione bloccata per timeout MCP
- ⏸️ 9 decisioni Anglus in attesa di tua risposta

---

## Note urgenti

1. **Ruotare API key Stitch** — esposta in chat 2026-05-24, segnalato in CLAUDE.md ma non ancora fatto. Vai su Google Cloud Console → APIs & Services → Credentials.
2. **Verificare quota account Stitch** — se anche browser dà problemi è quota piano free esaurita.

---

*Buon riposo. Tutto è pronto.*
