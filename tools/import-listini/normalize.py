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
from difflib import SequenceMatcher

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

# Immagini: riabilitate, ora che l'aggancio è affidabile.
#
# Erano state sospese perché il ritaglio veniva appaiato alla figura per posizione (n-esima
# cella = n-esima figura) e su alcune pagine il rilevatore scambiava le intestazioni della
# tabella per cartelli: il conteggio tornava, ma l'ordine slittava, e il ritaglio dato per
# "FIG. 42" stampava "FIGURA 45". Un'immagine sul prodotto sbagliato è peggio di nessuna
# immagine, tanto più su merce omologata.
#
# figure_ocr.py adesso legge la didascalia dentro ogni ritaglio con un OCR locale e risolve
# l'appaiamento cella↔figura come assegnamento ottimo sull'intera pagina. Misurato contro le
# 611 letture a vista dei lotti 0-5: 99.4% corretto (l'appaiamento per posizione: 91.0%).
#
# L'aggancio ritaglio→prodotto resta comunque per (listino, PAGINA, posizione), mai per
# codice figura globale: in sezioni come ACCESSORI la "figura" è una lettera che vale solo
# dentro la sua pagina — la 'A' di pagina 3 è un segnale di velocità, la 'A' di pagina 6 è
# una tuta da lavoro. Agganciare per lettera metterebbe il cartello addosso alla tuta.
def elenco_figure(code):
    """I codici figura a cui questa scritta si riferisce.

    Nel listino la colonna FIG. a volte contiene un ELENCO, non un codice: la figura è
    battezzata "E - E1" mentre le righe la cercano come "E" e come "E1"; una riga dice
    "466 / 467" e le figure sono due, "466" e "467". Confrontando le scritte tali e quali,
    129 righe non trovavano la propria figura e restavano senza immagine.

    Il trabocchetto è che la barra compare anche DENTRO codici singoli — 1/A, 60/B, 309/P —
    e spezzarli significherebbe agganciare l'immagine della figura 1 al prodotto 1/A. Quindi
    si spezza solo quando la scritta è davvero un elenco: separatori spaziati ("E - E1",
    "466 / 467"), oppure pezzi che sono tutti etichette a lettera (A-A1, I-J-K-L), oppure
    tutti numeri di figura. Un misto numero+lettera (1/A) resta un codice solo.
    """
    s = str(code or '').strip()
    if not s or s in ('/', '-'):
        return []

    pezzi = [p for p in re.split(r'\s*[-/]\s*', s) if p]
    if len(pezzi) < 2:
        return [s]

    spaziato = bool(re.search(r'\s[-/]|[-/]\s', s))
    lettere  = all(re.fullmatch(r'[A-Za-z]\d?', p) for p in pezzi)
    numeri   = all(re.fullmatch(r'\d{1,3}', p) for p in pezzi)

    if spaziato or lettere or numeri:
        return [s] + pezzi        # prima la scritta intera, poi i singoli
    return [s]


def scegli_figura(figmap, code, articolo):
    """La figura a cui appartiene questa riga, quando il codice da solo non basta.

    Su alcune pagine due cartelli diversi portano lo STESSO codice — su CAN pag. 32 una
    transenna e un New Jersey sono battezzati entrambi "A" — e tutte le righe della pagina
    li citano con quel codice. Scegliere la prima figura e via vuol dire mettere la foto
    della transenna sul New Jersey.

    Il codice non distingue, ma il nome sì: la figura ha una didascalia e la riga ha il suo
    articolo, e si somigliano. Quando i candidati sono più d'uno vince quello che somiglia
    di più all'articolo; se nessuno somiglia abbastanza non si aggancia niente, perché una
    foto sbagliata su merce omologata è peggio di nessuna foto.
    """
    cands = next((figmap[k] for k in elenco_figure(code) if figmap.get(k)), None)
    if not cands:
        return None
    if len(cands) == 1:
        return cands[0]

    a = re.sub(r'[^a-z0-9]', '', (articolo or '').lower())
    if not a:
        return None

    def somiglia(fg):
        n = re.sub(r'[^a-z0-9]', '', str(fg.get('nome') or '').lower())
        return SequenceMatcher(None, a, n).ratio() if n else 0.0

    migliore = max(cands, key=somiglia)
    return migliore if somiglia(migliore) >= 0.35 else None


def _carica_ritagli():
    f = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'naming/figure_ocr.json')
    if not os.path.exists(f):
        print('!! naming/figure_ocr.json assente: import SENZA immagini (lancia figure_ocr.py)')
        return {}, {}
    crops, nomi = {}, {}
    for x in json.load(open(f)):
        m = re.match(r'([A-Z]{3})_(\d{3})_(\d{2})\.png$', x['file'])
        if not m:
            continue
        crops[(m.group(1), int(m.group(2)), int(m.group(3)))] = x['file']
        nomi[x['file']] = {'nome': x.get('nome'), 'confidenza': x.get('confidenza')}
    return crops, nomi


crops, nomi_vista = _carica_ritagli()

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
    # Il listino di appartenenza si legge dal campo `tag` DENTRO la pagina, non dal nome
    # del file. Cinque file di estrazione si chiamano part_a…part_e (sono le pagine VER
    # 15-25, spezzate durante l'estrazione): filtrando per nome file finivano fuori, e con
    # loro 185 prodotti e ~9.000 varianti. Il prodotti.json committato li contiene, quindi
    # è stato generato prima che quel filtro entrasse: lo script non ricostruiva più i
    # propri dati.
    for pg in json.load(open(f)):
        if pg.get('type') != 'listino': continue
        tag = pg.get('tag')
        if tag not in TAGNAME: continue
        rows, figs = pg.get('rows') or [], pg.get('figures') or []
        sec, cat = pg.get('section') or '', categoria(tag, pg.get('section'))
        note_pg, page = pg.get('page_note'), pg['page']

        schema_A = any(r.get('figura') for r in rows) or not figs
        disambigua(rows, con_articolo=not schema_A)

        if schema_A:
            # ── SCHEMA A: la riga è il prodotto ──────────────────────────────
            # Prima i codici esatti, poi le espansioni degli elenchi: se una pagina ha sia la
            # figura "A-A1" sia una figura "A", la "A" esatta deve restare la "A".
            #
            # Un codice può indicare PIÙ figure: su CAN pag. 32 due cartelli diversi — una
            # transenna e un New Jersey — sono battezzati tutti e due "A". Tenere solo la
            # prima significa dare la transenna anche al New Jersey. Qui il codice porta a
            # una LISTA, e a scegliere dentro la lista è il nome (sotto).
            figmap = defaultdict(list)
            for x in figs:
                k = str(x.get('figura') or '').strip()
                if k: figmap[k].append(x)
            for x in figs:
                for k in elenco_figure(x.get('figura'))[1:]:
                    if x not in figmap[k]: figmap[k].append(x)
            for i, r in enumerate(rows, 1):
                cod, art = (r.get('codice') or '').strip(), (r.get('articolo') or '').strip()
                if not art and not cod: continue
                fg   = scegli_figura(figmap, r.get('figura'), art)
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

NOME_MAX = 90

def nome_breve(nome):
    """Un titolo che si possa leggere, senza perdere ciò che distingue il prodotto.

    Nel listino la colonna ARTICOLO è una riga tecnica, non un nome: "Targa Monofacciale in
    Lamiera di Alluminio - Spessore 25/10 - Colore Fondo: "rosso Traffico" RAL 3020 (non
    Rifrangente) - ... - Staffa di Ancoraggio (esclusa)" — 338 caratteri, e finiva tutta
    dentro l'H1 della scheda prodotto.

    Tagliare al primo trattino non basta: 67 prodotti su 175 si ritroverebbero con lo stesso
    identico nome, perché a distinguerli è quello che viene DOPO (il peso della base, la
    classe della fascia rifrangente). Quindi si accumulano segmenti finché il nome resta
    leggibile, e ci si ferma lì: chi ha un nome già corto — la mediana è 37 caratteri — non
    viene toccato.

    La riga per intero non si perde: va nella descrizione (vedi import.php).
    """
    n = (nome or '').strip()
    if len(n) <= NOME_MAX:
        return n

    segs = [s.strip() for s in re.split(r'\s+[-–]\s+', n) if s.strip()]
    out = segs[0]
    for s in segs[1:]:
        if len(out) + 3 + len(s) > NOME_MAX:
            break
        out += ' – ' + s

    if len(out) > NOME_MAX:                       # un solo segmento, lunghissimo: taglio a parola
        out = out[:NOME_MAX].rsplit(' ', 1)[0].rstrip(' ,;:-') + '…'
    return out


# ── report ────────────────────────────────────────────────────────────────────
for p in prodotti.values():
    p['desc_note'] = list(dict.fromkeys(p['desc_note']))
    p.pop('_seen', None)
    p['nome_breve'] = nome_breve(p.get('nome'))

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

# Un prodotto già in catalogo non sparisce perché l'estrazione di oggi non lo ricostruisce.
#
# out/prodotti.json è la fonte dell'import che ha popolato lo store: se una rigenerazione ne
# perde per strada dei pezzi, il danno arriva ai clienti (prodotto ordinabile che svanisce),
# e sovrascrivere in silenzio lo nasconderebbe. È già successo: 33 SKU delle pagine VER 26-28
# stanno nel catalogo ma la loro estrazione non è mai finita in extract/, quindi lo script
# non è più in grado di ricostruirli. Finché quel buco resta, i superstiti si conservano —
# e l'anomalia si stampa, invece di rimanere sepolta.
vecchio = {}
if os.path.exists(f'{OUT}/prodotti.json'):
    try:
        vecchio = json.load(open(f'{OUT}/prodotti.json'))
    except Exception:
        vecchio = {}

orfani = [s for s in vecchio if s not in prodotti]
for s in orfani:
    # I conservati non passano dal loop di arricchimento sopra: il nome breve glielo si
    # calcola qui, o restano con la riga di listino intera come titolo.
    prodotti[s] = vecchio[s]
    prodotti[s]['nome_breve'] = nome_breve(prodotti[s].get('nome'))

json.dump(prodotti, open(f'{OUT}/prodotti.json', 'w'), ensure_ascii=False, indent=1)
print()
if orfani:
    print(f'!! {len(orfani)} SKU in catalogo che questa estrazione NON ricostruisce: conservati dal file precedente.')
    print(f'   Causa nota: pagine VER 26-28 assenti da extract/ e part_d marcato type="altro".')
    for s in orfani[:5]:
        print(f'   {s}  {str(vecchio[s].get("nome"))[:60]}')
if SCARTI:
    print(f'!! PREZZI PERSI (varianti indistinguibili): {len(SCARTI)}')
    for x in SCARTI[:5]: print('   ', x['sku'], x['attrs'], f"tenuto {x['tenuto']} scartato {x['scartato']}")
else:
    print('✓ nessun prezzo perso: ogni variante ha una chiave attributi univoca')
print(f'\n→ {OUT}/prodotti.json')
