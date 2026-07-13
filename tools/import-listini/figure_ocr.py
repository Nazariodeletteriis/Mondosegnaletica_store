#!/usr/bin/env python3
"""
Ritaglia i cartelli dalle pagine di listino e li aggancia alla figura giusta.

Sostituisce crop_figures.py + la lettura a vista dei ritagli (naming2/fig_*.json).

I codici figura e i nomi sono già noti: stanno in extract/*.json, pagina per pagina, in
ordine di lettura. Quello che mancava era sapere QUALE cella è QUALE figura. La pipeline
precedente le appaiava per posizione (n-esima cella = n-esima figura) e sbagliava il 9%
dei casi, perché il rilevatore a volte scambia le intestazioni della tabella prezzi per
cartelli e da lì in poi l'ordine slitta. Un cartello sbagliato su un prodotto omologato
non è un difetto estetico, quindi la posizione non basta.

Qui la didascalia e il testo stampati DENTRO il ritaglio vengono letti da un OCR locale
e usati come giudice: per ogni pagina si risolve un assegnamento ottimo cella↔figura su
due segnali —

  · il codice figura ("FIGURA II 384"), quando la didascalia c'è;
  · il testo del cartello ("RALLENTARE LAVORI IN CORSO"), che è più ricco del codice e
    salva le pagine in cui l'etichetta è una lettera singola, dove il codice da solo
    combacerebbe con qualunque cosa;

più una preferenza debole per la posizione, che decide solo quando l'OCR tace. L'OCR
storpia qualche carattere, ma l'assegnamento regge lo stesso perché decide sull'evidenza
di TUTTA la pagina insieme, non del singolo ritaglio.

Ricaduta importante: l'appaiamento per posizione pretendeva una corrispondenza uno-a-uno,
quindi crop_figures.py scartava per intero ogni pagina in cui il numero di celle non
combaciava esattamente col numero di figure — 14 pagine, 155 figure perse. L'assegnamento
non ha bisogno di quel vincolo: si ritagliano tutte le celle plausibili e le intestazioni
restano semplicemente senza figura assegnata.

Il punteggio è tarato sui 611 ritagli già letti a vista nei lotti 0-5:
alta = 99% corretti · media = 100% · bassa = 58%.  Solo alta e media vanno in produzione;
la bassa viene scritta a parte, per non mettere il cartello sbagliato sul prodotto giusto.

Uso:  python3 figure_ocr.py [FILTRO]      es. FILTRO = "CAN" o "CAN_021"
Serve: pymupdf · pillow · numpy · scipy · rapidocr-onnxruntime
"""
import fitz, json, glob, os, re, sys
from collections import defaultdict
from difflib import SequenceMatcher

import numpy as np
from PIL import Image
from scipy.optimize import linear_sum_assignment
from rapidocr_onnxruntime import RapidOCR

REPO   = os.path.dirname(os.path.abspath(__file__))
SRC    = '/var/www/Mondosegnaletica_store/Prodotti/'
PDFS   = {'VER': 'LISTINO VERTICALE.pdf',
          'ORI': 'LISTINO ORIZZONTALE.pdf',
          'CAN': 'LISTINO SEGNALETICA CANTIERISTICA.pdf',
          'ACC': 'LISTINO ACCESSORI VARI.pdf',
          'GOM': 'LISTINO PRODOTTI IN GOMMA.pdf'}
CROPS  = os.path.join(REPO, 'crops-raw')
NAMING = os.path.join(REPO, 'naming')

# Sotto 0.55 l'OCR indovina più che leggere: su quella fascia il campione di controllo dà
# il 58% di correttezza, cioè due prodotti su cinque prenderebbero il cartello sbagliato.
SOGLIA = 0.55

ocr = RapidOCR()
TR  = str.maketrans('OIL', '011')          # le confusioni tipiche dell'OCR


# ---------------------------------------------------------------- rilevamento celle
def candidate_cells(page):
    """Tutte le celle plausibili sopra la tabella prezzi. Non filtra per conteggio:
    a separare i cartelli dalle intestazioni ci pensa l'assegnamento."""
    rects = [it['rect'] for it in page.get_drawings()]
    H, W = page.rect.height, page.rect.width

    # Le intestazioni della tabella prezzi ("ARTICOLO", "DIMENSIONE") hanno la stessa
    # taglia dei riquadri dei cartelli. Tutto ciò che sta all'altezza della tabella, o
    # sotto, è fuori.
    larghi = [r for r in rects if r.width > 0.60 * W and r.height > 20 and r.y0 > 0.30 * H]
    table_top = min((r.y0 for r in larghi), default=H)

    cand = [r for r in rects
            if 24 < r.width < 340 and 24 < r.height < 340
            and r.get_area() > 1200 and r.y1 <= table_top + 4]

    # bordo e riempimento sono due rettangoli sovrapposti: tieni il più esterno
    cand.sort(key=lambda r: -r.get_area())
    keep = []
    for r in cand:
        if not any(abs(r.x0 - k.x0) < 5 and abs(r.y0 - k.y0) < 5 and
                   abs(r.x1 - k.x1) < 5 and abs(r.y1 - k.y1) < 5 for k in keep):
            keep.append(r)
    # scarta le celle annidate (il cartello disegnato dentro il riquadro)
    keep = [r for r in keep if not any(k is not r and k.contains(r) for k in keep)]
    keep.sort(key=lambda r: (round(r.y0 / 12), r.x0))      # ordine di lettura
    return keep


# ---------------------------------------------------------------- lettura e punteggio
def n_code(s):
    """Codice in forma confrontabile: via il prefisso romano e il rimando all'articolo.
    La didascalia dice "FIGURA II 441/A ART. 148", il listino dice "441/A"."""
    s = str(s or '').upper()
    s = re.sub(r'\bART\w*\.?\s*\d*', ' ', s)
    s = re.sub(r'\b(FIG\w*|GIREV\w*)\.?', ' ', s)
    s = re.sub(r'\bI{1,3}\b', ' ', s)
    s = re.sub(r'\bI{2,3}(?=[0-9])', ' ', s)               # "II384" attaccato
    return re.sub(r'[^A-Z0-9/]', '', s)


def n_txt(s):
    return re.sub(r'[^A-Z0-9]', '', str(s or '').upper())


CAPTION = re.compile(r'FIG\w*\.?\s*([IVX]{0,3}[\s\.]*[0-9]{1,3}\s*(?:/\s*[A-Z0-9]{1,3})?)', re.I)


def leggi(page, rect, cache, chiave):
    """Restituisce (tutto il testo, solo la didascalia in fondo alla cella).

    Tenere separata la didascalia non è un dettaglio: il cartello ha numeri suoi
    disegnati sopra (una portata "6,5 t", un limite "50" barrato) e cercare il codice
    figura in tutto il ritaglio li scambia per codici. FIGURA 60/B finiva agganciata
    alla figura 65 perché sul cartello c'era scritto 6,5 t.
    """
    if chiave in cache:
        return cache[chiave]

    pix = page.get_pixmap(clip=rect, dpi=200)
    im = Image.frombytes('RGB', (pix.width, pix.height), pix.samples)
    scala = 3 if im.width < 400 else 1                      # le didascalie sono minute
    if scala > 1:
        im = im.resize((im.width * scala, im.height * scala), Image.LANCZOS)

    res, _ = ocr(np.array(im))
    H = im.height
    tutto, didascalia = [], []
    for box, testo, _conf in (res or []):
        tutto.append(testo)
        y = sum(p[1] for p in box) / len(box)
        if y > 0.74 * H:                                    # fascia bassa = didascalia
            didascalia.append(testo)

    val = (' '.join(tutto), ' '.join(didascalia))
    cache[chiave] = val
    return val


def sc_codice(blob, cap, codice):
    c = n_code(codice)
    if not c:
        return 0.0

    # La didascalia dichiara una figura: è la prova più forte che esista. Vale anche al
    # contrario — se dichiara un'ALTRA figura, questa cella non è di questa figura, e va
    # esclusa invece di ripescarla con una somiglianza qualsiasi.
    m = CAPTION.search(cap or blob)
    if m:
        letto = n_code(m.group(1))
        if letto:
            if letto == c:
                return 1.0
            if letto.translate(TR) == c.translate(TR):
                return 0.95
            return -1.0                                     # dichiarata un'altra figura

    b = n_code(blob)
    if not b:
        return 0.0
    if len(c) <= 2:
        # Etichetta a lettera singola (pagine dei cartelli da cantiere): compare in testa
        # al ritaglio. Cercarla come sottostringa combacerebbe con qualsiasi testo.
        return 1.0 if b.startswith(c) else 0.0
    if c in b:
        return 1.0
    if c.translate(TR) in b.translate(TR):
        return 0.95
    return SequenceMatcher(None, b.translate(TR), c.translate(TR)).ratio() * 0.7


def sc_nome(blob, nome):
    b, x = n_txt(blob).translate(TR), n_txt(nome).translate(TR)
    if len(x) < 6 or len(b) < 6:
        return 0.0
    r = SequenceMatcher(None, b, x)
    m = r.find_longest_match(0, len(b), 0, len(x))
    return max(r.ratio(), (m.size / min(len(b), len(x))) * 0.95)


# ---------------------------------------------------------------- figure note per pagina
# Si ritaglia da OGNI pagina che dichiari delle figure, non solo da quelle marcate "listino".
#
# part_d.json (pagine VER 22-24) è marcato type "altro" pur avendo 126 figure: sono cartelli
# veri, semplicemente su pagine senza tabella prezzi. Il filtro sul tipo le escludeva, e con
# loro le immagini dei prodotti che quelle figure ritraggono — prodotti che esistono, perché
# nati dalle pagine dove il prezzo c'è (una figura ricorre su più pagine e viene deduplicata).
#
# Qui il tipo della pagina non c'entra: interessa solo se ci sono figure da ritagliare. Chi
# decide se una figura diventa un prodotto è normalize.py, non questo script.
pagine = defaultdict(list)
for f in sorted(glob.glob(os.path.join(REPO, 'extract/*.json'))):
    for pg in json.load(open(f)):
        if not pg.get('figures'):
            continue
        for fg in (pg.get('figures') or []):
            pagine[(pg['tag'], pg['page'])].append(
                {'figura': fg.get('figura'), 'nome': fg.get('nome'), 'pos': fg.get('pos')})

os.makedirs(CROPS, exist_ok=True)
os.makedirs(NAMING, exist_ok=True)

# L'OCR di tutte le celle costa ~9 minuti. La lettura di una cella non cambia mai, mentre
# il punteggio si ritocca spesso: tenerla in cache rende istantanea ogni passata dopo la
# prima, e permette di tarare il punteggio senza rileggere niente.
CACHE = os.path.join(NAMING, 'ocr_cache.json')
cache = json.load(open(CACHE)) if os.path.exists(CACHE) else {}
cache = {k: tuple(v) for k, v in cache.items()}
n_cache0 = len(cache)

solo = sys.argv[1] if len(sys.argv) > 1 else None
voci, senza_celle = [], []
stat = defaultdict(int)

for tag, pdf in PDFS.items():
    pgs = sorted(p for (t, p) in pagine if t == tag)
    if not pgs:
        continue
    doc = fitz.open(SRC + pdf)
    for pnum in pgs:
        if solo and not f"{tag}_{pnum:03d}".startswith(solo):
            continue
        figs = pagine[(tag, pnum)]
        page = doc[pnum - 1]
        cells = candidate_cells(page)
        if not cells:
            senza_celle.append(f"{tag}_{pnum:03d} ({len(figs)} figure)")
            stat['figure_perse'] += len(figs)
            continue

        letture = [leggi(page, r, cache, f"{tag}|{pnum}|{i}|{round(r.x0)},{round(r.y0)},{round(r.x1)},{round(r.y1)}")
                   for i, r in enumerate(cells)]
        blobs = [t for t, _ in letture]

        S = np.zeros((len(cells), len(figs)))       # evidenza OCR
        P = np.zeros((len(cells), len(figs)))       # spareggio: preferisci l'ordine di lettura
        for i, (b, cap) in enumerate(letture):
            for j, fg in enumerate(figs):
                sc = sc_codice(b, cap, fg['figura'])
                # Se la didascalia dichiara un'altra figura, il testo del cartello non
                # deve poter ripescare l'accoppiata: la didascalia è più autorevole.
                S[i, j] = sc if sc < 0 else max(sc, sc_nome(b, fg['nome']))
                P[i, j] = 0.04 if fg['pos'] == i + 1 else 0.0

        ri, ci = linear_sum_assignment(-(S + P))
        scelta = {int(i): int(j) for i, j in zip(ri, ci)}

        for i, rect in enumerate(cells):
            j = scelta.get(i)
            s = float(S[i, j]) if j is not None else 0.0
            fg = figs[j] if j is not None else None

            # Nessuna figura convincente: è un'intestazione di tabella o una cornice.
            if not fg or not fg.get('figura') or s < SOGLIA:
                stat['scartati'] += 1
                continue

            name = f"{tag}_{pnum:03d}_{fg['pos']:02d}.png"
            page.get_pixmap(clip=rect, dpi=200).save(os.path.join(CROPS, name))
            conf = 'alta' if s >= 0.90 else 'media'
            voci.append({'file': name, 'figura': fg['figura'], 'nome': fg['nome'],
                         'confidenza': conf, 'scarto': False,
                         'score_ocr': round(s, 3), 'ocr': blobs[i][:80]})
            stat[conf] += 1
    doc.close()

json.dump(voci, open(os.path.join(NAMING, 'figure_ocr.json'), 'w'), ensure_ascii=False, indent=1)
json.dump(cache, open(CACHE, 'w'), ensure_ascii=False)

print(f"celle lette dall'OCR  : {len(cache) - n_cache0} nuove · {n_cache0} dalla cache")
print(f"ritagli agganciati    : {len(voci)}")
print(f"  confidenza alta     : {stat['alta']}")
print(f"  confidenza media    : {stat['media']}")
print(f"celle scartate        : {stat['scartati']}   (intestazioni, cornici, OCR muto)")
if senza_celle:
    print(f"pagine senza celle    : {len(senza_celle)}  → {stat['figure_perse']} figure senza immagine")
    for x in senza_celle:
        print('   ', x)
print(f"→ {os.path.join(NAMING, 'figure_ocr.json')}")
