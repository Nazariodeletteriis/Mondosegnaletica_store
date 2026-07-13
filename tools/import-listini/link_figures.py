#!/usr/bin/env python3
"""
Prepara le immagini da mettere sui prodotti: rifila i ritagli e scrive out/figure_immagini.json.

L'aggancio ritaglio→prodotto NON si fa più qui. Lo fa normalize.py, per (listino, PAGINA,
posizione), e lo scrive nel campo `immagine` di ogni prodotto. Questo script si limita a
fidarsi di quel campo.

La versione precedente agganciava per CODICE FIGURA globale, e su alcune sezioni era una
trappola: in ACCESSORI la "figura" è una lettera che vale solo dentro la sua pagina — la 'A'
di pagina 3 è un segnale di velocità, la 'A' di pagina 6 è una tuta da lavoro. Cercando la
lettera 'A' in tutto il listino, l'immagine del cartello finiva addosso alla tuta. Peggio:
il codice veniva assegnato a UN SOLO prodotto per figura, e gli altri 473 che condividono
un codice restavano senza immagine.

Rifila anche la didascalia dal fondo del ritaglio: sul prodotto va il cartello, non la
scritta "FIGURA 45" del listino.
"""
import json, os, shutil
from PIL import Image

REPO = os.path.dirname(os.path.abspath(__file__))
SRC  = os.path.join(REPO, 'crops-raw')     # ritagli grezzi, con la didascalia
DEST = os.path.join(REPO, 'figures')       # rifilati, sono questi che vanno su WooCommerce

os.makedirs(DEST, exist_ok=True)

prodotti = json.load(open(os.path.join(REPO, 'out/prodotti.json')))

voci, mancanti, senza_img = [], 0, 0
for sku, p in prodotti.items():
    f = p.get('immagine')
    if not f:
        senza_img += 1
        continue

    src = os.path.join(SRC, f)
    if not os.path.exists(src):
        mancanti += 1
        continue

    # Un ritaglio serve più prodotti (stessa figura, formati diversi): rifila una volta sola.
    dst = os.path.join(DEST, f)
    if not os.path.exists(dst):
        with Image.open(src) as im:
            w, h = im.size
            im.crop((2, 2, w - 2, int(h * 0.84))).save(dst)   # via la didascalia e il bordo cella

    voci.append({
        'sku': sku,
        'file': f,
        'figura': p.get('figura'),
        # il nome BREVE, non quello di listino: apply_images.php riallinea il titolo del
        # prodotto a questo campo, e col nome lungo rimetterebbe in pagina i titoli
        # chilometrici che apply_nomi.php ha appena accorciato.
        'nome': p.get('nome_breve') or p.get('nome'),
        'confidenza': p.get('nome_da_verificare') or 'alta',
    })

json.dump(voci, open(os.path.join(REPO, 'out/figure_immagini.json'), 'w'),
          ensure_ascii=False, indent=1)

print(f'prodotti totali        : {len(prodotti)}')
print(f'CON IMMAGINE           : {len(voci)}')
print(f'senza ritaglio associato: {senza_img}')
if mancanti:
    print(f'!! ritaglio dichiarato ma assente su disco: {mancanti}')
print(f'immagini rifilate in   : {DEST}')
print(f'→ out/figure_immagini.json')
