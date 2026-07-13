#!/usr/bin/env python3
"""
Raccoglie il catalogo pubblico di epanza e lo aggancia ai nostri prodotti per codice figura.

Non è una lettura "a occhio": il codice figura del Codice della Strada compare nell'URL di
ogni loro prodotto (…-fig-412a-…), e noi lo abbiamo nel campo `figura`. L'aggancio è quindi
esatto, e ciò che non aggancia resta fuori invece di essere indovinato.

Questo script SOLO RACCOGLIE (URL, titolo, immagine, codice figura) e propone gli abbinamenti.
Non scarica niente e non tocca lo store: scaricare le immagini è un passo separato e
deliberato, perché le fotografie di prodotto di un concorrente sono roba loro — i pittogrammi
dei cartelli sono standard di legge e riprodurli è lecito, una loro fotografia no.

  python3 scrape_epanza.py            # raccoglie e propone
  python3 scrape_epanza.py --pagine 3 # prova su poche pagine
"""
import json, os, re, sys, time, urllib.request, urllib.error
from difflib import SequenceMatcher

REPO   = os.path.dirname(os.path.abspath(__file__))
BASE   = 'https://epanza.com/it/130-segnaletica-stradale-e-viabilita'
OUT    = os.path.join(REPO, 'out')
PAUSA  = 1.0          # una richiesta al secondo: è il catalogo di qualcun altro
UA     = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36'

# L'immagine nella griglia è la miniatura (home_default). Per la scheda prodotto serve
# la versione grande, che in PrestaShop è lo stesso URL con un altro suffisso.
TAGLIA_GRANDE = 'large_default'

RE_PROD = re.compile(
    r'<a[^>]+href="(https://epanza\.com/it/[a-z0-9-]+/(\d+)-([^"#]+?)\.html)"[^>]*>', re.I)
RE_IMG  = re.compile(
    r'data-src="(https://epanza\.com/(\d+)-[a-z_]+/([^"]+?)\.(?:jpg|webp))"', re.I)
# "…-fig-412a-…" / "…-fig-411b-sx-…" — il codice figura stampato nello slug
RE_FIG  = re.compile(r'-fig-(\d{1,3})([a-z])?(?:-(sx|dx))?-', re.I)


def scarica(url):
    req = urllib.request.Request(url, headers={'User-Agent': UA})
    with urllib.request.urlopen(req, timeout=30) as r:
        return r.read().decode('utf-8', 'replace')


def figura_da_slug(slug):
    """Il codice figura come lo scrive il Codice della Strada: 412/a, 411/b."""
    m = RE_FIG.search('-' + slug + '-')
    if not m:
        return None
    num, let, lato = m.group(1), m.group(2), m.group(3)
    cod = num + ('/' + let.upper() if let else '')
    return cod, (lato.upper() if lato else None)


def raccogli(pagine_max=None):
    """Percorre la categoria pagina per pagina finché non escono più prodotti nuovi."""
    visti, voci = set(), []
    pagina = 1
    while True:
        if pagine_max and pagina > pagine_max:
            break
        url = BASE if pagina == 1 else f'{BASE}?page={pagina}'
        try:
            html = scarica(url)
        except urllib.error.HTTPError as e:
            print(f'  pagina {pagina}: HTTP {e.code} — mi fermo')
            break

        # Prodotti e immagini compaiono nello stesso ordine dentro la griglia: si appaiano
        # per ID PrestaShop, che sta sia nell'URL del prodotto sia in quello dell'immagine.
        prodotti = {m.group(2): (m.group(1), m.group(3)) for m in RE_PROD.finditer(html)}
        immagini = {}
        for m in RE_IMG.finditer(html):
            immagini.setdefault(m.group(3), m.group(1))   # slug immagine → url

        nuovi = 0
        for pid, (purl, pslug) in prodotti.items():
            if pid in visti:
                continue
            visti.add(pid)
            nuovi += 1

            # lo slug dell'immagine è quello del prodotto senza l'ID e senza l'EAN in coda
            img = None
            for islug, iurl in immagini.items():
                if islug and (islug in pslug or pslug.startswith(islug)):
                    img = iurl.replace('home_default', TAGLIA_GRANDE)
                    break

            fig = figura_da_slug(pslug)
            voci.append({
                'id': pid,
                'url': purl,
                'slug': pslug,
                'immagine': img,
                'figura': fig[0] if fig else None,
                'lato': fig[1] if fig else None,
            })

        print(f'  pagina {pagina}: {nuovi} prodotti nuovi (totale {len(voci)})')
        if nuovi == 0:
            break
        pagina += 1
        time.sleep(PAUSA)

    return voci


def norma(f):
    return re.sub(r'[^A-Z0-9]', '', str(f or '').upper())


def main():
    pagine = None
    if '--pagine' in sys.argv:
        pagine = int(sys.argv[sys.argv.index('--pagine') + 1])

    print('Raccolgo il catalogo epanza…')
    voci = raccogli(pagine)

    os.makedirs(OUT, exist_ok=True)
    json.dump(voci, open(f'{OUT}/epanza_catalogo.json', 'w'), ensure_ascii=False, indent=1)

    con_fig = [v for v in voci if v['figura']]
    con_img = [v for v in voci if v['immagine']]
    print(f'\nprodotti raccolti     : {len(voci)}')
    print(f'  con codice figura   : {len(con_fig)}')
    print(f'  con immagine        : {len(con_img)}')

    # ── abbinamento coi nostri prodotti, per codice figura ──
    nostri = json.load(open(f'{REPO}/out/prodotti.json'))
    senza_img = {s: p for s, p in nostri.items() if not p.get('immagine') and p.get('figura')}

    indice = {}
    for v in con_fig:
        if v['immagine']:
            indice.setdefault(norma(v['figura']), v)

    # Il codice figura da solo non basta come chiave.
    #
    # Nei nostri listini non tutte le "figure" sono codici del Codice della Strada: su certe
    # pagine sono numeri d'ordine locali (la figura 16 di pagina 27 non è la FIG. 16 del CdS).
    # Agganciando sul solo numero si rimetterebbe in pagina l'errore che abbiamo appena
    # finito di togliere: la foto di un altro prodotto. Serve un secondo segnale d'accordo,
    # e ce l'abbiamo — il titolo di epanza descrive il cartello, e anche il nostro.
    proposte, scartate = [], 0
    for sku, p in senza_img.items():
        v = indice.get(norma(p['figura']))
        if not v:
            continue
        nostro = re.sub(r'[^a-z0-9]', '', (p.get('nome_breve') or p['nome']).lower())
        loro   = re.sub(r'[^a-z0-9]', '', v['slug'].replace('-', ' ').lower())
        sim = SequenceMatcher(None, nostro, loro).ratio()
        m = SequenceMatcher(None, nostro, loro).find_longest_match(0, len(nostro), 0, len(loro))
        accordo = max(sim, (m.size / max(len(nostro), 1)) * 0.95)

        if accordo < 0.30:
            scartate += 1
            continue

        proposte.append({'sku': sku, 'nome': p.get('nome_breve') or p['nome'],
                         'figura': p['figura'], 'epanza_figura': v['figura'],
                         'accordo_nome': round(accordo, 2),
                         'immagine': v['immagine'], 'fonte': v['url']})

    proposte.sort(key=lambda x: -x['accordo_nome'])
    json.dump(proposte, open(f'{OUT}/epanza_proposte.json', 'w'), ensure_ascii=False, indent=1)

    print(f'\nnostri prodotti SENZA immagine ma CON codice figura : {len(senza_img)}')
    print(f'  figura combacia MA il nome no (scartati)          : {scartate}')
    print(f'ABBINAMENTI PROPOSTI (figura + nome d\'accordo)      : {len(proposte)}')
    print(f'\n→ out/epanza_catalogo.json · out/epanza_proposte.json')
    if proposte:
        print('\nmigliori:')
        for x in proposte[:6]:
            print(f"   {x['accordo_nome']:.2f}  {x['sku']:22} fig {str(x['figura']):8} · {x['nome'][:44]}")
        print('\npeggiori (da guardare):')
        for x in proposte[-4:]:
            print(f"   {x['accordo_nome']:.2f}  {x['sku']:22} fig {str(x['figura']):8} · {x['nome'][:44]}")


if __name__ == '__main__':
    main()
