# Anomalie del listino fornitore — decisioni necessarie prima dell'import

Queste NON sono errori di lettura: gli agenti le hanno verificate a zoom.
Sono incoerenze stampate nei PDF originali.

## IL PIÙ GROSSO: prezzi assenti alla fonte

Il listino CANTIERISTICA ha **celle prezzo vuote nel PDF originale**, non è un problema
di lettura. Verificato personalmente a 400 DPI su pag. 29: le celle sono bianche, e in
un caso contengono solo una virgola senza cifre. Pag. 132 è marcata "PREZZI NETTI" con
l'intera colonna vuota (23 righe).

- CAN pagg. 27-38: 107 righe, solo **19 prezzi** leggibili
- CAN pag. 132: 23 righe, **zero** prezzi

Conseguenza: questi prodotti NON possono essere venduti con "Aggiungi al carrello".
Vanno pubblicati senza prezzo, con CTA "Richiedi preventivo".
DA CHIEDERE AL FORNITORE: esiste una versione del listino cantieristica con i prezzi?

## Bloccanti per l'import (SKU/prezzo ambiguo)

| # | Listino | Codice | Problema | Impatto |
|---|---|---|---|---|
| 1 | GOM | `1200TR0010100` | Stesso codice (tassello Ø10x100) quotato **€ 1,20** a pag. 064 e **€ 1,50** a pag. 069 e 071. Entrambi nitidi. | Quale prezzo va a catalogo? |
| 2 | GOM | `1200PRCPB0001` | Codice identico a `1200PRCPG0001` (paracolpi B vs G). Probabile refuso fornitore. | Due prodotti non possono avere lo stesso SKU |
| 3 | ORI | `2400ASOPP0041` | "DIMA PISTA PEDONALE": stampato con doppia P, rompe il pattern `2400ASOD**`. Probabile refuso per `2400ASODP0041`. | SKU da correggere o tenere com'è |
| 4 | ORI | `2400AS0DD0037` | "DIMA DISABILE": ha uno **zero** al 7° carattere invece della lettera O. Probabile refuso per `2400ASODD0037`. | Idem |

## Non bloccanti, ma da sapere

| # | Listino | Cosa | Nota |
|---|---|---|---|
| 5 | GOM | `1200ROT000830` | Diametro interno stampato **6,34 MT**; tutte le altre righe seguono la regola esterno − 2,00. Confermato al 5x: è davvero 6,34. Possibile refuso fornitore. |
| 6 | GOM | Tutte le pagine listino | Nota a piè pagina: **"per quantità inferiori a quelle sopra riportate aumento del 10%"**. C'è quindi una quantità minima d'ordine con maggiorazione. Oggi lo store NON la gestisce. |
| 7 | ORI | 5 articoli | Macchine tracciatinee, vernice spartitraffico, diluente: prezzo = **"CHIEDERE PREVENTIVO"**. Vanno a catalogo senza prezzo, con CTA preventivo. |
| 8 | GOM | 5 articoli | Passaggi pedonali rialzati mod. A-E: **"CHIEDERE PREVENTIVO"**. Idem. |
| 9 | CAN | pag. 32 | La cella CODICE contiene **tre codici in una riga sola** (`2200TRATPSF07 (senza fascia) / 2200TRATPMO07 (CL. 1) / 2200TRATBI008 (CL. 2)`). Quella stringa è finita a fare da SKU **e** da nome del prodotto. Vanno separati in tre articoli. |
| 10 | CAN | pag. 32 | Due cartelli diversi — una transenna e un New Jersey — sono battezzati **entrambi FIG. A**. Il codice non li distingue, quindi nessuno dei due riceve immagine (scelta di sicurezza). Serve un codice figura distinto. |

## Immagini prodotto: cosa manca e perché

Copertura attuale: **610 prodotti su 1.236 hanno l'immagine del proprio cartello**, ritagliata
dal listino e agganciata leggendo la didascalia dentro il ritaglio (`figure_ocr.py`, 99,4%
di esattezza misurata). I 626 senza immagine si dividono in due gruppi, e solo uno dei due
possiamo chiuderlo da soli.

**A) 200 prodotti: nel listino non esiste proprio un pittogramma.** Non è un difetto della
pipeline — quelle pagine hanno solo tabelle. Servono **fotografie dal fornitore**:

| Quanti | Categoria |
|---|---|
| 77 | Cantieristica (segnaletica temporanea, cavalletti) |
| 72 | Segnaletica Verticale (pannelli integrativi, passo carrabile) |
| 29 | Dissuasori e Accessori |
| 14 | Segnaletica Orizzontale |
| 8 | Coni e Transenne |

**B) ~426 prodotti: hanno un codice figura, ma il ritaglio non arriva.** Cause note:
- pagine **VER 26-28 assenti da `extract/`**: l'estrazione non è mai stata committata, quindi
  33 SKU del catalogo non sono più ricostruibili dallo script (oggi vengono conservati dal
  file precedente, vedi il guard-rail in `normalize.py`). **Vanno riestratte.**
- `part_d.json` (VER 22-24) è marcato `type: "altro"` invece di `listino`: le sue 126 figure
  non entrano nell'import.
- `CAN_030`: la pagina non espone celle vettoriali, 2 figure non ritagliabili.
