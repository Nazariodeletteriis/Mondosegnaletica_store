#!/usr/bin/env python3
"""
Collega i ritagli ai prodotti usando il CODICE FIGURA letto dentro l'immagine.

L'aggancio per posizione (n-esima cella = n-esima figura) non è affidabile: su alcune
pagine il rilevatore scambia le celle di intestazione della tabella per cartelli e
l'ordine slitta. Qui invece si usa la didascalia stampata nel ritaglio, che è un
identificatore, non una posizione.

Rifila anche la didascalia dal fondo: sul prodotto va il cartello, non la scritta
"FIGURA 45" del listino.
"""
import json, glob, os, re, shutil, unicodedata
from PIL import Image

SCRATCH = os.environ.get('MS_SCRATCH', '.')
REPO    = '/var/www/Mondosegnaletica_store/tools/import-listini'
SRC     = os.path.join(SCRATCH, 'figures')
DEST    = os.path.join(REPO, 'figures')
os.makedirs(DEST, exist_ok=True)

def sku_part(s, n=10):
    s = unicodedata.normalize('NFKD', str(s or '')).encode('ascii', 'ignore').decode()
    return re.sub(r'[^A-Z0-9]', '', s.upper())[:n] or 'X'

prodotti = json.load(open(os.path.join(REPO, 'out/prodotti.json')))

def chiavi(fig):
    """Varianti normalizzate di un codice figura.

    Lo stesso cartello è scritto in modi diversi nei due punti da cui leggiamo: la
    didascalia stampata nel ritaglio dice "FIGURA II 441/A ART. 148", la tabella del
    listino dice "441/A". Un confronto letterale non aggancia quasi nulla, quindi si
    confrontano forme ridotte: senza il prefisso romano, senza il rimando all'articolo.
    """
    f = str(fig or '').upper()
    out = []
    for v in (f, re.sub(r'\bART\.?\s*\d+.*$', '', f), re.sub(r'^\s*I{1,3}\s+', '', f)):
        v = re.sub(r'^\s*I{1,3}\s+', '', v)
        v = re.sub(r'\bART\.?\s*\d+.*$', '', v)
        k = re.sub(r'[^A-Z0-9]', '', v)
        if k and k not in out:
            out.append(k)
    return out

# indice: chiave normalizzata → sku (solo prodotti nati da una figura)
indice = {}
for sku, p in prodotti.items():
    if not p.get('figura'):
        continue
    tag = sku.split('-')[1] if sku.startswith('MS-') else None
    for k in chiavi(p['figura']):
        indice.setdefault((tag, k), sku)

letture = []
for f in sorted(glob.glob(os.path.join(SCRATCH, 'naming2/fig_*.json'))):
    try:
        letture += json.load(open(f))
    except Exception as e:
        print(f'!! {os.path.basename(f)}: {e}')

print(f'ritagli letti      : {len(letture)}')

voci, scarti, senza_fig, senza_sku = [], 0, 0, 0
visti = set()

# Più ritagli possono riportare lo stesso codice figura, e non sono equivalenti:
# alcuni sono ACCESSORI di quel cartello (didascalia "PER FIGURA II 392" = il sostegno,
# non la barriera). Agganciando il primo che capita finirebbe la foto del sostegno sul
# prodotto sbagliato. Ordino per confidenza e scarto gli accessori, così a parità di
# codice vince il ritaglio del cartello vero.
RANK = {'alta': 0, 'media': 1, 'bassa': 2}
ACCESSORIO = re.compile(r'\b(per figura|sostegno|supporto|staffa|piede|zavorra)\b', re.I)

letture.sort(key=lambda x: (
    RANK.get(x.get('confidenza'), 3),
    1 if ACCESSORIO.search((x.get('nome') or '')) else 0,
))

for x in letture:
    if x.get('scarto'):
        scarti += 1
        continue
    fig = (x.get('figura') or '').strip()
    if not fig:
        senza_fig += 1
        continue

    tag = (x.get('file') or '')[:3]
    sku = next((indice[(tag, k)] for k in chiavi(fig) if (tag, k) in indice), None)

    if not sku:
        senza_sku += 1
        continue
    if sku in visti:          # stessa figura ritagliata su più pagine: basta una immagine
        continue
    visti.add(sku)

    src = os.path.join(SRC, x['file'])
    if not os.path.exists(src):
        continue

    # rifila la didascalia in basso (~16% dell'altezza) e il bordo cella
    with Image.open(src) as im:
        w, h = im.size
        im.crop((2, 2, w - 2, int(h * 0.84))).save(os.path.join(DEST, x['file']))

    voci.append({
        'sku': sku,
        'file': x['file'],
        'figura': fig,
        'nome': (x.get('nome') or '').strip() or None,
        'confidenza': x.get('confidenza'),
    })

os.makedirs(os.path.join(REPO, 'out'), exist_ok=True)
json.dump(voci, open(os.path.join(REPO, 'out/figure_immagini.json'), 'w'),
          ensure_ascii=False, indent=1)

bassa = [v for v in voci if v['confidenza'] == 'bassa']
print(f'scartati (non cartelli): {scarti}')
print(f'senza codice figura    : {senza_fig}')
print(f'codice senza prodotto  : {senza_sku}')
print(f'AGGANCIATI             : {len(voci)}   (a bassa confidenza: {len(bassa)})')
print(f'→ out/figure_immagini.json')
