#!/usr/bin/env python3
"""
Normalizza i JSON estratti dai listini nel modello prodotto/variante di WooCommerce.

Due schemi convivono nei listini:
  A) La riga della tabella referenzia una figura (rows[].figura) → la RIGA è il prodotto.
     Tipico di ACC/GOM/ORI: hanno un codice articolo proprio e prezzo secco.
  B) Le righe NON referenziano figure → il prodotto è la FIGURA (il singolo cartello) e
     le righe sono i suoi FORMATI. Tipico di VER/CAN: nel listino il prezzo è per formato,
     non per cartello — qualunque segnale in targa 60x90 CL1 costa uguale. Da qui il
     prodotto variabile: Dimensione × Materiale × Classe rifrangenza.

Una stessa figura compare su più pagine: viene deduplicata in un solo prodotto,
unendo i formati di tutte le pagine in cui appare.
"""
import json, glob, os, re, unicodedata
from collections import defaultdict

OUT = '/var/www/Mondosegnaletica_store/tools/import-listini/out'
os.makedirs(OUT, exist_ok=True)

# ── categoria di destinazione, decisa per SEZIONE, non per PDF ────────────────
# (i delineatori stanno nel listino Verticale ma sono un'altra categoria)
def categoria(tag, section):
    s = (section or '').upper()
    if 'DELINEATOR' in s and 'CURVA' in s:                            return 'Delineatori e Paletti'
    if 'CONI' in s:                                                    return 'Coni e Transenne'
    if 'TRANSENN' in s or 'BARRIERA ESTENSIBILE' in s or 'NEW JERSEY' in s:
        return 'Coni e Transenne'
    if 'DISSUASOR' in s:                                               return 'Dissuasori e Accessori'
    return {'VER':'Segnaletica Verticale', 'ORI':'Segnaletica Orizzontale',
            'CAN':'Cantieristica', 'GOM':'Dissuasori e Accessori',
            'ACC':'Dissuasori e Accessori'}.get(tag, 'Dissuasori e Accessori')

TAGNAME = {'VER':'Verticale','ORI':'Orizzontale','CAN':'Cantieristica',
           'ACC':'Accessori','GOM':'Gomma'}

MINUSCOLE = {'di','da','del','della','dello','dei','delle','e','ed','per','in','con',
             'a','al','alla','ai','alle','su','sul','sulla','the','o','od','a.','s.'}

def nice(s):
    """ALL CAPS del listino → italiano leggibile, senza storpiare unità e sigle."""
    s = re.sub(r'\s+', ' ', str(s or '').strip())
    if not s: return ''
    # isupper() è troppo rigido: "STAFFA ... 70x70" contiene una minuscola dentro una
    # misura e verrebbe lasciata tutta maiuscola. Conta invece le lettere.
    lettere = [c for c in s if c.isalpha()]
    if lettere and sum(c.isupper() for c in lettere) / len(lettere) < 0.8:
        return s[0].upper() + s[1:]
    out = []
    for i, w in enumerate(s.lower().split(' ')):
        if re.search(r'\d', w) or len(w) <= 2 and w not in MINUSCOLE:
            out.append(w.upper() if len(w) <= 3 and not re.search(r'\d', w) else w)
        elif i > 0 and w in MINUSCOLE:
            out.append(w)
        else:
            out.append(w[0].upper() + w[1:])
    r = ' '.join(out)
    r = re.sub(r'/([a-z])\b', lambda m: '/' + m.group(1).upper(), r)   # "6/h" → "6/H"
    return r[0].upper() + r[1:]

SEZ_SINGOLARE = {
    'SEGNALI DI PERICOLO': 'Segnale di pericolo',
    'SEGNALI DI DIVIETO': 'Segnale di divieto',
    'SEGNALI DI OBBLIGO': 'Segnale di obbligo',
    'SEGNALI DI PRECEDENZA': 'Segnale di precedenza',
    'SEGNALI DI PRECEDENZA E DI PERICOLO': 'Segnale di precedenza',
    'SEGNALI DI DIREZIONE': 'Segnale di direzione',
    'SEGNALI DI DIREZIONE URBANA': 'Segnale di direzione urbana',
    'SEGNALI DI INDICAZIONE': 'Segnale di indicazione',
    'SEGNALI INDICAZIONI SERVIZI': 'Segnale di indicazione servizi',
    'SEGNALI UTILI PER LA GUIDA': 'Segnale utile per la guida',
    'SEGNALI DI PREAVVISO': 'Segnale di preavviso',
    'SEGNALI COMPLEMENTARI': 'Segnale complementare',
    'PANNELLI INTEGRATIVI': 'Pannello integrativo',
    'SEGNALETICA LUMINOSA': 'Segnalatore luminoso',
    'SEGNALETICA ALBERGHIERA': 'Segnale alberghiero',
    'SEGNALETICA FERROVIARIA': 'Segnale ferroviario',
    'SEGNALETICA TEMPORANEA': 'Segnale temporaneo da cantiere',
}

def sezione_singolare(sec):
    """Etichetta al singolare per i cartelli che il listino non descrive.
    Il listino stampa solo "FIGURA 1": senza un nome il prodotto è incercabile."""
    s = (sec or '').split('/')[0].split('+')[0].strip().rstrip('-').strip()
    if s.upper() in SEZ_SINGOLARE:
        return SEZ_SINGOLARE[s.upper()]
    for k, v in SEZ_SINGOLARE.items():
        if s.upper().startswith(k):
            return v
    # fallback: prima parola al singolare, resto minuscolo
    t = nice(s)
    parole = t.split(' ')
    parole[0] = re.sub(r'i$', 'e', parole[0]) if parole[0].lower().startswith('segnali') else parole[0]
    return ' '.join([parole[0]] + [w if re.search(r'[0-9A-Z]{2}', w) else w.lower() for w in parole[1:]])

def sku_part(s, n=10):
    s = unicodedata.normalize('NFKD', str(s or '')).encode('ascii','ignore').decode()
    return re.sub(r'[^A-Z0-9]', '', s.upper())[:n] or 'X'

def fmt(v):
    if isinstance(v, float) and v == int(v): return str(int(v))
    return str(v).replace('.', ',')

def dimensione(row, con_articolo=False):
    b, h = row.get('base_cm'), row.get('altezza_cm')
    lato, dia = row.get('lato_cm'), row.get('diametro_cm')
    d = nice(row.get('dimensione') or '')
    if dia:        misura = f'Ø {fmt(dia)} cm'
    elif lato:     misura = f'Lato {fmt(lato)} cm'
    elif b and h:  misura = f'{fmt(b)}x{fmt(h)} cm'
    elif b:        misura = f'{fmt(b)} cm'
    else:          misura = None
    parti = [nice(row.get('articolo'))] if con_articolo and row.get('articolo') else []
    parti += [x for x in (d, misura) if x]
    return ' '.join(dict.fromkeys(parti)) or None

def fissaggio(row):
    """Nel listino due righe possono avere la STESSA dimensione e prezzi diversi:
    cambia il numero di attacchi o di rinforzi. Senza questo attributo le due
    varianti collassano e uno dei due prezzi viene perso."""
    att  = row.get('n_att')
    rinf = row.get('n_rinf_norm_cors') or row.get('n_rinf_norm')
    trav = row.get('n_rinf_trav_cors') or row.get('n_rinf_trav')
    p = []
    if att:  p.append(f'{att} attacchi')
    if rinf: p.append(f'{rinf} rinforzi')
    if trav: p.append(f'{trav} rinforzi trasversali')
    return ' + '.join(p) or None

# Nomi e immagini dai ritagli sono SOSPESI in questo import.
#
# Il ritaglio viene agganciato alla figura per posizione (n-esima cella = n-esima figura),
# e quell'aggancio si è rivelato inaffidabile: su alcune pagine il rilevatore prende celle
# di intestazione della tabella come se fossero cartelli. Il conteggio torna lo stesso, ma
# l'ordine slitta — verificato: il ritaglio dato per "FIG. 42" stampa "FIGURA 45".
# Un'immagine o un nome sul prodotto sbagliato è peggio di nessuna immagine.
#
# I codici figura letti dalla TABELLA restano affidabili (e sono la chiave degli SKU):
# il passo successivo è leggere la didascalia "FIGURA xx" dentro ogni ritaglio e agganciare
# immagine e nome per codice, non per posizione. Poi è un semplice update per SKU.
nomi_vista = {}
crops = {}

# ── raccolta ──────────────────────────────────────────────────────────────────
prodotti = {}

def get(sku, **kw):
    if sku not in prodotti:
        prodotti[sku] = {'sku': sku, 'varianti': [], 'desc_note': [], 'pagine': [],
                         '_seen': set(), **kw}
    return prodotti[sku]

SCARTI = []   # varianti collassate su una chiave già vista: se hanno un prezzo DIVERSO
              # significa che il modello non le distingue e un prezzo andrebbe perso.

def add_var(p, attrs, euro, nota=None):
    attrs = {k: v for k, v in attrs.items() if v}
    key = tuple(sorted(attrs.items()))
    if key in p['_seen']:
        prec = next((v for v in p['varianti'] if tuple(sorted(v['attrs'].items())) == key), None)
        if prec and euro is not None and prec['euro'] != euro:
            SCARTI.append({'sku': p['sku'], 'attrs': attrs,
                           'tenuto': prec['euro'], 'scartato': euro})
        return
    p['_seen'].add(key)
    p['varianti'].append({'attrs': attrs, 'euro': euro, 'nota': nota})

def prezzi_riga(r):
    """Prezzi utilizzabili della riga.

    Se una riga ha PIÙ prezzi ma le colonne non hanno intestazione (materiale e classe
    assenti), non sappiamo cosa distingua i valori: sulle pagine difettose della
    Cantieristica il PDF stampa 3 cifre sotto colonne senza titolo. Pubblicarne una a
    caso, o etichettarle a fantasia, sarebbe inventare dati: la riga va a preventivo.
    """
    pr = [x for x in (r.get('prezzi') or []) if x.get('euro') is not None]
    if len(pr) > 1 and all(not x.get('materiale') and not x.get('classe') and not x.get('variante') for x in pr):
        return []
    return pr

def disambigua(rows, con_articolo):
    """Righe della stessa tabella che collassano sulla stessa chiave (dimensione +
    fissaggio) ma hanno prezzi diversi: il dato che le distingue esiste, ma il listino
    lo stampa nella nota libera invece che in una colonna (es. "per targa 40x40 —
    adesivo 7x7"). Senza recuperarlo, una delle due varianti — e il suo prezzo —
    sparisce."""
    g = defaultdict(list)
    for r in rows:
        g[(str(r.get('table_title')), dimensione(r, con_articolo), fissaggio(r))].append(r)
    for gruppo in g.values():
        if len(gruppo) < 2:
            continue
        for i, r in enumerate(gruppo, 1):
            nota = (r.get('note') or '').strip()
            r['_versione'] = nice(nota[:70]) if nota else f'Versione {i}'

def attrs_da(r, x, con_articolo=False):
    a = {'Dimensione': dimensione(r, con_articolo), 'Fissaggio': fissaggio(r),
         'Versione': r.get('_versione')}
    if x:
        if x.get('materiale'): a['Materiale'] = nice(x['materiale'])
        if x.get('classe'):    a['Classe rifrangenza'] = str(x['classe']).replace('CL', 'CL ').replace('  ', ' ').strip()
        if x.get('variante'):  a['Variante'] = nice(x['variante'])
    return a

for f in sorted(glob.glob('extract/*.json')):
    base = os.path.basename(f)
    if not re.match(r'^(VER|ORI|CAN|ACC|GOM)_', base): continue
    tag = base.split('_')[0]
    for pg in json.load(open(f)):
        if pg.get('type') != 'listino': continue
        rows, figs = pg.get('rows') or [], pg.get('figures') or []
        sec, cat = pg.get('section') or '', categoria(tag, pg.get('section'))
        note_pg, page = pg.get('page_note'), pg['page']

        schema_A = any(r.get('figura') for r in rows) or not figs
        disambigua(rows, con_articolo=not schema_A)

        if schema_A:
            # ── SCHEMA A: la riga è il prodotto ──────────────────────────────
            figmap = {str(x.get('figura')): x for x in figs}
            for i, r in enumerate(rows, 1):
                cod, art = (r.get('codice') or '').strip(), (r.get('articolo') or '').strip()
                if not art and not cod: continue
                fg   = figmap.get(str(r.get('figura'))) if r.get('figura') else None
                nome = nice(art) or nice((fg or {}).get('nome')) or cod
                sku  = cod or f'MS-{tag}-{sku_part(art)}-{page:03d}{i:02d}'
                p = get(sku, nome=nome, cat=cat, listino=TAGNAME[tag], sezione=sec,
                        figura=r.get('figura'), immagine=crops.get((tag, page, (fg or {}).get('pos'))))
                for n in (r.get('note'), note_pg):
                    if n: p['desc_note'].append(n)
                if page not in p['pagine']: p['pagine'].append(pg.get('page_label') or page)
                pr = prezzi_riga(r)
                if not pr:
                    add_var(p, attrs_da(r, None), None, r.get('nota_prezzo') or r.get('note'))
                for x in pr:
                    add_var(p, attrs_da(r, x), x['euro'], x.get('unita'))
        else:
            # ── SCHEMA B: il prodotto è la figura, le righe sono i formati ───
            for fg in figs:
                figc = str(fg.get('figura') or '').strip()
                crop = crops.get((tag, page, fg.get('pos')))
                vista = nomi_vista.get(crop) if crop else None

                nome = nice(fg.get('nome') or '')
                if not nome and vista:  nome = vista['nome']
                if not nome and figc:
                    etich = sezione_singolare(sec)
                    nome = (f'{etich} — FIG. {figc}' if re.match(r'^[0-9]', figc)
                            else f'{etich} {nice(figc)}')
                if not nome:            continue

                # dedup: stessa figura su più pagine dello stesso listino = un prodotto
                sku = f'MS-{tag}-{sku_part(figc or nome, 10)}'
                p = get(sku, nome=nome, cat=cat, listino=TAGNAME[tag], sezione=sec,
                        figura=figc, immagine=crop)
                if crop and not p.get('immagine'): p['immagine'] = crop
                if vista and vista.get('confidenza') in ('media', 'bassa'):
                    p['nome_da_verificare'] = vista.get('confidenza')
                if note_pg: p['desc_note'].append(note_pg)
                lbl = pg.get('page_label') or page
                if lbl not in p['pagine']: p['pagine'].append(lbl)

                for r in rows:
                    pr = prezzi_riga(r)
                    if not pr:
                        add_var(p, attrs_da(r, None, True), None, r.get('nota_prezzo') or r.get('note'))
                    for x in pr:
                        add_var(p, attrs_da(r, x, True), x['euro'], x.get('unita'))

# ── report ────────────────────────────────────────────────────────────────────
for p in prodotti.values():
    p['desc_note'] = list(dict.fromkeys(p['desc_note']))
    p.pop('_seen', None)

con   = [p for p in prodotti.values() if any(v['euro'] is not None for v in p['varianti'])]
senza = [p for p in prodotti.values() if not any(v['euro'] is not None for v in p['varianti'])]
var   = [p for p in con if len([v for v in p['varianti'] if v['euro'] is not None]) > 1]
sem   = [p for p in con if len([v for v in p['varianti'] if v['euro'] is not None]) == 1]
img   = [p for p in prodotti.values() if p.get('immagine')]
dubbi = [p for p in prodotti.values() if p.get('nome_da_verificare')]

print(f'PRODOTTI          {len(prodotti)}')
print(f'  variabili       {len(var)}')
print(f'  semplici        {len(sem)}')
print(f'  senza prezzo    {len(senza)}  → CTA preventivo')
print(f'VARIAZIONI        {sum(len([v for v in p["varianti"] if v["euro"] is not None]) for p in var)}')
print(f'con immagine      {len(img)} ({100*len(img)//max(len(prodotti),1)}%)')
print(f'nome da verificare{len(dubbi):5d}')
print()
bycat = defaultdict(int)
for p in prodotti.values(): bycat[p['cat']] += 1
for c, n in sorted(bycat.items(), key=lambda x: -x[1]):
    print(f'  {c:26s} {n:5d}')

# I pochi prodotti su cui resta un conflitto di prezzo vengono marcati: verranno
# pubblicati SENZA prezzo (preventivo) invece di mostrarne uno scelto a caso.
for x in SCARTI:
    p = prodotti.get(x['sku'])
    if p:
        p['prezzo_conflitto'] = True

conflitto = [p for p in prodotti.values() if p.get('prezzo_conflitto')]
for p in conflitto:
    for v in p['varianti']:
        v['euro'] = None

json.dump([{'sku': p['sku'], 'nome': p['nome'], 'cat': p['cat'], 'pagine': p['pagine']}
           for p in conflitto], open(f'{OUT}/prezzo_conflitto.json', 'w'), ensure_ascii=False, indent=1)
print(f'prodotti con prezzo in CONFLITTO (→ preventivo): {len(conflitto)}')

json.dump(prodotti, open(f'{OUT}/prodotti.json', 'w'), ensure_ascii=False, indent=1)
print()
if SCARTI:
    print(f'!! PREZZI PERSI (varianti indistinguibili): {len(SCARTI)}')
    for x in SCARTI[:5]: print('   ', x['sku'], x['attrs'], f"tenuto {x['tenuto']} scartato {x['scartato']}")
else:
    print('✓ nessun prezzo perso: ogni variante ha una chiave attributi univoca')
print(f'\n→ {OUT}/prodotti.json')
