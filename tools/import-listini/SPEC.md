# Spec estrazione listini Mondo Segnaletica

Le pagine sono PNG in `/tmp/claude-1000/-var-www-Mondosegnaletica-store/3bea9b56-6a94-42df-9fa2-ec0183653dff/scratchpad/pages/`
Naming: `<TAG>_<NNN>.png` — TAG ∈ {VER, ORI, CAN, ACC, GOM}, NNN = numero pagina PDF (1-based, con zeri).

I PDF NON hanno layer di testo: sono immagini. Devi LEGGERE le pagine con lo strumento Read (una alla volta) e trascrivere.

## Obiettivo

Per ogni pagina assegnata produci un oggetto JSON. Scrivi TUTTI gli oggetti in un unico file JSON (array) nel path che ti viene indicato.

## Tipi di pagina

- `copertina` — frontespizio
- `indice` — sommario
- `normativa` — tabelle dimensionali del Codice della Strada, schemi quotati, norme di attuazione. NON contiene prezzi. Non estrarre prodotti.
- `listino` — contiene una tabella prezzi. È l'unica che conta davvero.
- `altro` — qualsiasi altra cosa

## Schema JSON per pagina

```json
{
  "tag": "VER",
  "page": 19,
  "page_label": "019",
  "type": "listino",
  "section": "SEGNALI DI FERMATA - SOSTA E PARCHEGGIO",
  "figures": [
    { "figura": "309/P", "nome": "Strada senza uscita", "pos": 1 },
    { "figura": "77/G",  "nome": "Parcheggio privato",  "pos": 2 }
  ],
  "rows": [
    {
      "wl": "*",
      "codice": null,
      "articolo": "TARGA PIANA",
      "dimensione": "PICCOLA",
      "base_cm": 25,
      "altezza_cm": 45,
      "note": "ALL. 10/10 + 4 FORI",
      "prezzi": [
        { "materiale": "ALLUMINIO 25/10", "classe": "CL1",    "euro": 9.50 },
        { "materiale": "ALLUMINIO 25/10", "classe": "CL2",    "euro": 11.00 },
        { "materiale": "ALLUMINIO 25/10", "classe": "CL2 S.", "euro": 13.00 }
      ]
    }
  ]
}
```

## Regole di trascrizione — LEGGI CON ATTENZIONE

1. **Precisione assoluta sui numeri.** Prezzi e misure vanno trascritti esattamente come stampati. Un prezzo sbagliato finisce in vendita. Se una cifra è illeggibile o ambigua, metti `"euro": null` e aggiungi `"dubbio": "descrizione del problema"` nella riga. NON tirare a indovinare.

2. **Decimali.** Nel PDF il separatore è la virgola (`62,00`). Nel JSON usa il punto: `62.00`.

3. **Il carattere `/` in una cella prezzo significa "non disponibile"** per quella combinazione. Non è un prezzo. Ometti quella voce dall'array `prezzi` (non mettere 0).

4. **Colonne prezzo.** Le tabelle hanno intestazioni a due livelli. Esempio tipico:
   - livello 1: `LAMIERA 10/10` | `ALLUMINIO 25/10`  ← il MATERIALE
   - livello 2: `CL 1` | `CL 2` | `CL 2 S.`          ← la CLASSE DI RIFRANGENZA
   Ogni cella prezzo è quindi l'incrocio materiale × classe. Riporta entrambi in ogni voce di `prezzi`.
   Se la tabella ha una sola fascia di prezzo senza materiale (es. colonna unica `EURO`), usa `"materiale": null`.
   Alcune tabelle hanno come livello 1 `CL 1 / EGP / SERIE 3430`, `CL 2 / HIP / SERIE 3930`, `CL 2 S. / DG / SERIE 4000`: in quel caso la classe è quella e `materiale` è quello dichiarato nel titolo della tabella (es. "ALLUMINIO 15/10").

5. **Titolo tabella.** Spesso sopra la tabella c'è una fascia colorata con la descrizione merceologica (es. "SEGNALI PIANI IN ALLUMINIO 15/10 AUTOSTRADALI FORATI E RIBORDATI PER CANTIERISTICA - COMPLETI DI BULLONERIA"). Mettila in `"table_title"` a livello di pagina. Se ci sono più tabelle con titoli diversi, ripeti il titolo dentro ogni riga in `"table_title"`.

6. **Figure.** Nelle pagine di listino, sopra la tabella, c'è di solito una griglia di cartelli, ognuno con il nome (es. "sosta vietata") e sotto il codice figura (es. "FIGURA 74/B"). Trascrivi TUTTE le figure nell'ordine di lettura (sinistra→destra, alto→basso), con `pos` progressivo da 1. Il campo `figura` è solo il codice senza la parola "FIGURA" (es. `"74/B"`, `"II 441/A ART. 148"`). Il `nome` è la dicitura sotto/dentro il cartello, in minuscolo naturale.
   Se la pagina non ha figure, metti `"figures": []`.

7. **Codice articolo.** Alcuni listini (tipicamente ORI, ACC, GOM) hanno una colonna `CODICE` con codici tipo `2400ASODS0030`. Trascrivilo in `"codice"`. Se non c'è, `null`.

8. **Colonna WL.** È il "wind load" (classe di carico vento). Trascrivila come stringa così com'è (`"*"`, `"7"`, `"6"`, `"/"`). Se la tabella ha due sotto-colonne WL (`FE` e `AL`), usa `"wl_fe"` e `"wl_al"` invece di `"wl"`.

9. **Non inventare mai.** Se una pagina è normativa, `"rows": []` e `"figures": []` (a meno che non ci siano davvero figure catalogate). Meglio una pagina vuota che dati inventati.

10. **Note a piè di tabella** (es. "N.B.: I PANNELLI CON WL INFERIORE A 6 SONO DESTINATI ALLA CANTIERISTICA"): mettile in `"page_note"`.

## Output

Scrivi un array JSON con un oggetto per OGNI pagina assegnata (anche quelle normativa/copertina, con rows vuote), nel file che ti viene indicato. JSON valido, UTF-8.

Poi rispondi con MASSIMO 8 righe: quante pagine di tipo listino, quante righe prezzo totali, quante figure totali, e l'elenco degli eventuali dubbi (pagina + problema). Niente altro.
