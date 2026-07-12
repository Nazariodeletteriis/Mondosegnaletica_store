#!/usr/bin/env python3
"""
Normalizza i JSON estratti dai listini in un modello prodotto/variante unico.

Due schemi convivono nei listini:
  A) La riga della tabella referenzia una figura (rows[].figura) → la RIGA è il prodotto.
     Tipico di ACC/GOM/ORI: hanno codice articolo proprio e prezzo secco.
  B) Le righe NON referenziano figure → il prodotto è la FIGURA (il singolo cartello) e
     le righe della tabella sono i suoi FORMATI. Tipico di VER/CAN: il prezzo nel listino
     è per formato, non per cartello: qualunque segnale in targa 60x90 CL1 costa uguale.
     Da qui il prodotto variabile: dimensione × materiale × classe.
"""
import json, glob, os, re, csv, unicodedata
from collections import defaultdict

EXTRACT = 'extract'
OUT = 'out'
os.makedirs(OUT, exist_ok=True)

# ── categoria di destinazione, per sezione del listino ────────────────────────
# Le 6 sottocategorie già esistenti nello store. La scelta è per SEZIONE, non per PDF:
# es. i delineatori stanno nel listino Verticale ma sono un'altra categoria.
CAT_VERTICALE   = 'Segnaletica Verticale'
CAT_ORIZZONTALE = 'Segnaletica Orizzontale'
CAT_CONI        = 'Coni e Transenne'
CAT_DELIN       = 'Delineatori e Paletti'
CAT_CANTIERE    = 'Cantieristica'
CAT_DISSUASORI  = 'Dissuasori e Accessori'

def categoria(tag, section):
    s = (section or '').upper()
    if 'DELINEATOR' in s and 'CURVA' in s:      return CAT_DELIN
    if 'CONI' in s:                              return CAT_CONI
    if 'TRANSENN' in s or 'BARRIERA ESTENSIBILE' in s or 'NEW JERSEY' in s:
        return CAT_CONI
    if 'DISSUASOR' in s:                         return CAT_DISSUASORI
    if tag == 'VER':                             return CAT_VERTICALE
    if tag == 'ORI':                             return CAT_ORIZZONTALE
    if tag == 'CAN':                             return CAT_CANTIERE
    if tag == 'GOM':                             return CAT_DISSUASORI
    if tag == 'ACC':                             return CAT_DISSUASORI
    return CAT_DISSUASORI

TAGNAME = {'VER':'Verticale','ORI':'Orizzontale','CAN':'Cantieristica',
           'ACC':'Accessori','GOM':'Gomma'}

def slug(s, maxlen=60):
    s = unicodedata.normalize('NFKD', str(s or '')).encode('ascii','ignore').decode()
    s = re.sub(r'[^A-Za-z0-9]+', '-', s).strip('-').lower()
    return s[:maxlen]

def sku_part(s, n=6):
    s = unicodedata.normalize('NFKD', str(s or '')).encode('ascii','ignore').decode()
    return re.sub(r'[^A-Z0-9]', '', s.upper())[:n] or 'X'

def dimensione(row):
    """Etichetta leggibile della dimensione, dal formato della riga."""
    b, h = row.get('base_cm'), row.get('altezza_cm')
    d = (row.get('dimensione') or '').strip()
    if b and h:   base = f'{fmt(b)}x{fmt(h)} cm'
    elif b:       base = f'{fmt(b)} cm'
    elif d:       base = d
    else:         base = None
    if d and base and d.lower() not in base.lower():
        return f'{d} {base}'.strip()
    return base

def fmt(v):
    if isinstance(v, float) and v == int(v): return str(int(v))
    return str(v).replace('.', ',')

# ── raccolta ─────────────────────────────────────────────────────────────────
prodotti = {}   # sku → prodotto
def add_variant(p, attrs, price, note=None):
    key = tuple(sorted(attrs.items()))
    if key in p['_seen']: return
    p['_seen'].add(key)
    p['varianti'].append({'attrs': attrs, 'euro': price, 'note': note})

for f in sorted(glob.glob(f'{EXTRACT}/*.json')):
    base = os.path.basename(f)
    if not re.match(r'^(VER|ORI|CAN|ACC|GOM)_', base):   # salta file parziali/spuri
        continue
    tag = base.split('_')[0]
    try:
        pages = json.load(open(f))
    except json.JSONDecodeError as e:
        print(f'!! {base}: JSON non valido ({e}) — SALTATO'); continue

    for pg in pages:
        if pg.get('type') != 'listino': continue
        rows = pg.get('rows') or []
        figs = pg.get('figures') or []
        sec  = pg.get('section') or ''
        cat  = categoria(tag, sec)
        note_pg = pg.get('page_note')

        rows_ref_fig = any(r.get('figura') for r in rows)

        if rows_ref_fig or not figs:
            # ── SCHEMA A: la riga è il prodotto ──────────────────────────────
            figmap = {str(x.get('figura')): x for x in figs}
            for i, r in enumerate(rows, 1):
                cod  = (r.get('codice') or '').strip()
                art  = (r.get('articolo') or '').strip()
                if not art and not cod: continue
                fig  = figmap.get(str(r.get('figura'))) if r.get('figura') else None
                nome = art or (fig or {}).get('nome') or cod
                sku  = cod or f"MS-{tag}-{sku_part(art)}-{pg['page']:03d}{i:02d}"
                p = prodotti.setdefault(sku, {
                    'sku': sku, 'nome': nome.title(), 'cat': cat, 'listino': TAGNAME[tag],
                    'sezione': sec, 'pagina': pg.get('page_label') or pg['page'],
                    'figura': r.get('figura'), 'desc_note': [], 'varianti': [], '_seen': set(),
                })
                if r.get('note'): p['desc_note'].append(r['note'])
                if note_pg: p['desc_note'].append(note_pg)
                dim = dimensione(r)
                prezzi = [x for x in (r.get('prezzi') or []) if x.get('euro') is not None]
                if not prezzi:
                    add_variant(p, {k:v for k,v in {'Dimensione':dim}.items() if v}, None,
                                r.get('nota_prezzo') or r.get('note'))
                for x in prezzi:
                    a = {}
                    if dim: a['Dimensione'] = dim
                    if x.get('materiale'): a['Materiale'] = x['materiale']
                    if x.get('classe'):    a['Classe rifrangenza'] = x['classe']
                    if x.get('variante'):  a['Variante'] = x['variante']
                    add_variant(p, a, x['euro'], x.get('unita'))
        else:
            # ── SCHEMA B: il prodotto è la figura, le righe sono i formati ───
            for fg in figs:
                nome = (fg.get('nome') or '').strip()
                figc = (fg.get('figura') or '').strip()
                if not nome and not figc: continue
                titolo = nome.capitalize() if nome else f'Segnale fig. {figc}'
                sku = f"MS-{tag}-{sku_part(figc or nome, 8)}"
                if sku in prodotti:   # stessa figura su più pagine → disambigua
                    sku = f"{sku}-{pg['page']:03d}"
                p = prodotti.setdefault(sku, {
                    'sku': sku, 'nome': titolo, 'cat': cat, 'listino': TAGNAME[tag],
                    'sezione': sec, 'pagina': pg.get('page_label') or pg['page'],
                    'figura': figc, 'desc_note': [note_pg] if note_pg else [],
                    'varianti': [], '_seen': set(),
                })
                for r in rows:
                    dim = dimensione(r)
                    prezzi = [x for x in (r.get('prezzi') or []) if x.get('euro') is not None]
                    if not prezzi:
                        add_variant(p, {k:v for k,v in {'Dimensione':dim}.items() if v},
                                    None, r.get('nota_prezzo') or r.get('note'))
                    for x in prezzi:
                        a = {}
                        if dim: a['Dimensione'] = dim
                        if x.get('materiale'): a['Materiale'] = x['materiale']
                        if x.get('classe'):    a['Classe rifrangenza'] = x['classe']
                        if x.get('variante'):  a['Variante'] = x['variante']
                        add_variant(p, a, x['euro'], x.get('unita'))

# ── report ───────────────────────────────────────────────────────────────────
tot_var = sum(len(p['varianti']) for p in prodotti.values())
con_prezzo = [p for p in prodotti.values() if any(v['euro'] is not None for v in p['varianti'])]
senza      = [p for p in prodotti.values() if not any(v['euro'] is not None for v in p['varianti'])]
variabili  = [p for p in con_prezzo if len([v for v in p['varianti'] if v['euro'] is not None]) > 1]
semplici   = [p for p in con_prezzo if len([v for v in p['varianti'] if v['euro'] is not None]) == 1]

print(f"PRODOTTI            {len(prodotti)}")
print(f"  con prezzo        {len(con_prezzo)}   (variabili {len(variabili)} · semplici {len(semplici)})")
print(f"  SENZA prezzo      {len(senza)}   → pubblicati con CTA preventivo")
print(f"VARIANTI totali     {tot_var}")
print()
print("Per categoria:")
bycat = defaultdict(lambda: [0,0])
for p in prodotti.values():
    bycat[p['cat']][0] += 1
    bycat[p['cat']][1] += len(p['varianti'])
for c,(n,v) in sorted(bycat.items(), key=lambda x:-x[1][0]):
    print(f"  {c:26s} {n:5d} prodotti  {v:6d} varianti")

json.dump({k:{kk:vv for kk,vv in p.items() if kk!='_seen'} for k,p in prodotti.items()},
          open(f'{OUT}/prodotti.json','w'), ensure_ascii=False, indent=1)
print(f"\n→ {OUT}/prodotti.json")
