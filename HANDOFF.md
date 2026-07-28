# HANDOFF — Mondo Segnaletica
> Stato al **2026-07-28** (sessione 14 **IN CORSO**). **Leggi SOLO il primo blocco per ripartire.** Tutto ciò che sta sotto è storico superato.

---

## 🟡 2026-07-28 — Sessione 14 **IN CORSO** (checkpoint automatico): coming soon — indirizzi, email unificata, **privacy policy GDPR**. **TUTTO SOLO IN LOCALE, DEPLOY LIVE NON ANCORA ESEGUITO.**

> Lavori in `deploy/coming-soon/`. Il live è ancora alla versione della sessione 13.

- 📍 **`index.html`** — aggiunta riga **"Sede operativa · Viale Europa, 48/50 · Lucca"** nel blocco `.meta`, sotto la sede legale.
- 📍 **`index.html`** — **topbar** (visibile solo `>=961px`) ora mostra *"Sede legale Via Carlo Angeloni, 360 · Sede operativa Viale Europa, 48/50 · Lucca"*, con `.topbar__addr{text-align:right}` e `.beacon{white-space:nowrap}` per gestire il wrap.
- ✉️ **Email uniformata** da `mondosegnaleticacoop@gmail.com` a **`info@mondosegnaletica.it`** (sia `mailto:` sia testo visibile). ✅ **CHIUDE la questione aperta #1** della sessione 13. `invia.php` era già su `info@`.
- 🔐 **NUOVO FILE `deploy/coming-soon/privacy.html`** — informativa **GDPR art. 13** completa in 9 sezioni (titolare, dati raccolti, finalità/base giuridica con tabella, destinatari, conservazione, cookie, diritti artt. 15-22, sicurezza, modifiche). Stile *Sistema Strada* coerente con la home. P.IVA `02629010460` presa da `public/wp-content/themes/mondosegnaletica/front-page.php:39`. Dichiara esplicitamente **Netsons come responsabile art. 28** e **Google Fonts come trasferimento extra-UE**. ✅ **CHIUDE la questione aperta #2** — *va comunque fatta validare a un legale*.
- 🔗 **`index.html`** — la checkbox di consenso ora linka `privacy.html`.
- 🚧 **IN CORSO**: pulsante submit disabilitato finché la checkbox privacy non è spuntata. CSS già fatto (`.btn[disabled]` → `cursor:not-allowed`, variante `[data-invio]` → `cursor:progress`), **manca la logica JS**.

### TODO PRIORITARIO
1. **Finire il JS** del pulsante submit (abilita/disabilita al toggle della checkbox privacy).
2. Aggiungere il **link a privacy.html nel footer `.meta`** della home.
3. **DEPLOY LIVE** via `scp` → `ssh mondosegnaletica` (**porta 65100**) in `public_html`. Caricare `index.html` **e** il nuovo `privacy.html`.
4. Rigenerare lo zip `deploy/mondosegnaletica-coming-soon.zip` (ora sono 7 file) e committare.

---

## 🟢 2026-07-27 — Sessione 13 CHIUSA: **COMING SOON ONLINE, HTTPS OK, FORM REALE COLLAUDATO**. DEPLOYATO.

> Questo blocco **supersede tutti i blocchi sottostanti**, inclusi i checkpoint 1, 2 e 3 di oggi.

- 🖥️ **HOSTING migrato** al piano Netsons **Hosting Web 500**. **IP cambiato: `89.40.173.35` → `89.40.173.117`**, server `hostingweb16.netsons.net`.
  - **SSH porta `65100`** — dato reperibile **SOLO in Area Clienti Netsons → Hosting → Gestione hosting → Dati di accesso**, *non* in cPanel. Host `cpanel.mondosegnaletica.it`, utente `thfghgww`, **auth solo a chiave** (`~/.ssh/mondosegnaletica_ed25519`), alias `mondosegnaletica` in `~/.ssh/config`. `ssh mondosegnaletica` e `scp` funzionano.
  - Tool server: git 2.48.2, PHP 8.4 CLI **ma il sito gira su `ea-php81`**, MariaDB 10.11.18 + `mysqldump`, WP-CLI 2.11.0, Composer 2.10.2. **Assenti `rsync` e `node`.**
- 🔒 **SSL — il certificato non è mai mancato.** Let's Encrypt valido **26/07 → 24/10/2026**, catena completa (`Verify return code 0`), SAN su apex, `www`, `shop`, `mail`, `cpanel`. **NON toccare il certificato.** Il "sito non sicuro" aveva **due cause distinte**:
  - **(a)** Force HTTPS Redirect spento → risolto con blocco `RewriteCond %{HTTPS} !=on` in `.htaccess`; verificato **301 singolo senza loop** su apex e www.
  - **(b)** Sul **cellulare** il DNS puntava ancora al **vecchio IP `89.40.173.35`**, tuttora attivo sulla 443 con certificato valido solo per `hostingweb61-35.netsons.net` → `no alternative certificate subject name`. **Non è un guasto**: si risolve con la propagazione DNS (o cambiando rete/DNS sul telefono).
- 🧹 **`public_html` ripulito: 6,9 MB → ~292 KB.** Backup in `~/backup-vecchio-sito-20260727-1235.tar.gz` e `~/.htaccess.bak-1241` (**cancellare quando l'utente è sicuro**).
  - Rimossi residui vecchio sito 2016-2019 (`LICENSE`, `css/`, `js/`, `img/`, `fonts/`, `font-awesome/`, `default.html`, `licenza.html`, `test/`, `error_log` 3,8 MB, vecchio `robots.txt`), la cartella `~/mondosegnaletica.it` con `_p2.txt`, e — **su richiesta esplicita dell'utente** — anche i 2 file di verifica Google Search Console (**se servirà, la verifica va rifatta**).
  - Tenuti: `.well-known/` (SSL), `cgi-bin/`, `.htaccess` (regole nostre **in coda** al blocco cPanel `ea-php81`, mai sovrascritto), `robots.txt` nuovo.
- 📱 **Fix mobile — soluzione finale diversa dal checkpoint 3.** L'utente ha **rifiutato `object-fit:contain`** e ha chiesto **cover centrato**. Immagine mobile rigenerata pulita **900×507 (64 KB, ratio 1.775)** + media query `max-width:800px` con `.hero__bg{height:55vw;bottom:auto}`: sotto i **56,3vw** il ritaglio cover avviene **in verticale**, la larghezza si vede tutta → **logo intero e centrato**. Più `.topbar{position:absolute}`, gradiente che chiude la fascia nel nero, `.hero__body{padding-top:48vw}`.
- ✉️ **FORM ATTIVATO** — prima era un `mailto:` inutile, ora **invio server-side reale**.
  - Nuovo `deploy/coming-soon/invia.php`, **PHP 8.1 compatibile**. Destinatario e mittente `info@mondosegnaletica.it` (casella già esistente; **stesso dominio = SPF/DKIM allineati**), `Reply-To` con nome ed email del visitatore.
  - Protezioni: solo POST · **honeypot** campo `sito` (finge successo) · scarto invii **< 3 s** via campo `ts` · **rate limit 1/60 s per IP** (file in `sys_get_temp_dir`) · sanitizzazione anti header-injection (`\r \n \0`) · validazione server-side nome/email/consenso · subject UTF-8 base64 · `MIME-Version: 1.0`.
  - `index.html`: form in **POST via `fetch`** a `invia.php` con **fallback POST classico senza JS** (redirect `?esito=ok`), pulsante disabilitato durante l'invio, errori dal server, **checkbox consenso privacy obbligatoria**.
  - ✅ **Collaudo completo superato (7 test)**: GET → 405 JSON / 303 da browser · senza privacy 422 · email non valida 422 · honeypot → finto ok senza invio · invio troppo rapido → finto ok · invio vero → ok · secondo invio immediato → 429. **Mail realmente consegnata** e verificata nella maildir di `info@` (Exim, filtro Imunify *no action*), header `From`/`To`/`Reply-To` corretti.
- 🚀 **Deploy ora via `scp` su SSH**, niente più zip + File Manager. Zip locale comunque rigenerato: `deploy/mondosegnaletica-coming-soon.zip` (265 KB, 6 file).

### ⚠️ DUE QUESTIONI APERTE (segnalate all'utente, NON risolte)
1. **Incoerenza indirizzi email**: la pagina mostra ancora `mondosegnaleticacoop@gmail.com` nella scheda contatti e nel link email, mentre il form invia a `info@mondosegnaletica.it`. Decidere se **uniformare tutto su `info@`** (più professionale B2B).
2. **Privacy policy assente**: la checkbox di consenso c'è, ma **manca l'informativa GDPR art. 13**, obbligatoria per un sito italiano che raccoglie nome/email/telefono. Serve una pagina da linkare nella checkbox.

### TODO PRIORITARIO
1. **Sciogliere le 2 questioni aperte** sopra: uniformare l'email su `info@mondosegnaletica.it` e scrivere/linkare la **privacy policy GDPR**.
2. **WordPress — decisione ancora sospesa**: (a) migrare il **locale DDEV** (tema custom + prodotti importati) oppure (b) **installazione nuova sul server**. Staging pronto su `shop.mondosegnaletica.it` (docroot `/shop.mondosegnaletica.it`). **Non procedere prima della risposta.**
3. **Git Version Control cPanel vuoto** → valutare **push-to-deploy** con `.cpanel.yml` + deploy key GitHub.
4. Riprendere **checkout / SMTP / pagine legali** della sessione 12.
5. **Cancellare i backup** (`~/backup-vecchio-sito-20260727-1235.tar.gz`, `~/.htaccess.bak-1241`) quando l'utente è sicuro.
6. **Repo locale sporco**: non committati i lavori della sessione 12 (`tools/import-listini/out/`) e **tutta la cartella `deploy/` creata oggi**. L'utente non ha ancora chiesto il commit.

---

## 🟡 2026-07-27 — Sessione 13 (checkpoint 3): SSH FUNZIONANTE + SERVER PULITO. *(storico — superato dal blocco sopra)*

> Questo blocco **supersede tutti i blocchi sottostanti**, inclusi i checkpoint 1 e 2 di oggi.

- ✅ **SSH RISOLTO**. Il dato mancante **non era in cPanel** ma nell'**Area Clienti Netsons → Hosting → Gestione hosting → Dati di accesso** (piano *Hosting Web 500*). Parametri: host `cpanel.mondosegnaletica.it`, **porta `65100`**, utente `thfghgww`, **autenticazione solo a chiave** (password SSH non prevista). Chiave ed25519 autorizzata dall'utente in cPanel → **connessione OK**.
  - Creato `~/.ssh/config` con alias `Host mondosegnaletica` (`HostName cpanel.mondosegnaletica.it`, `Port 65100`, `User thfghgww`, `IdentityFile ~/.ssh/mondosegnaletica_ed25519`, `IdentitiesOnly yes`, `ServerAliveInterval 60`) → basta **`ssh mondosegnaletica`**; VSCode Remote-SSH vede l'host in automatico.
  - Altri accessi: FTP `ftp.mondosegnaletica.it:21` utente `thfghgww` (con password) · MySQL `localhost:3306` · cPanel `https://hostingweb16.netsons.net:2083` e `https://cpanel.mondosegnaletica.it` · webmail `https://webmail.mondosegnaletica.it` · IMAP 993 / POP3 995 / SMTP 465.
- 🖥️ **Ambiente server** (`hostingweb16.netsons.net`): git 2.48.2, PHP **8.4.23** CLI, MariaDB 10.11.18 client + `mysqldump`, **WP-CLI 2.11.0**, Composer 2.10.2, python3 3.6.8, unzip. **Assenti: `rsync` e `node`** → deploy via **SFTP porta 65100** o **Git**, *non* rsync.
- ✅ **Fix mobile verificato online**: `index.html` 15655 B sul server, media query `max-width:800px` presente (2 occorrenze), `object-fit:contain` presente, `background-mobile.webp` servito 57378 B. **Logo intero visibile su telefono.**
- 🧹 **Pulizia server eseguita** (richiesta esplicita dell'utente). `public_html` era pieno di residui di un vecchio sito 2016-2019. **Backup preventivo: `~/backup-vecchio-sito-20260727-1235.tar.gz` (2,3 MB, fuori dal docroot — cancellare quando l'utente è sicuro).**
  - **Rimossi**: `LICENSE`, `css/`, `js/`, `img/`, `fonts/`, `font-awesome/`, `default.html`, `licenza.html`, `test/`, vecchio `robots.txt`, `error_log` da 3,8 MB fermo al 2023, e la cartella `~/mondosegnaletica.it` (conteneva `_p2.txt` e `.ftpquota`). **`public_html` da 6,9 MB → 292 KB.**
  - **Tenuti di proposito**: `.well-known/` (validazione SSL Let's Encrypt — cancellarla rompe il rinnovo), `googlea1af60571ba740e5.html` e `googlecc6982269e34b8de.html` (verifica Search Console, 53 B l'una — l'utente può decidere di toglierle), `cgi-bin/` (vuota, standard cPanel).
- ⚙️ **`.htaccess`**: le regole nuove sono state **aggiunte in coda** al file esistente, lasciando **intatto il blocco cPanel** che imposta l'handler PHP (`ea-php81`). File ora 840 B. **Verificato online**: `Cache-Control: no-cache, must-revalidate` sull'HTML, `public, max-age=31536000` su `.webp`, `ErrorDocument 404` che serve la coming soon, `robots.txt` nuovo (`User-agent: *` / `Allow: /`). Sito **200** col title corretto.
- 📂 **Stato `public_html` ora**: `.htaccess`, `.well-known/`, `assets/` (3 file), `cgi-bin/`, i 2 file di verifica Google, `index.html`, `robots.txt`.

### ❓ DOMANDA APERTA ALL'UTENTE (blocca il piano)
L'utente ha detto *"dobbiamo fare una cosa pulita ex novo"*. Chiarire se intende:
- **(a)** migrare il **WordPress locale DDEV esistente** (tema custom + prodotti importati), oppure
- **(b)** **installazione WordPress nuova sul server** e ricostruire da zero.

Cambia radicalmente il piano di deploy → **non procedere prima della risposta**.

### TODO PRIORITARIO
1. **Risposta dell'utente su (a) vs (b)** — vedi sopra.
2. **Attivare Force HTTPS Redirect** (cPanel → *Domains*, ancora **OFF**).
3. **Git Version Control** su cPanel è vuoto → valutare **push-to-deploy** con `.cpanel.yml` + deploy key GitHub.
4. **Staging su `shop.mondosegnaletica.it`** (subdomain + WP via WP-CLI già disponibile sul server).
5. Riprendere i punti aperti della sessione 12: **checkout, SMTP, pagine legali**.
6. Cancellare `~/backup-vecchio-sito-20260727-1235.tar.gz` quando l'utente conferma che non serve più.

---

## 🟡 2026-07-27 — Sessione 13 (checkpoint 2): **MIGRAZIONE NETSONS COMPLETATA + FIX MOBILE**. Zip pronto, da ricaricare. SUPERATO.

> Questo blocco **supersede tutti i blocchi sottostanti**, incluso il checkpoint 1 di oggi.

- ✅ **Migrazione conclusa** (confermata dall'utente). **IP CAMBIATO: `89.40.173.35` → `89.40.173.117`**. Utente cPanel `thfghgww` invariato.
- ✅ **Coming soon sopravvissuto integro**: `https://mondosegnaletica.it` risponde **200** col title corretto, **zero tracce della pagina di cortesia**, tutti e 3 gli asset serviti 200 (`background.webp` 179196 B, `background-mobile.webp` 48246 B, `favicon.png` 21747 B). Header `Cache-Control` **assente** → conferma che **`.htaccess` non è ancora stato caricato**.
- ⛔ **SSH ancora chiuso sul server nuovo**: testate **22, 2222, 2223, 22222, 21098, 65002, 7822, 2200** su `89.40.173.117` — tutte chiuse. Serve la porta reale da *cPanel → Security → SSH Access* (chiesto screenshot all'utente) o dal supporto Netsons.
- 🐛 **BUG MOBILE RISOLTO** — lo sfondo veniva ritagliato ai lati e **decapitava il logo MONDOSEGNALETICA + tagline**. Causa **geometrica**, non di allineamento: foto 1672×941 (ratio 1.78) dentro un contenitore molto più alto che largo → con `object-fit:cover` il browser scala per coprire l'altezza e taglia i lati. Spostare `object-position` non risolve (rientra un lato, esce l'altro).
- 🔧 **FIX applicato, 2 modifiche**:
  1. **Rigenerato `deploy/coming-soon/assets/background-mobile.webp`**: ora **900×627, 56 KB** (prima 800×450, 47 KB). Generato con PIL da `assets/img/background_new.png`: resize a 900px di larghezza (900×506), canvas esteso a 900×627 con fondo `#0A0A0A`, sfumatura progressiva **quadratica** delle righe dal 62% dell'altezza fino al nero pieno → la foto sfuma nel nero senza stacco netto.
  2. **`index.html`**: media query `@media (max-width:800px)` con `.hero{min-height:0}`, `.hero__bg img{object-fit:contain;object-position:center top}` (mostra l'immagine **intera in larghezza** invece di ritagliarla), gradiente overlay mobile alleggerito a `rgba(10,10,10,.45) 0% → trasparente 24%` (prima sovra-scuriva il logo), `.hero__body{margin-top:0;padding-top:52vw}` per far partire il testo dentro la zona già sfumata a nero.
- 📦 **ZIP rigenerato**: `/var/www/Mondosegnaletica_store/deploy/mondosegnaletica-coming-soon.zip` — **254 KB, 5 file** (`.htaccess` incluso).

### TODO PRIORITARIO
1. **ORA: l'utente ricarica lo zip in `public_html` e riestrae sovrascrivendo** → aggiorna `index.html` + `background-mobile.webp` e installa finalmente `.htaccess`. ⚠️ **Cache browser**: `background-mobile.webp` ha lo stesso nome del precedente → serve **hard refresh** per vedere il fix.
2. **Trovare la porta SSH reale** su `89.40.173.117` (cPanel → *Security → SSH Access*) e **autorizzare la chiave pubblica ed25519** già generata in locale (`~/.ssh/mondosegnaletica_ed25519`, fingerprint `SHA256:eTbxDon9lfrkBzK9McwwWauASvy1K/c9mmzsjUqVoWg`) via *Manage SSH Keys → Import Key → Authorize*.
3. **Attivare Force HTTPS Redirect** (ancora OFF).
4. **Pulizia residui**: verificare se `_p2.txt` e la cartella `mondosegnaletica.it` sono stati migrati sul server nuovo, ed eliminarli.
5. A SSH aperto: `~/.ssh/config` + **VSCode Remote-SSH**, **Git push-to-deploy**, **migrazione DB WordPress su staging `shop.mondosegnaletica.it`**.

---

## 🟡 2026-07-27 — Sessione 13 (checkpoint 1): **UPGRADE HOSTING NETSONS**. Nuove funzioni sbloccate, chiave SSH creata, porta SSH da trovare. IN CORSO.

> Questo blocco **supersede tutti i blocchi sottostanti**: quelli restano solo come storico.

- ⬆️ **Piano hosting superiore attivo**. Stesso server (**IP 89.40.173.35**), stesso utente cPanel **`thfghgww`**, stessa home `/home/thfghgww`, dominio primario invariato. Nuovi limiti: **disco 500 GB** (98,45 MB usati), **Databases 0/50**, **PostgreSQL 0/50**, **FTP Accounts 1/50**, **Addon Domains 0/5**, Bandwidth 2,46 MB, **SSL Active**.
- 🔓 **Nuove funzioni ora nel pannello**: *Security → SSH Access*, *Advanced → Terminal*, *Files → Git™ Version Control*, *Netsons MySQL Remote*, *Setup Node.js / Python / Ruby App*.
- ✅ **Sito coming soon integro dopo la migrazione**: `https://mondosegnaletica.it` risponde **200** con il title corretto. **DNS invariato** su `89.40.173.35` per apex, `www`, `shop` e `ftp`.
- 🔑 **Creata coppia di chiavi SSH dedicata** in locale: `~/.ssh/mondosegnaletica_ed25519` (+ `.pub`), tipo **ed25519**, **senza passphrase**, commento `vscode-wsl-nazario@mondosegnaletica`, permessi 600. Fingerprint `SHA256:eTbxDon9lfrkBzK9McwwWauASvy1K/c9mmzsjUqVoWg`.
- ⛔ **PROBLEMA APERTO — porte SSH chiuse dall'esterno**. Testate **22, 2222, 2223, 22222, 21098, 65002** su `89.40.173.35`: nessuna risponde, nessun banner. Ipotesi: **porta non standard Netsons** oppure **firewall con whitelist di IP autorizzati**. Serve il numero di porta reale (pagina *SSH Access*, *Server Information*, o supporto/documentazione Netsons). **Fallback sempre disponibile: cPanel → Terminal da browser**, funziona a prescindere dalla porta.

### TODO PRIORITARIO
1. **Trovare la porta SSH reale** (cPanel → SSH Access / Server Information / supporto Netsons) e verificare se il firewall richiede l'autorizzazione dell'IP di casa.
2. **Importare e AUTORIZZARE la chiave pubblica**: cPanel → *SSH Access → Manage SSH Keys → Import Key* (incollare **solo** la parte pubblica), poi *Manage → Authorize*.
3. Ad SSH aperto: configurare `~/.ssh/config` + **VSCode Remote-SSH**; valutare **Git Version Control (push-to-deploy)** al posto dell'upload manuale; usare **Netsons MySQL Remote o SSH** per la migrazione del DB WordPress; **staging su `shop.mondosegnaletica.it`**.
4. **Resta aperto da prima**: caricare `.htaccess` in `public_html` (⚠️ file nascosto → *Settings → Show Hidden Files*); attivare **Force HTTPS Redirect** (ancora OFF); rimuovere i residui `_p2.txt` e la cartella `mondosegnaletica.it`; account FTP `wpdeploy` **ora meno urgente** (con SSH si userà rsync/git al posto dell'FTP).

---

## 🟢 2026-07-27 — Sessione 13 (checkpoint): **PAGINA COMING SOON ONLINE E VERIFICATA**. Cache browser diagnosticata, `.htaccess` pronto da caricare.

> Questo blocco **supersede tutti i blocchi sottostanti**: quelli restano solo come storico.

- ✅ **ONLINE**. L'utente ha caricato ed estratto lo zip in `public_html`. **Verifica lato server superata**: `http://mondosegnaletica.it/`, `https://mondosegnaletica.it/` (HTTP/2) e `http://www.mondosegnaletica.it/` rispondono tutti **200** con `<title>Mondo Segnaletica — Il nuovo store apre a settembre</title>` e **zero occorrenze di "pagina-cortesia"** → la pagina Netsons è stata sostituita. `Last-Modified: 2026-07-27 09:31:40 GMT`.
- ✅ **Asset serviti correttamente** (tutti 200): `assets/background.webp` 179196 byte, `assets/background-mobile.webp` 48246 byte, `assets/favicon.png` 21747 byte.
- 🐞 **"Si vede in incognito ma non in navigazione normale" — RISOLTO, non è il server**: la vecchia pagina di cortesia Netsons conteneva un `<META HTTP-EQUIV="REFRESH">` verso `netsons.com/pagina-cortesia.html`; il browser ha **quell'HTML in cache** e riesegue il redirect **senza interpellare il server**. Fix utente: DevTools aperti (F12) + tasto destro sul pulsante ricarica → **"Svuota la cache ed esegui il ricaricamento forzato"**, oppure Network → *Disable cache*.
- ⚠️ **Causa strutturale**: il server **non invia alcun header `Cache-Control`**, quindi i browser applicano caching euristico sull'HTML. **Si ripresenterà ai visitatori** quando la pagina verrà sostituita da WordPress.
- 📄 **Creato `deploy/coming-soon/.htaccess`**: `Cache-Control: no-cache` sull'HTML, `max-age` 1 anno su webp/png/jpg/svg/ico/woff2, `mod_deflate`, `AddType image/webp`, `AddDefaultCharset UTF-8`, `ErrorDocument 404` → `/index.html`. Tutte le direttive dentro blocchi `<IfModule>` per non rischiare un 500.

### TODO PRIORITARIO
1. **Caricare `deploy/coming-soon/.htaccess` in `public_html`**. ⚠️ È un **file nascosto**: in cPanel File Manager serve *Settings → Show Hidden Files*.
2. **Attivare "Force HTTPS Redirect"** in cPanel → Domains: è **OFF su entrambi i domini** mentre il certificato SSL è **Active** → one-click, così il traffico `http` passa a `https`.
3. **Pulizia server**: `_p2.txt` dentro `/home/thfghgww/mondosegnaletica.it` (cancellabile via FTP con l'account `deploy`) e poi la cartella `mondosegnaletica.it` stessa (da File Manager, dopo aver eliminato l'account FTP `deploy`).
4. **Ricreare account FTP `wpdeploy`** puntato su `public_html` (Log In per primo, Directory per ultima) e aggiornare `remotePath` in `.vscode/sftp.json`.
5. Solo dopo: **migrazione WordPress su staging `shop.mondosegnaletica.it`**; restano aperti **checkout / SMTP / pagine legali** dalla sessione 12.

---

## 🟢 2026-07-27 — Sessione 13 (checkpoint): **CAMBIO DI SCOPO — pagina COMING SOON statica pronta al deploy**. Docroot chiarito, account FTP da rifare.

> Questo blocco **supersede tutti i blocchi sessione 13 sottostanti**: quelli restano solo come storico.

- 🔄 **NUOVO SCOPO**: WordPress **non** va online adesso. Serve solo una **pagina HTML statica "sito in costruzione"** su `mondosegnaletica.it`. Il negozio andrà poi su `mondosegnaletica.it` (dominio principale); **`shop.mondosegnaletica.it` resta come STAGING** per provare la migrazione DB prima della produzione.
- 📁 **Docroot chiarito** (da cPanel > Domains): `mondosegnaletica.it` (Main Domain) → **`/public_html`** ; `shop.mondosegnaletica.it` → `/shop.mondosegnaletica.it`. In File Manager su `/home/thfghgww` esistono **sia `public_html`** (335 byte, oggi 9:09) **sia `mondosegnaletica.it`** (20 byte, oggi 11:12): quest'ultima è stata creata insieme all'account FTP `deploy`, che quindi è **ingabbiato nella cartella sbagliata**.
- 🐞 **Causa del bug**: cPanel **ricompila da solo il campo Directory** ogni volta che si tocca il campo Log In, sovrascrivendo quanto digitato. **Fix**: cancellare `deploy` e **ricreare** l'account (es. `wpdeploy`) compilando il **Log In PER PRIMO** e la **Directory PER ULTIMA** (`public_html`). cPanel non permette di cambiare la directory di un account FTP esistente (solo password e quota) → serve **delete + ricrea**. Marcatore `_p2.txt` lasciato sul server per identificare la cartella sbagliata.
- 🎨 **Design importato e convertito**: progetto Claude Design (`projectId 9198ca9e-72bd-4579-b825-18663fd99390`) importato via DesignSync. Il file `Mondosegnaletica Coming Soon.dc.html` **non era pubblicabile** (documento canvas con runtime proprietario `x-dc`, `sc-if`, binding `{{ }}`, `support.js`, e due artboard affiancati desktop 1440 + mobile 390) → **convertito in pagina standalone responsive in vanilla JS**.
- ✅ **Creato `deploy/coming-soon/`**:
  - `index.html` (15 KB) — layout unico responsive (media query 721px/961px), **countdown al 2026-09-01T09:00:00+02:00**, form che compone `mailto:` verso `mondosegnaleticacoop@gmail.com`, validazione nome+email, stato "inviato" con reset, SEO/OG completi, favicon, `prefers-reduced-motion`, label `sr-only`.
  - `assets/background.webp` (175 KB) + `assets/background-mobile.webp` (47 KB) — convertiti con PIL da `assets/img/background_new.png` (2,3 MB, 1672x941).
  - `assets/favicon.png` (21 KB) — da theme `assets/images/favicon-512.png`.
- 📦 **Pacchetto pronto**: `deploy/mondosegnaletica-coming-soon.zip` (244 KB) — da caricare ed estrarre in `public_html` da cPanel File Manager.
- 🎯 **Design fedele a Sistema Strada**: `#0A0A0A` / `#F5F4F0` / `#FFCC00` / `#C8102E`, Anton + IBM Plex Sans + JetBrains Mono, radius 2px, contatti **+39 340 981 9925** e **Via Carlo Angeloni 360, Lucca**.
- 🔧 **Aggiornato `.vscode/sftp.json`**: `context` ora `deploy/coming-soon` (non più il tema WP). `remotePath` **ancora da correggere** quando l'account FTP sarà rifatto.

### TODO PRIORITARIO
1. **Caricare `deploy/mondosegnaletica-coming-soon.zip` in `public_html`** via cPanel File Manager ed **estrarlo** (sovrascrive la pagina di cortesia Netsons). Verificare `https://mondosegnaletica.it/`.
2. **In alternativa/poi**: cancellare l'account FTP `deploy` e **ricrearlo come `wpdeploy`** (Log In per primo, Directory `public_html` per ultima), poi aggiornare `remotePath` in `.vscode/sftp.json` e sincronizzare da VSCode.
3. **Pulizia server**: rimuovere `_p2.txt` e la cartella `/home/thfghgww/mondosegnaletica.it/` creata per sbaglio.
4. Solo dopo: riprendere il piano WooCommerce usando `shop.mondosegnaletica.it` come **staging** per la prova di migrazione DB.

---

## 🟢 2026-07-27 — Sessione 13 (checkpoint, IN CORSO): **account FTP `deploy@` creato — scrittura OK ma NON è il docroot**. Manca il Document Root reale del dominio.

> Questo blocco **supersede** il blocco sessione 13 sottostante (chroot su account vecchio): quel blocco resta solo come storico.

- ✅ **Creato nuovo account FTP `deploy@mondosegnaletica.it`** (password = stessa stringa già nota, Directory impostata a `public_html`, quota unlimited).
- ✅ **Login FTPS esplicito porta 21 OK** e **scrittura verificata** (upload + delete riusciti).
- 🔴 **MA non è il docroot**: il file caricato nella root dell'account **non è raggiungibile** via `http://mondosegnaletica.it/_probe.txt` → **404**.
- 🔎 **Indizio decisivo**: la directory dell'account è **vuota** e ha **timestamp di creazione odierno** (`Jul 27 11:12`) → **cPanel l'ha creata ex novo**, quindi **`/home/thfghgww/public_html` NON esisteva prima**.
- 🧠 **Ipotesi forte**: su **Netsons Hosting Gestito** il docroot del dominio primario **non è `/home/thfghgww/public_html`** ma un altro path (es. `/home/thfghgww/mondosegnaletica.it/`).
- 🔒 **Account chrooted**: negato `cd` verso `/public_html`, `/home/thfghgww`, `/home/thfghgww/public_html`, `..` → **impossibile esplorare da FTP**.
- ℹ️ `mondosegnaletica.it` serve la **pagina di cortesia Netsons** (meta refresh → `https://www.netsons.com/pagina-cortesia.html`) → **nessun sito installato**.
- 📋 **Dal pannello**: **SSH NON disponibile** (nessuna voce *SSH Access* / *Terminal* in Security o Advanced) · **Databases 0/10** (nessun DB creato) · **Disk Usage 97.93 MB** · **FTP Accounts 2/10** · disponibili **installer WordPress "Il tuo sito con 1 click!"** e tool **"Netsons Site Transfer"**.
- 🔑 **Password esposta**: l'utente ha **deciso di NON cambiarla** — scelta sua, **non riproporre**.

### ⛔ SERVE DALL'UTENTE (blocca tutto)
Screenshot di **cPanel → Domains** (mostra il **Document Root reale** del dominio) **oppure** **File Manager** su `/home/thfghgww` per identificare il docroot vero.

### TODO PRIORITARIO
1. **Identificare il Document Root reale** del dominio (cPanel → Domains, o File Manager su `/home/thfghgww`).
2. **Ricreare l'account FTP** puntando la Directory su quel path.
3. **Aggiornare `.vscode/sftp.json`**: utente `deploy@mondosegnaletica.it` + `remotePath` corretto.
4. Riprovare la **probe HTTP** (`_probe.txt`) per confermare che si scrive davvero nel docroot.
5. Poi: **installare WordPress** (installer 1-click o upload manuale) + creare DB (0/10 usati) + primo upload del tema.

---

## 🟡 2026-07-27 — Sessione 13 (blocco storico, SUPERATO): **collegamento VSCode ↔ hosting Netsons — test connessione ESEGUITO**. Connessione OK ma **BLOCCATA da chroot**: serve azione dell'utente su cPanel.

- **Obiettivo**: collegare VSCode (WSL) all'hosting **Netsons cPanel** per **mondosegnaletica.it**. DNS già puntato a `89.40.173.35`. Installata estensione VSCode **SFTP** (`Natizyskunk.sftp`) dentro WSL.
- ✅ **FTP FUNZIONA**: account `mondosegnaletica_ftp@mondosegnaletica.it` via **FTPS esplicito porta 21** — login OK e **scrittura verificata** (upload + rilettura + delete di un file di test tutti riusciti). Il cert TLS **non combacia col nome host** → obbligatorio `rejectUnauthorized: false`.
- 🔴 **BLOCCO PRINCIPALE**: l'account FTP è **chrooted** (`230 OK. Current restricted directory is /`) su una directory **vuota** (contiene solo `.ftpquota`) e **non può raggiungere `/public_html`** → `Server denied you to change to the given directory`.
- 🔴 **Utente principale cPanel `thfghgww` → `530 Access denied`**: la password del cPanel è **diversa** da quella dell'account FTP.
- 🔴 **SSH NON attivo**: porte `22`, `2222`, `2223`, `22222`, `21098` tutte chiuse/filtrate su `89.40.173.35`.
- ℹ️ `mondosegnaletica.it` risponde **HTTP 200** con pagina placeholder "Redirect..." (Apache) → **nessun WordPress online**.
- **Creato**: `/var/www/Mondosegnaletica_store/.vscode/sftp.json` (`protocol: ftp`, `secure: true`, `uploadOnSave: false`, `context: public/wp-content/themes/mondosegnaletica`, `remotePath: "/"` **provvisorio**). `.vscode/` è già gitignorato → la password **non finisce su git**.
- **NON ancora fatto**: upload reale, migrazione DB, search-replace URL.
- **Working tree**: `tools/import-listini/out/epanza_proposte.json` e `out/epanza_figure_html.json` non committati = **residuo sessione 12**, non toccati qui.

### ⛔ SERVE DALL'UTENTE (blocca tutto il resto)
- **Password cPanel dell'utente `thfghgww`** — **OPPURE** —
- **Ripuntare la home dell'account FTP su `/public_html`** da *cPanel → Account FTP → Cambia directory*.
- Per **SSH**: attivarlo da cPanel → SSH Access, oppure aprire **ticket Netsons**.

### 🔴 NOTA SICUREZZA
La **password FTP è stata esposta in chat** → **ruotarla a fine setup** da cPanel. (Si aggiunge alla API key Stitch esposta il 2026-05-24, ancora da ruotare.)

### TODO PRIORITARIO
1. **Sbloccare l'accesso a `/public_html`**: chiedere all'utente la password cPanel `thfghgww` **oppure** far ripuntare la home dell'account FTP.
2. Aggiornare `remotePath` in `.vscode/sftp.json` da `"/"` al path reale (`/public_html/wp-content/themes/mondosegnaletica`) appena sbloccato.
3. Attivare **SSH** da cPanel (o ticket Netsons) → migrare da FTPS a **SFTP su SSH**, più sicuro e più veloce.
4. Primo **upload reale** del tema, poi migrazione DB WordPress + search-replace URL.
5. **Ruotare la password FTP** e la API key Stitch.

---

## 🟡 2026-07-13 — Sessione 12 (CHIUSA): **il pozzo epanza è esaurito**. 143 su 146 = ~98% di tutto il prendibile. Codice **committato e pushato**, working tree **pulito**.

### 🔌 DOPO IL RIAVVIO — PRIMO COMANDO

> Il PC è stato riavviato a fine sessione 12: **lo scraper detached è MORTO**, ed era ancora nella **fase di sondaggio** → **non aveva scaricato NESSUNA immagine** (`epanza-img/` è **vuota**). Va **rilanciato**.
> È **IDEMPOTENTE**: la cache degli H1 (`out/epanza_figure_html.json`) e le immagini già su disco **non vengono riscaricate** → rilanciarlo è **sempre sicuro**.

```bash
cd /var/www/Mondosegnaletica_store/tools/import-listini
setsid nohup python3 -u scrape_epanza.py --sonda-html --scarica > scrape_epanza.log 2>&1 &
```

Dura **~8 minuti** (~470 richieste a **1 req/s** — il sito è di qualcun altro, non alzare il rate).
Segui con: `tail -f tools/import-listini/scrape_epanza.log`

**POI, e SOLO poi:**
1. 👁️ **GUARDARE A OCCHIO 3-4 immagini in `epanza-img/`**: sono davvero **FOTOGRAFIE** dei cartelli, o `og:image` ha restituito **placeholder/logo**? **143 placeholder sarebbero PEGGIO dei 143 disegni attuali** → questo è un **BLOCCO**, non un'opzione.
2. `wp eval-file tools/import-listini/apply_epanza.php dry-run`
3. Poi **senza** `dry-run` (apply vero).
4. Verificare la **resa sul sito**: le foto devono stare meglio dei disegni dentro le card scure.

---

> **RETTIFICA DEFINITIVA della chiusura della sessione 11.** L'ipotesi "il codice figura sta nel titolo della pagina epanza, non solo nell'URL → la copertura sale ben oltre 143" è **FALSA**. Misurata, non assunta.

- **Sondaggio random, 25 schede epanza senza figura nell'URL → 0/25** hanno un codice figura nel contenuto. Non sono cartelli: sono **scarpe antinfortunistiche, guanti, tute, gilet U-Power, DPI**. Epanza vende soprattutto **altro**.
- **Sondaggio mirato, 40 schede che dal nome *sembrano* cartelli → 1/40.** Le altre 39 sono **PANNELLI INTEGRATIVI**: il Codice della Strada li numera per **MODELLO** (mod. 3/d, mod. 6/g), **non per figura**. Il codice non "manca": **non gli spetta**.
- **NUMERO DEFINITIVO** (dal log dello scraper): epanza ha **146 CODICI FIGURA DISTINTI** in tutto il catalogo (2.128 schede, 240 con fig nell'URL → si riducono a **146 codici unici**). Noi ne agganciamo già **143** = **~98% di tutto ciò che epanza può darci**. Nostri: **1.236** prodotti, **1.036** con figura, **494** codici distinti.

> 🔴 **IL POZZO EPANZA È ESAURITO. Non cercare altre leve lì dentro: non ce ne sono.**
> **Conseguenza strategica:** le immagini per gli altri **~350 codici figura** NON verranno da epanza. Vanno **chieste al fornitore** (già in `ANOMALIE.md` punto 1) o prese da **un'altra fonte**.

**Codice scritto (COMMITTATO E PUSHATO su `main` — working tree pulito):**
- `scrape_epanza.py`: nuovo **`--sonda-html`** (legge la figura dall'**H1** delle 170 schede sospette; cache in `out/epanza_figure_html.json` — il `''` in cache significa *"ho già guardato, non c'è"*). **Fix**: se l'immagine è già su disco, ora registra comunque `x['file']` nella proposta — senza, al secondo run l'apply non la trovava. Docstring aggiornata col **terzo errore pagato**.
- **Salvataggio INCREMENTALE della cache H1 (ogni 20 schede)**, aggiunto proprio perché un riavvio/kill non buttasse via il lavoro di sondaggio già fatto. È la ragione per cui il rilancio dopo il riavvio non riparte da zero.
- **`apply_epanza.php` — NUOVO.** Applica le foto a Woo. Idempotente via meta **`_ms_epanza_file`**, **deliberatamente diverso** da `_ms_figura_file`: se riusasse quel meta, `apply_images.php` al giro dopo **rimetterebbe il disegno di listino SOPRA la fotografia**.
- `apply_images.php`: **paletto** — se il prodotto ha `_ms_epanza_file`, non ci rimette sopra il disegno.

**~~IN CORSO alla chiusura~~ → MORTO COL RIAVVIO.** Lo scraper detached (`setsid`, PID 3635165) è stato ucciso dal riavvio del PC **mentre era ancora in fase di sondaggio**: `epanza-img/` è **vuota**, **zero immagini scaricate**. → vedi **🔌 DOPO IL RIAVVIO** in cima al blocco.

### TODO PRIORITARIO — primo passo della prossima sessione
0. **Rilanciare lo scraper** (comando in cima al blocco). ~8 min. È idempotente.
1. `tail -f tools/import-listini/scrape_epanza.log` → verifica che finisca e **quante** immagini ha preso.
2. 👁️ **GUARDARE A OCCHIO 3-4 immagini in `epanza-img/`**: sono davvero **fotografie** dei cartelli, o `og:image` ha restituito **placeholder/logo**? **Non applicare niente** prima di averlo verificato con gli occhi.
3. `wp eval-file tools/import-listini/apply_epanza.php dry-run` → poi **senza** dry-run.
4. Verificare la **resa sul sito**: le foto devono stare meglio dei disegni dentro le card scure.
5. ~~Committare~~ → **già fatto**: commit pushato su `main`, working tree pulito, niente in sospeso.

### 🔴 RESTA APERTO — prioritario subito dopo le immagini
- **Checkout NON funziona**: 0 gateway di pagamento, 0 zone di spedizione.
- **SMTP assente** → le richieste di preventivo **si perdono**.
- **Legali incompleti**: no T&C, no cookie banner.
- **Categoria fantasma** catch-all.

---

## 🟢 2026-07-13 — Sessione 11 (CHIUSA): bug CSS di layout, titoli/slug leggibili, pista immagini EPANZA aperta. DEPLOYATO.

Commit pushati: **`c54a208`** · **`7ba9c9d`** · **`a0532a8`** · **`1625fbc`** su `main`. Working tree pulito.

- **Griglia catalogo attaccata → token CSS orfani.** `--space-5` e `--space-10` **non esistono** nella scala: `gap: var(--space-5)` era una dichiarazione invalida → gap a 0. Sostituiti con token validi. Nuovo `public/wp-content/themes/mondosegnaletica/scripts/check-tokens.mjs` agganciato a `pnpm build`: **un token orfano ora blocca la build**.
- **Paginazione in colonna.** `paginate_links(type => 'list')` emette `<ul><li>` che nessuno stilava; le classi del tema (`.pagination__item--current/--dots`) **non esistevano nell'HTML** → regole morte. Passato a `'plain'` e stilate le classi vere (`.page-numbers`, `.current`, `.dots`).
- **Prodotti correlati**: da griglia 3 colonne (4 prodotti = 1 orfano a capo) a **carosello**, riusando `carousel.js` già in home. Portati a 8.
- **Nome card tagliato**: `line-clamp` 2 righe con `line-height: 1.15` su Anton rifilava i glifi → 1.3 + `min-height`. Rimossa la **doppia definizione di `.products-grid`** (vinceva solo per ordine di import).
- **TITOLI.** La colonna ARTICOLO del listino (fino a **338 caratteri**) finiva nell'H1. Aggiunto campo **`nome_breve`** in `normalize.py` (max 90 car, accumula i segmenti separati da trattino: tagliare al **primo** trattino dava lo stesso nome a **67 prodotti su 175**). Nuovo **`tools/import-listini/apply_nomi.php`**: accorcia i titoli su Woo e salva la riga completa in descrizione come *"Denominazione a listino"*. **169 titoli accorciati**, max 90 car, idempotente. `link_figures.py` e `import.php` aggiornati a `nome_breve` (senza, `apply_images` **rimetteva i nomi lunghi**).
- **SLUG.** Nuovo **`tools/import-listini/fix_slug.php`**: **363 slug** rigenerati dai titoli brevi (erano 150+ car → ora max 111, media 42, **zero duplicati**). Scrive in **due passate** (parcheggio su `ms-tmp-<id>`, poi slug definitivo) perché altrimenti WordPress accoda `-2` e servono **tre** esecuzioni per convergere. **Idempotenza verificata** sporcando 30 slug in rotazione: una sola passata li ricostruisce identici. ✅ Bug chiuso.

### 🖼️ IMMAGINI — EPANZA È LA PISTA BUONA (pronta, non ancora applicata)

> **RETTIFICA di quanto scritto prima in questo file: il verdetto "EPANZA vicolo cieco" era SBAGLIATO.** Era basato sulla sola categoria 130 (527 prodotti). La **sitemap** dice che il catalogo vero è di **2.128 prodotti**. E **EPANZA è un sito affiliato del cliente**: l'utente ha dato l'**ok esplicito** a prendere le immagini → **nessun vincolo legale**, la nota sui "loro asset" è annullata.

**Perché serve.** Oggi **610 prodotti su 1.236 hanno un'immagine, ma sono i DISEGNI ritagliati dal listino** (pittogrammi al tratto su **fondo bianco**): dentro le card scure del tema si vedono male. **L'utente vuole sostituirle con FOTOGRAFIE vere.**

**Stato del lavoro** — `tools/import-listini/scrape_epanza.py` **riscritto**:
- legge la **sitemap** (2.128 prodotti), aggancia per **CODICE FIGURA letto dall'URL** (`…-fig-412a-…`);
- scarica **solo** le immagini agganciate, **1 richiesta/secondo**, in `tools/import-listini/epanza-img/` (gitignored).
- **MISURATO: 143** nostri prodotti hanno un codice figura coperto da epanza → **24** oggi senza immagine (prendono la prima), **119** col disegno di listino (**passano alla fotografia**).
- Output: `out/epanza_proposte.json` · URL completi in `out/epanza_urls.txt`.

**LEVA NON ANCORA TIRATA — probabilmente vale molto più di 143.** Il codice figura lo cerco
oggi solo nell'**URL**, e ce l'hanno appena 240 prodotti su 2.128. Ma il titolo della loro
scheda lo contiene comunque (l'H1 verificato dice `… Classe 1 **Fig. 412/a** Lamiera
d'acciaio zincato`): il codice c'è, semplicemente non finisce nello slug. Scaricando le 2.128
schede (35 minuti a una richiesta al secondo) e leggendo il codice **dal contenuto della
pagina**, la copertura può salire di parecchio. È il primo esperimento da fare, prima di
applicare qualsiasi cosa.

**Prossimo passo (è il TODO #1):** `python3 scrape_epanza.py --scarica`, poi scrivere l'**apply** che le carica su Woo — **riusare la logica idempotente di `apply_images.php`**, che registra su ogni prodotto il meta `_ms_figura_file`.

**🪤 TRAPPOLA MISURATA — NON agganciare per somiglianza del NOME.** Il punteggio sulla **copertura** del nostro nome dà **0.90** a *"Lamiera di Ferro 10/10"* contro *"cartello attraversamento tramviario"* → spazzatura. E anche i match "buoni" sono **sbagliati**: *"Gilet Classe 2"* pesca il loro *"Gilet Classe 3"*, *"Paletto Ø 89"* pesca il loro *"Ø 60"*. **Solo codice figura esatto.**

### 🔴 VICOLI CIECHI CONFERMATI — NON RITENTARLI
1. **PART_D** (pagine VER 22-24, `type: "altro"`, 126 figure). `figure_ocr.py` ora **ritaglia da ogni pagina con figure** e `normalize.py` ripesca per codice figura → **+6 ritagli, ZERO immagini prodotto in più**. Le figure di quelle pagine (II 100/102/166) **non sono** quelle dei prodotti censiti lì (II 224-231). Filone esaurito.
2. **200 prodotti senza alcun pittogramma nel listino e senza codice figura** → per quelli **né i ritagli né epanza possono fare nulla**: servono **foto dal fornitore** oppure un **aggancio manuale** su epanza.

### STATO STORE (verificato a fine sessione)
**1.236 prodotti · 36.182 varianti intatti** · **610 immagini** tutte coerenti (0 incompatibili, ma sono disegni su fondo bianco) · titoli entro **90 car** · slug puliti e **idempotenti** · tutte le pagine **200**.

### TODO PRIORITARIO — in ordine
1. **🖼️ IMMAGINI EPANZA — scaricare le 143 e applicarle allo store.** È quello che l'utente vuole **subito**. (Dettaglio nel blocco qui sopra.)
2. **🔴 BLOCCANTI VENDITA — il checkout oggi NON funziona**: zero **gateway di pagamento** attivi, zero **zone di spedizione**, **indirizzo negozio vuoto**.
3. **SMTP assente**: i form preventivo/contatti usano `wp_mail` nudo → in produzione le richieste di preventivo (**165 prodotti vendono solo a preventivo**) **si perdono**.
4. **LEGALI**: Privacy Policy in bozza, **Termini e Condizioni inesistenti**, **cookie banner assente**, doppione "Refund and Returns Policy" vs "Spedizioni e Resi".
5. **CATEGORIA FANTASMA**: "Segnaletica Stradale, Cantieristica e Accessori" contiene **tutti i 1.236 prodotti** (catch-all dell'import) + **3 categorie vuote** → inquinano shop e filtri. Da eliminare.
6. **MENU inesistente**: la navigazione è un **array hardcoded** in `nav-primary.php` → il cliente non può modificarla da admin.
7. **PDP**: i meta `_ms_specs` / `_ms_downloads` / `_ms_fig_cds` / `_ms_qty_discounts` **non sono popolati da nessuno script** → la tabella specifiche cade sempre sul fallback `wc_display_product_attributes`, badge FIG. e certificazioni **non compaiono mai**.
8. **Selettore varianti assente sui prodotti senza prezzo**: in `single-product.php`, `woocommerce_template_single_add_to_cart()` è **dentro `if ($ha_prezzo)`** → se `get_price()` è vuoto il menu non viene mai emesso.
9. **GSAP/Lenis mai installati**: animazioni hero ferme alla fase 1 (vanilla JS).
10. **SKU stampato due volte** (`card.php` + hook `ms_show_sku_in_loop`) · **"Pagina di esempio"** WP ancora pubblicata · **WooCommerce 10.7 → 10.9.4** · un prodotto ha slug `inizio` (residuo di parsing) · **Graphify STALE** → `/graphify . --update`.

---

## 🟢 2026-07-12 — Sessione 10: aggancio ritaglio↔figura via OCR locale + 3 bug di fondo corretti. DEPLOYATO.

Ultimo commit pushato: **`1880ea2`** · working tree pulito.

- **LOTTO 6 a vista NON eseguito e DECADUTO.** La lettura delle immagini con un modello **non serve più, mai più** → costo zero. Codici figura e nomi erano **già** in `extract/*.json`: la lettura a vista serviva solo a **riallineare ritaglio↔figura**, perché lo zip per posizione slittava (misurato: sbaglia il **9%**; banco di prova = le 611 letture a vista dei lotti 0-5).
- **Nuovo `tools/import-listini/figure_ocr.py`**: OCR locale gratuito (`rapidocr-onnxruntime`) legge la **didascalia dentro ogni ritaglio**; **assegnamento ottimo cella↔figura per pagina** (Hungarian, `scipy`) su due segnali (codice figura + testo del cartello); soglia **0.55**. **Esattezza misurata 99,4%** (99,7% sulla sola confidenza alta). `verifica_figure_ocr.py` è il test riproducibile contro le 611 letture.
- Caduto il vincolo `celle == figure`: recuperate **14 pagine** che `crop_figures.py` scartava → **+116 figure**.
- **3 bug di fondo corretti**:
  1. `normalize.py` scartava `part_a..part_e` (pagine VER 15-25) perché filtrava il tag dal **nome file** invece che dal campo `pg['tag']` → **185 prodotti e ~9.000 varianti sparivano a ogni rigenerazione**. Aggiunto **guard-rail**: conserva gli SKU che l'estrazione non ricostruisce più, invece di cancellarli in silenzio.
  2. `link_figures.py` agganciava per codice figura **globale**: in ACCESSORI la figura è una **lettera valida solo dentro la pagina** (la "A" di pag. 3 è un segnale di velocità, quella di pag. 6 è una tuta). Ora l'aggancio è per **(listino, pagina, posizione)** via `normalize`.
  3. La colonna **FIG. a volte contiene un elenco** ("E - E1", "466 / 467"): 129 righe non trovavano la figura. Aggiunta `elenco_figure()` che **non spezza i codici veri con barra** (`1/A`, `60/B`, `309/P`), e `scegli_figura()` disambigua due figure con lo stesso codice sulla stessa pagina confrontando il nome figura con l'articolo della riga.
- Nuovo **`purge_immagini.php`**: rimuove le immagini che la mappa non giustifica (residui degli agganci vecchi).

### STATO STORE (verificato)
- **1.236 prodotti · 36.182 varianti INTATTI**
- **610 prodotti con immagine valida** (erano ~300)
- Zero riferimenti rotti · zero nomi corrotti · carrello + placeholder OK · tutte le pagine **200**

### TODO PRIORITARIO
1. **IMMAGINI RESTANTI — 626 prodotti senza foto**, due gruppi distinti:
   - **(A) 200 prodotti NON hanno alcun pittogramma nel listino** (77 Cantieristica · 72 Verticale · 29 Dissuasori · 14 Orizzontale · 8 Coni) → **servono foto dal fornitore**: richiesta da girare al cliente.
   - **(B) ~426 hanno codice figura ma nessun ritaglio**, per tre cause tecniche risolvibili:
     - pagine **VER 26-28 ASSENTI da `extract/`** (mai committate) → 33 SKU non più ricostruibili, **vanno riestratte**;
     - `part_d.json` marcato `type: "altro"` invece di `listino` → **126 figure inutilizzate**;
     - `CAN_030` senza celle vettoriali.
2. **Anomalie fornitore aggiornate** in `tools/import-listini/ANOMALIE.md` (ora **10 voci**): aggiunte CAN pag. 32 (tre codici in una cella → SKU e nome degradati) e CAN pag. 32 (due cartelli diversi entrambi `FIG. A`).
3. **Graphify è STALE** → rilanciare `/graphify . --update`.

### Note di ambiente
- **venv** con `pymupdf` · `pillow` · `numpy` · `scipy` · `rapidocr-onnxruntime` in `<scratchpad>/venv` — **temporaneo, va ricreato** nelle sessioni future.
- `naming/ocr_cache.json` e `crops-raw.old/` sono **gitignored**. La **prima** passata di `figure_ocr.py` costa **~9 min** di OCR; poi la cache la rende istantanea.

---

## ⛔ ERRORE DA NON RIPETERE (letto prima di tutto)

**Nella sessione 9 ho esaurito i crediti lanciando 7 subagenti IN PARALLELO, ognuno con ~105 immagini da leggere (700+ letture immagine in un colpo).** Le immagini costano moltissimo. **Ora è irrilevante**: l'OCR locale ha eliminato del tutto la lettura a vista.

Gerry monitora il **contesto della finestra** — **NON i crediti di sessione**. Sono due cose diverse: il contesto può essere vuoto mentre i crediti sono finiti.

---

## ✅ LO STORE FUNZIONA

- Sito: **http://mondosegnaletica.ddev.site** — admin / `Admin1234!`
- **1.236 prodotti · 36.182 variazioni · 0 errori**
- 674 variabili · 405 semplici · **165 senza prezzo** → CTA "Prezzo su richiesta" + preventivo
- Categorie: Verticale 518 · Dissuasori&Accessori 307 · Cantieristica 246 · Orizzontale 68 · Coni&Transenne 63 · Delineatori 34
- Attributi varianti: **Dimensione × Materiale × Classe rifrangenza × Fissaggio × Versione**
- Verificato end-to-end: PDP con menu varianti popolati · acquisto reale 3 × € 11,00 = € 33,00 + IVA 22% € 7,26 = **€ 40,26** · checkout con campo P.IVA · zero errori PHP.

---

## 📞 DA CHIEDERE AL FORNITORE / CLIENTE (non risolvibile da noi)
Dettaglio completo (10 voci) in `tools/import-listini/ANOMALIE.md`.

1. **200 prodotti senza alcun pittogramma nel listino** → servono **foto dal fornitore**.
2. **PDF LISTINO CANTIERISTICA con celle prezzo VUOTE alla fonte** (verificato a 400 DPI): pagg. 27-38 = 107 righe ma solo 19 prezzi; pag. 132 marcata "PREZZI NETTI" con colonna interamente bianca. **Esiste una versione con i prezzi?**
3. Due articoli diversi con lo **stesso codice**: `1200PRCPB0001` ≡ `1200PRCPG0001`
4. Codice `1200TR0010100` quotato **€ 1,20** a pag. 064 e **€ 1,50** a pagg. 069/071
5. ~190 articoli con **"CHIEDERE PREVENTIVO"** al posto del prezzo
6. CAN pag. 32: **tre codici in una sola cella** → SKU e nome degradati
7. CAN pag. 32: **due cartelli diversi entrambi marcati `FIG. A`**

---

## 🔒 DECISIONI UTENTE GIÀ PRESE (non ridiscutere)
- Prezzi **IVA ESCLUSA**. **NESSUNO sconto quantità** (rimossi quelli finti che il tema applicava di default).
- **Un prodotto per cartello (FIGURA)**, varianti Dimensione × Materiale × Classe.
- **I listini sono la FONTE UNICA**: i 215 prodotti del vecchio import sono archiviati in **DRAFT** (reversibili).
- **Homepage allineamento Stitch: NON prioritario**, rimandato.

---

## 🪤 TRAPPOLE TECNICHE (già pagate — non ricascarci)
- `wp eval-file` **non accetta il flag `--`**, solo argomenti posizionali. E **non ammette `declare(strict_types)`**.
- WooCommerce vuole gli **ID** dei termini in `WC_Product_Attribute::set_options()`, ma lo **SLUG** nel meta `attribute_pa_*` della variazione. Passando gli slug a entrambi → termini duplicati e **menu varianti VUOTI**.
- `wipe.php` deve cancellare **anche i TERMINI attributo**, non solo i post.
- Il filtro `woocommerce_placeholder_img` riceve `$size` anche come **ARRAY**: tipizzarlo `string` manda il carrello in **fatal error** su ogni prodotto senza immagine.
- L'aggancio ritaglio→figura **per POSIZIONE è inaffidabile** (sbaglia il 9%): va fatto per **codice letto dalla didascalia dentro l'immagine** (OCR) + assegnamento ottimo per pagina.
- Il codice figura **NON è globale**: è valido solo **dentro la pagina** del listino.
- Filtrare le pagine dal **nome file** invece che dal campo `pg['tag']` fa **sparire prodotti in silenzio**.

---

## 📁 FILE CHIAVE

```
tools/import-listini/
├── extract/              JSON estratti dalle pagine (⚠️ VER 26-28 MANCANTI)
├── normalize.py          normalizzazione → out/prodotti.json (+ guard-rail SKU)
├── import.php            import WooCommerce
├── wipe.php              reset (post + termini attributo)
├── figure_ocr.py         ritaglio celle + OCR locale + assegnamento Hungarian ⭐
├── verifica_figure_ocr.py test riproducibile vs le 611 letture a vista
├── link_figures.py       aggancio figura → prodotto (listino, pagina, posizione)
├── apply_images.php      applica immagini+nomi a WC (idempotente)
├── purge_immagini.php    rimuove immagini non giustificate dalla mappa
├── crops-raw/            ritagli sorgente (rigenerati da figure_ocr.py)
├── naming/               figure_ocr.json · ocr_cache.json (gitignored)
├── out/prodotti.json     dataset finale
├── SPEC.md
└── ANOMALIE.md           10 anomalie da girare al fornitore
```

- Backup DB pre-import: `backups/pre-import-listini-20260712-1019.sql.gz`
- PDF sorgente: `Prodotti/` (gitignored, 46 MB)
