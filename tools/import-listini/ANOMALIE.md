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
