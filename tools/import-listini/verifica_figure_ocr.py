#!/usr/bin/env python3
"""
Mette alla prova figure_ocr.py contro le 611 letture a vista dei lotti 0-5.

Quelle letture sono costate crediti e restano l'unico metro di giudizio indipendente
che abbiamo: qui servono da banco di prova per dire se l'aggancio automatico è
affidabile abbastanza da mandare le immagini in produzione.

Il confronto NON può essere fatto per nome file: la vecchia pipeline nominava il ritaglio
con l'indice della cella, la nuova con la posizione della figura assegnata — stesso nome,
significato diverso. Vecchi e nuovi ritagli sono però render dello stesso rettangolo alla
stessa risoluzione, quindi la stessa cella dà gli stessi byte: si appaiano per hash del
contenuto.

Uso: python3 verifica_figure_ocr.py
Serve: crops-raw.old/ (i ritagli della vecchia pipeline) e naming2/fig_00..05.json
"""
import json, glob, os, re, hashlib, sys
from collections import defaultdict

REPO = os.path.dirname(os.path.abspath(__file__))
OLD  = os.path.join(REPO, 'crops-raw.old')
NEW  = os.path.join(REPO, 'crops-raw')

if not os.path.isdir(OLD):
    sys.exit(f"manca {OLD}: senza i ritagli della vecchia pipeline non c'è banco di prova")


def sha(p):
    return hashlib.sha1(open(p, 'rb').read()).hexdigest()


def n_code(s):
    s = str(s or '').upper()
    s = re.sub(r'\bART\w*\.?\s*\d*', ' ', s)
    s = re.sub(r'\b(FIG\w*|GIREV\w*)\.?', ' ', s)
    s = re.sub(r'\bI{1,3}\b', ' ', s)
    s = re.sub(r'\bI{2,3}(?=[0-9])', ' ', s)
    return re.sub(r'[^A-Z0-9/]', '', s)


vista = {x['file']: x for x in sum(
    [json.load(open(f)) for f in sorted(glob.glob(os.path.join(REPO, 'naming2/fig_*.json')))], [])}
nuovo = {x['file']: x for x in json.load(open(os.path.join(REPO, 'naming/figure_ocr.json')))}

h_old = {}
for p in glob.glob(f'{OLD}/*.png'):
    h_old.setdefault(sha(p), os.path.basename(p))
h_new = {}
for p in glob.glob(f'{NEW}/*.png'):
    fn = os.path.basename(p)
    if fn in nuovo:
        h_new.setdefault(sha(p), nuovo[fn])

comuni = set(h_old) & set(h_new)
print(f"ritagli vecchi {len(h_old)} · nuovi {len(h_new)} · stessa cella (stessi byte): {len(comuni)}")

ok = ko = 0
per = defaultdict(lambda: [0, 0])
errori = []
for h in comuni:
    g = vista.get(h_old[h])
    x = h_new[h]
    if not g or g.get('scarto') or not (g.get('figura') or '').strip():
        continue
    giusto = n_code(g['figura']) == n_code(x['figura'])
    ok += giusto
    ko += not giusto
    per[x['confidenza']][0 if giusto else 1] += 1
    if not giusto:
        errori.append((h_old[h], g['figura'], x['figura'], x['confidenza'], x['score_ocr']))

tot = ok + ko
if not tot:
    sys.exit("nessuna cella confrontabile")

print(f"\n=== figure_ocr.py contro le letture a vista ===")
print(f"celle confrontabili : {tot}")
print(f"  ACCORDO           : {ok}  ({ok / tot * 100:.1f}%)      [aggancio per posizione: 91.0%]")
print(f"  DISACCORDO        : {ko}")
for c in ('alta', 'media'):
    a, b = per[c]
    t = a + b
    if t:
        print(f"  confidenza {c:5}: {a}/{t} corretti ({a / t * 100:.1f}%)")

if errori:
    print(f"\nerrori residui ({len(errori)}):")
    for e in sorted(errori, key=lambda e: -e[4]):
        print(f"   {e[0]:18} vista={e[1]!r:12} ocr={e[2]!r:12} {e[3]:6} score={e[4]}")
