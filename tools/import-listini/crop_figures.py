#!/usr/bin/env python3
"""
Ritaglia le figure (i singoli cartelli) dalle pagine di listino.

Le celle della griglia sono rettangoli vettoriali. Il problema è distinguerle dai
rettangoli della tabella prezzi e dalle cornici decorative. Vincolo che risolve
l'ambiguità: gli agenti hanno già contato quante figure ci sono su ogni pagina.
Quindi cerco il sottoinsieme di celle, raggruppate per dimensione, il cui totale
combacia con quel numero. Se non combacia, la pagina viene segnalata e NON ritagliata:
meglio nessuna immagine che l'immagine sbagliata sul prodotto sbagliato.
"""
import fitz, json, glob, os, itertools
from collections import defaultdict

SRC = '/var/www/Mondosegnaletica_store/Prodotti/'
PDFS = {'VER':'LISTINO VERTICALE.pdf','ORI':'LISTINO ORIZZONTALE.pdf',
        'CAN':'LISTINO SEGNALETICA CANTIERISTICA.pdf','ACC':'LISTINO ACCESSORI VARI.pdf',
        'GOM':'LISTINO PRODOTTI IN GOMMA.pdf'}
OUT = 'figures'
os.makedirs(OUT, exist_ok=True)

def candidate_cells(page):
    rects = [it['rect'] for it in page.get_drawings()]

    # Le celle di INTESTAZIONE della tabella prezzi ("ARTICOLO", "DIMENSIONE") hanno la
    # stessa taglia dei riquadri dei cartelli e venivano scambiate per figure: il conteggio
    # tornava lo stesso, ma l'allineamento figura↔immagine slittava. Tutto ciò che sta
    # all'altezza della tabella prezzi, o sotto, va escluso.
    H, W = page.rect.height, page.rect.width
    larghi = [r for r in rects if r.width > 0.60 * W and r.height > 20 and r.y0 > 0.30 * H]
    table_top = min((r.y0 for r in larghi), default=H)

    cand = [r for r in rects
            if 35 < r.width < 280 and 35 < r.height < 280 and r.get_area() > 2000
            and r.y1 <= table_top + 2]
    # bordo + riempimento coincidono: tieni il rettangolo più esterno
    cand.sort(key=lambda r: -r.get_area())
    keep = []
    for r in cand:
        if not any(abs(r.x0-k.x0) < 5 and abs(r.y0-k.y0) < 5 and
                   abs(r.x1-k.x1) < 5 and abs(r.y1-k.y1) < 5 for k in keep):
            keep.append(r)
    # scarta celle annidate dentro un'altra cella (il cartello dentro il riquadro)
    return [r for r in keep if not any(k is not r and k.contains(r) for k in keep)]

def _sorted(rects):
    rects.sort(key=lambda r: (round(r.y0 / 12), r.x0))   # ordine di lettura
    return rects

def pick_grid(cells, n):
    """Sottoinsieme di celle, per gruppi di dimensione, che somma esattamente a n."""
    if len(cells) == n:
        return _sorted(list(cells))

    groups = defaultdict(list)
    for r in cells:
        groups[(round(r.width/3), round(r.height/3))].append(r)   # tolleranza ~3pt
    gs = sorted(groups.values(), key=len, reverse=True)
    for k in range(1, min(6, len(gs)) + 1):
        for combo in itertools.combinations(gs, k):
            if sum(len(g) for g in combo) == n:
                return _sorted([r for g in combo for r in g])
    return None

report = {'ok': 0, 'ko': 0, 'pagine_ko': [], 'ritagli': 0}
mapping = {}   # "TAG_pagina_pos" → file

for f in sorted(glob.glob('extract/*.json')):
    tag = os.path.basename(f).split('_')[0]
    if tag not in PDFS: continue
    doc = fitz.open(SRC + PDFS[tag])
    for pg in json.load(open(f)):
        figs = pg.get('figures') or []
        if pg.get('type') != 'listino' or not figs: continue
        page = doc[pg['page'] - 1]
        cells = candidate_cells(page)
        grid = pick_grid(cells, len(figs))
        if not grid:
            report['ko'] += 1
            report['pagine_ko'].append(f"{tag} p{pg['page']} (figure={len(figs)}, celle={len(cells)})")
            continue
        report['ok'] += 1
        for fg, rect in zip(figs, grid):
            name = f"{tag}_{pg['page']:03d}_{fg.get('pos', 0):02d}.png"
            page.get_pixmap(clip=rect, dpi=200).save(f'{OUT}/{name}')
            mapping[f"{tag}|{pg['page']}|{fg.get('pos')}"] = {
                'file': name, 'figura': fg.get('figura'), 'nome': fg.get('nome'),
                'sezione': pg.get('section'),
            }
            report['ritagli'] += 1
    doc.close()

json.dump(mapping, open(f'{OUT}/mapping.json', 'w'), ensure_ascii=False, indent=1)
print(f"pagine ritagliate OK : {report['ok']}")
print(f"pagine NON risolte   : {report['ko']}")
print(f"ritagli prodotti     : {report['ritagli']}")
if report['pagine_ko']:
    print('\nda rivedere:')
    for x in report['pagine_ko']: print('  ', x)
