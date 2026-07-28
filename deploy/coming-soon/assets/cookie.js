/* ==================== CONSENSO COOKIE ====================
   Unico punto di verità, condiviso da index.html e privacy.html.

   Il markup della barra e del pannello viene iniettato da qui, non duplicato
   nelle pagine: due copie di un testo che ha valore legale divergerebbero alla
   prima modifica. Nessun problema di degrado senza JavaScript — un banner di
   consenso senza JS non potrebbe comunque salvare la scelta.

   Oggi il sito non installa cookie di profilazione: le categorie oltre ai
   "necessari" restano spente e sono predisposte per il futuro e-commerce.
   La scelta viene salvata in un cookie di prima parte per 180 giorni, come
   raccomandato dal Garante, e riesposta come `window.msConsenso` più evento
   `ms:consenso`, così gli script futuri (Woo, statistiche) potranno agganciarsi.

   Richiede assets/cookie.css e le variabili di tema nel :root della pagina. */
(function () {
  "use strict";

  var CHIAVE = "ms_consenso";
  var DURATA = 60 * 60 * 24 * 180; // 180 giorni
  var CATEGORIE = ["preferenze", "statistici", "marketing"];

  /* ---- markup ----
     Sulla pagina della privacy il rimando all'informativa non diventa un link
     a se stessa: si limita a indicare la pagina corrente. */
  var suPrivacy = /(^|\/)privacy\.html$/i.test(location.pathname);
  var rimando = suPrivacy
    ? "Tutti i dettagli sono in questa pagina."
    : 'Tutti i dettagli nella <a href="privacy.html">privacy policy</a>.';

  document.body.insertAdjacentHTML("beforeend",
    '<div class="ck" id="ck" role="region" aria-label="Informativa cookie">' +
      '<div class="ck__in">' +
        '<p class="ck__txt">' +
          '<strong>Cookie e dati di navigazione</strong>' +
          'Questo sito usa solo cookie tecnici, necessari a farlo funzionare e a inviare ' +
          'il modulo di contatto: nessuna profilazione e nessun tracciamento pubblicitario. ' +
          'Le altre categorie sono predisposte per il prossimo e-commerce e restano spente ' +
          'finché non le attivi tu. ' + rimando +
        '</p>' +
        '<div class="ck__btns">' +
          '<button type="button" class="ck__b" id="ck-no">Rifiuta</button>' +
          '<button type="button" class="ck__b" id="ck-pers">Personalizza</button>' +
          '<button type="button" class="ck__b ck__b--si" id="ck-si">Accetta tutti</button>' +
        '</div>' +
      '</div>' +
    '</div>' +

    '<div class="ckm" id="ckm" role="dialog" aria-modal="true" aria-labelledby="ckm-t">' +
      '<div class="ckm__box">' +
        '<p class="ckm__eyebrow">Preferenze cookie</p>' +
        '<h2 class="ckm__t" id="ckm-t">Scegli cosa attivare</h2>' +
        '<p class="ckm__sub">Puoi cambiare idea quando vuoi: la voce <em>Preferenze cookie</em> ' +
          'in fondo alla pagina riapre questo pannello. La scelta resta valida sei mesi.</p>' +

        '<div class="ckc">' +
          '<div class="ckc__t">' +
            '<p class="ckc__n">Necessari</p>' +
            '<p class="ckc__d">Fanno funzionare il sito: invio del modulo di contatto, protezione ' +
              'dagli abusi, memoria di questa stessa scelta sui cookie. Senza di loro il sito non ' +
              'può funzionare, quindi non sono disattivabili.</p>' +
            '<p class="ckc__stato">Sempre attivi</p>' +
          '</div>' +
          '<button type="button" class="sw" role="switch" aria-checked="true" ' +
            'aria-label="Cookie necessari, sempre attivi" disabled></button>' +
        '</div>' +

        '<div class="ckc">' +
          '<div class="ckc__t">' +
            '<p class="ckc__n">Preferenze</p>' +
            '<p class="ckc__d">Ricordano le impostazioni che scegli — per esempio i dati di consegna ' +
              'o le opzioni di visualizzazione del catalogo — così non devi reinserirle a ogni visita.</p>' +
            '<p class="ckc__stato">Disattivati</p>' +
          '</div>' +
          '<button type="button" class="sw" role="switch" aria-checked="false" ' +
            'aria-label="Cookie di preferenze" data-cat="preferenze"></button>' +
        '</div>' +

        '<div class="ckc">' +
          '<div class="ckc__t">' +
            '<p class="ckc__n">Statistici</p>' +
            '<p class="ckc__d">Misurano in forma aggregata quali pagine vengono viste e da dove ' +
              'arrivano i visitatori, per capire cosa migliorare. Non identificano la singola persona.</p>' +
            '<p class="ckc__stato">Disattivati</p>' +
          '</div>' +
          '<button type="button" class="sw" role="switch" aria-checked="false" ' +
            'aria-label="Cookie statistici" data-cat="statistici"></button>' +
        '</div>' +

        '<div class="ckc">' +
          '<div class="ckc__t">' +
            '<p class="ckc__n">Marketing</p>' +
            '<p class="ckc__d">Servono a mostrare annunci pertinenti su altri siti e a misurarne ' +
              'il rendimento. Oggi non ne usiamo nessuno.</p>' +
            '<p class="ckc__stato">Disattivati</p>' +
          '</div>' +
          '<button type="button" class="sw" role="switch" aria-checked="false" ' +
            'aria-label="Cookie di marketing" data-cat="marketing"></button>' +
        '</div>' +

        '<div class="ckm__ft">' +
          '<button type="button" class="ck__b" id="ckm-chiudi">Annulla</button>' +
          '<button type="button" class="ck__b" id="ckm-tutti">Accetta tutti</button>' +
          '<button type="button" class="ck__b ck__b--si" id="ckm-salva">Salva le preferenze</button>' +
        '</div>' +
      '</div>' +
    '</div>'
  );

  var barra   = document.getElementById("ck");
  var modal   = document.getElementById("ckm");
  var box     = modal.querySelector(".ckm__box");
  var riapri  = document.getElementById("riapri-cookie");
  var ultimoFocus = null;

  function leggi() {
    var m = document.cookie.match(new RegExp("(?:^|; )" + CHIAVE + "=([^;]*)"));
    if (!m) { return null; }
    try { return JSON.parse(decodeURIComponent(m[1])); } catch (e) { return null; }
  }

  function scrivi(stato) {
    stato.necessari = true;
    stato.data = new Date().toISOString().slice(0, 10);
    document.cookie = CHIAVE + "=" + encodeURIComponent(JSON.stringify(stato)) +
      ";path=/;max-age=" + DURATA + ";SameSite=Lax" +
      (location.protocol === "https:" ? ";Secure" : "");
    applica(stato);
  }

  function applica(stato) {
    window.msConsenso = stato;
    document.dispatchEvent(new CustomEvent("ms:consenso", { detail: stato }));
  }

  function tutte(valore) {
    var s = { necessari: true };
    CATEGORIE.forEach(function (c) { s[c] = valore; });
    return s;
  }

  /* ---- interruttori del pannello ---- */
  var switches = Array.prototype.slice.call(modal.querySelectorAll(".sw"));

  function impostaSwitch(sw, acceso) {
    sw.setAttribute("aria-checked", acceso ? "true" : "false");
    var stato = sw.parentNode.querySelector(".ckc__stato");
    if (stato && !sw.disabled) { stato.textContent = acceso ? "Attivi" : "Disattivati"; }
  }

  switches.forEach(function (sw) {
    if (sw.disabled) { return; }
    sw.addEventListener("click", function () {
      impostaSwitch(sw, sw.getAttribute("aria-checked") !== "true");
    });
  });

  function riempiPannello(stato) {
    switches.forEach(function (sw) {
      if (sw.disabled) { return; }
      impostaSwitch(sw, !!(stato && stato[sw.dataset.cat]));
    });
  }

  function raccogliPannello() {
    var s = { necessari: true };
    switches.forEach(function (sw) {
      if (!sw.disabled) { s[sw.dataset.cat] = sw.getAttribute("aria-checked") === "true"; }
    });
    return s;
  }

  /* ---- apertura / chiusura ---- */
  function mostraBarra(v) {
    if (v) { barra.setAttribute("data-on", ""); } else { barra.removeAttribute("data-on"); }
  }

  function apri() {
    ultimoFocus = document.activeElement;
    riempiPannello(leggi() || tutte(false));
    modal.setAttribute("data-on", "");
    // Il primo bottone del pannello è l'interruttore "necessari", che è disabilitato:
    // va saltato, altrimenti il focus non si sposta e la tastiera resta fuori.
    (box.querySelector('button:not([disabled]), a[href]') || box).focus();
    document.addEventListener("keydown", tasti);
  }

  function chiudi() {
    modal.removeAttribute("data-on");
    document.removeEventListener("keydown", tasti);
    if (ultimoFocus && ultimoFocus.focus) { ultimoFocus.focus(); }
  }

  // Esc chiude, Tab resta dentro al pannello finché è aperto.
  function tasti(e) {
    if (e.key === "Escape") { return chiudi(); }
    if (e.key !== "Tab") { return; }
    var f = box.querySelectorAll('button:not([disabled]), a[href], input, [tabindex]:not([tabindex="-1"])');
    if (!f.length) { return; }
    var primo = f[0], ultimo = f[f.length - 1];
    if (e.shiftKey && document.activeElement === primo) { e.preventDefault(); ultimo.focus(); }
    else if (!e.shiftKey && document.activeElement === ultimo) { e.preventDefault(); primo.focus(); }
  }

  function decidi(stato) { scrivi(stato); mostraBarra(false); chiudi(); }

  document.getElementById("ck-si").addEventListener("click", function () { decidi(tutte(true)); });
  document.getElementById("ck-no").addEventListener("click", function () { decidi(tutte(false)); });
  document.getElementById("ck-pers").addEventListener("click", apri);
  document.getElementById("ckm-salva").addEventListener("click", function () { decidi(raccogliPannello()); });
  document.getElementById("ckm-tutti").addEventListener("click", function () { decidi(tutte(true)); });
  document.getElementById("ckm-chiudi").addEventListener("click", chiudi);
  modal.addEventListener("click", function (e) { if (e.target === modal) { chiudi(); } });

  if (riapri) {
    // La barra non si nasconde qui: se l'utente annulla senza decidere, deve restare.
    riapri.addEventListener("click", function (e) { e.preventDefault(); apri(); });
  }

  /* ---- avvio ---- */
  var salvato = leggi();
  if (salvato) { applica(salvato); } else { mostraBarra(true); }
})();
