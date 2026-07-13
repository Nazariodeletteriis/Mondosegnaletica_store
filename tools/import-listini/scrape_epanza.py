#!/usr/bin/env python3
"""
Prende le fotografie dei cartelli da epanza (sito affiliato) e le aggancia ai nostri prodotti.

Perché serve: le immagini che abbiamo oggi sono i DISEGNI ritagliati dal listino del
fornitore — pittogrammi al tratto su fondo bianco, che dentro le card scure del sito si
vedono male. Epanza ha fotografie vere dei cartelli finiti.

L'aggancio è per CODICE FIGURA, e il codice sta scritto nell'URL dei loro prodotti
(…-fig-412a-…). Nessuna somiglianza, nessun "assomiglia a": o il codice combacia o non si
aggancia. Ci ho provato, con la somiglianza dei nomi, e non regge: "Gilet Classe 2" pesca il
loro "Gilet Classe 3", "Paletto Ø 89" pesca il loro "Ø 60". Sono prodotti diversi, e una foto
sbagliata su merce omologata è peggio di nessuna foto.

Tre errori già pagati, scritti qui perché non si ripetano:
  · guardare la sola categoria 130 (527 prodotti): il catalogo vero ne ha 2.128, e si prende
    dalla sitemap, non dalle pagine di categoria;
  · pesare l'abbinamento sulla COPERTURA del nostro nome invece che sull'intersezione: con
    nomi corti dà 0.90 a "Lamiera di Ferro 10/10" contro "cartello attraversamento tramviario";
  · sperare che il codice figura fosse "nascosto nel titolo" delle schede che non l'hanno
    nell'URL. Misurato: NO. Delle 2.128 schede di epanza solo 240 hanno un codice figura, e
    le altre 1.888 sono un altro mestiere (scarpe, guanti, tute, DPI). Fra le 170 che almeno
    SEMBRANO cartelli, il codice nel testo compare 1 volta su 40: le altre sono pannelli
    integrativi, che il CdS numera per MODELLO (mod. 3/d) e non per figura — il codice non
    manca, non gli spetta.

Copertura reale, misurata: 143 nostri prodotti hanno un codice figura che epanza copre —
24 oggi non hanno immagine, 119 hanno il disegno di listino e prenderebbero la foto.
Il tetto NON è lontano: i loro codici figura sono 240 in tutto, i nostri 494. Leggere anche
l'H1 delle 170 schede "sospette" (--sonda-html) vale una manciata di prodotti in più, non un
salto di categoria.

  python3 scrape_epanza.py                # raccoglie e propone (non scarica)
  python3 scrape_epanza.py --sonda-html   # + legge la figura dall'H1 delle schede sospette
  python3 scrape_epanza.py --scarica      # scarica le immagini agganciate in epanza-img/
"""
import json, os, re, sys, time, urllib.request, urllib.error

REPO    = os.path.dirname(os.path.abspath(__file__))
OUT     = os.path.join(REPO, 'out')
IMGDIR  = os.path.join(REPO, 'epanza-img')
CACHE   = os.path.join(OUT, 'epanza_figure_html.json')   # url → figura letta nell'H1 ('' = niente)
SITEMAP = 'https://epanza.com/1_it_0_sitemap.xml'
PAUSA   = 1.0        # una richiesta al secondo: è il sito di qualcun altro
UA      = ('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) '
           'Chrome/120 Safari/537.36')

RE_URL  = re.compile(r'https://epanza\.com/it/[a-z0-9-]+/(\d+)-([^"<\s]+?)\.html')
RE_FIG  = re.compile(r'-fig-(\d{1,3})([a-z])?(?:-(sx|dx))?[-.]', re.I)
# nella scheda prodotto la foto grande sta nei meta OpenGraph
RE_OG   = re.compile(r'<meta[^>]+property="og:image"[^>]+content="([^"]+)"', re.I)
# il codice come lo scrivono nel titolo della scheda: "… classe 1 fig.388"
RE_H1   = re.compile(r'<h1[^>]*>(.*?)</h1>', re.I | re.S)
RE_FIG_TXT = re.compile(r'\bfig\.?\s*(\d{1,3})\s*[/\-\s]?\s*([a-z])?\b', re.I)
# le schede che almeno SEMBRANO cartelli: le uniche su cui vale la pena spendere una richiesta
CARTELLO = re.compile(r'cartell|segnal|disco|pannell|targ|preavvis|divieto|obblig|pericol', re.I)


def scarica(url):
    req = urllib.request.Request(url, headers={'User-Agent': UA})
    with urllib.request.urlopen(req, timeout=30) as r:
        return r.read()


def catalogo():
    """Tutti i prodotti italiani, dalla sitemap. La categoria da sola ne mostra un quarto."""
    xml = scarica(SITEMAP).decode('utf-8', 'replace')
    visti = {}
    for m in RE_URL.finditer(xml):
        visti.setdefault(m.group(1), m.group(0))
    return visti      # id prestashop → url


def figura(url):
    """Il codice figura del Codice della Strada, come lo scrive il listino: 412/A."""
    m = RE_FIG.search(url)
    if not m:
        return None
    return m.group(1) + ('/' + m.group(2).upper() if m.group(2) else '')


def chiave(f):
    return re.sub(r'[^A-Z0-9]', '', str(f or '').upper())


def figura_nell_h1(url, cache):
    """Il codice figura scritto nel titolo della scheda, per le poche che non l'hanno nell'URL.

    Rende poco (1 scheda su 40) ma costa una richiesta e la cache lo rende una volta sola.
    Il '' in cache è una risposta: 'ho già guardato, non c'è'."""
    if url in cache:
        return cache[url] or None
    try:
        html = scarica(url).decode('utf-8', 'replace')
    except Exception:
        return None            # non lo metto in cache: un errore di rete non è una risposta
    m = RE_H1.search(html)
    testo = re.sub(r'<[^>]+>', ' ', m.group(1)) if m else ''
    f = RE_FIG_TXT.search(testo)
    cache[url] = (f.group(1) + (f.group(2).upper() if f.group(2) else '')) if f else ''
    time.sleep(PAUSA)
    return cache[url] or None


def main():
    os.makedirs(OUT, exist_ok=True)

    print('Leggo la sitemap di epanza…')
    prod = catalogo()
    print(f'  prodotti trovati: {len(prod)}')

    # indice: codice figura → url del loro prodotto
    loro = {}
    for pid, url in prod.items():
        f = figura(url)
        if f:
            loro.setdefault(chiave(f), {'figura': f, 'url': url, 'id': pid})
    print(f'  con codice figura nell\'URL: {len(loro)} codici distinti')

    # ── il codice scritto nel titolo, non nell'URL ──
    # Vale una manciata di prodotti, non un salto di categoria: delle schede che sembrano
    # cartelli ma non hanno la figura nell'URL, quasi tutte sono pannelli integrativi, che
    # il CdS numera per modello (mod. 3/d) e non per figura.
    if '--sonda-html' in sys.argv:
        cache = json.load(open(CACHE)) if os.path.exists(CACHE) else {}
        sospette = [(pid, u) for pid, u in prod.items()
                    if not figura(u) and CARTELLO.search(u)]
        print(f'\nSondo l\'H1 di {len(sospette)} schede senza figura nell\'URL…')
        agg = 0
        for i, (pid, url) in enumerate(sospette, 1):
            f = figura_nell_h1(url, cache)
            if f and chiave(f) not in loro:
                loro[chiave(f)] = {'figura': f, 'url': url, 'id': pid}
                agg += 1
            # la cache si salva strada facendo: se lo script muore (o si riavvia il PC) il
            # lavoro fatto resta, e al rilancio riparte da dove era invece che da capo
            if i % 20 == 0:
                json.dump(cache, open(CACHE, 'w'), ensure_ascii=False, indent=1)
        json.dump(cache, open(CACHE, 'w'), ensure_ascii=False, indent=1)
        print(f'  codici figura in più, letti dal titolo: {agg}')

    nostri = json.load(open(f'{REPO}/out/prodotti.json'))

    proposte = []
    for sku, p in nostri.items():
        if not p.get('figura'):
            continue
        v = loro.get(chiave(p['figura']))
        if not v:
            continue
        proposte.append({
            'sku': sku,
            'nome': p.get('nome_breve') or p.get('nome'),
            'figura': p['figura'],
            'epanza_figura': v['figura'],
            'epanza_url': v['url'],
            # cosa succede a questo prodotto: prende la prima immagine, o sostituisce
            # il disegno di listino con una fotografia
            'azione': 'sostituisce-disegno' if p.get('immagine') else 'prima-immagine',
        })

    json.dump(proposte, open(f'{OUT}/epanza_proposte.json', 'w'), ensure_ascii=False, indent=1)

    nuove = [x for x in proposte if x['azione'] == 'prima-immagine']
    print(f'\nABBINATI per codice figura : {len(proposte)}')
    print(f'  prima immagine           : {len(nuove)}')
    print(f'  foto al posto del disegno: {len(proposte) - len(nuove)}')
    print(f'→ out/epanza_proposte.json')

    if '--scarica' not in sys.argv:
        print('\n(non ho scaricato niente: rilancia con --scarica)')
        return

    # ── scarico solo le immagini dei prodotti agganciati ──
    os.makedirs(IMGDIR, exist_ok=True)
    fatti = falliti = 0
    for x in proposte:
        dest = os.path.join(IMGDIR, f"{chiave(x['figura'])}.jpg")
        if os.path.exists(dest):
            x['file'] = os.path.basename(dest)   # già scaricata: va comunque nella proposta,
            fatti += 1                           # o al secondo giro l'apply non la trova
            continue
        try:
            html = scarica(x['epanza_url']).decode('utf-8', 'replace')
            m = RE_OG.search(html)
            if not m:
                falliti += 1
                continue
            open(dest, 'wb').write(scarica(m.group(1)))
            x['file'] = os.path.basename(dest)
            fatti += 1
            time.sleep(PAUSA)
        except Exception as e:
            print(f"  !! {x['sku']}: {e}")
            falliti += 1

    json.dump(proposte, open(f'{OUT}/epanza_proposte.json', 'w'), ensure_ascii=False, indent=1)
    print(f'\nimmagini scaricate : {fatti}   falliti: {falliti}')
    print(f'→ {IMGDIR}')


if __name__ == '__main__':
    main()
