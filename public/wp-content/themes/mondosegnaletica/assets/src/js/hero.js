/**
 * hero.js — Sistema Strada v2
 * Animazioni homepage hero: char-by-char headline, fadein progressivo, header trasparente.
 * Vanilla JS — zero dipendenze. GSAP integrato in fase 2.
 */

// Costanti fine-tuning
const CHAR_DELAY_MS    = 28;   // ms tra un char e il successivo
const INITIAL_DELAY_MS = 200;  // ms prima che parta la headline

document.addEventListener('DOMContentLoaded', () => {

  // ─── 1. Char animation per .js-char-animate ───────────────────────────────
  const heading = document.querySelector('.js-char-animate');

  if (heading) {
    const rawHTML = heading.innerHTML;

    // Split su <br> (preserva tag inline come <em>) — ogni elemento è una riga
    const lines = rawHTML.split(/<br\s*\/?>/i);

    // Conta i char raw di ogni linea (esclusi tag HTML, inclusi spazi)
    const lineCharCounts = lines.map(line => line.replace(/<[^>]+>/g, '').length);

    /**
     * Wrappa il testo grezzo in .word-span + .char-span.
     * Ogni parola è avvolta in <span class="word" style="white-space:nowrap">
     * così il browser può fare word-wrap TRA parole, mai a metà parola.
     * Gli spazi diventano <span class="char char--space"> tra i word-span.
     *
     * @param {string} text      - testo grezzo (no HTML)
     * @param {number} charOffset - offset globale per calcolo delay
     * @param {boolean} isEm     - se true, non aggiunge ulteriore wrapper (lo fa il chiamante)
     * @returns {{ html: string, charCount: number }}
     */
    const wrapWords = (text, charOffset, isEm = false) => {
      const words = text.split(' ');
      let offset = charOffset;
      const parts = [];

      words.forEach((word, wi) => {
        if (word.length === 0) return;

        // Costruisci i char-span per questa parola
        const charSpans = [...word].map((char, ci) => {
          const delay = INITIAL_DELAY_MS + (offset + ci) * CHAR_DELAY_MS;
          return `<span class="char" style="transition-delay:${delay}ms">${char}</span>`;
        }).join('');

        parts.push(`<span class="word" style="white-space:nowrap">${charSpans}</span>`);
        offset += word.length;

        // Spazio tra parole (non dopo l'ultima)
        if (wi < words.length - 1) {
          const delay = INITIAL_DELAY_MS + offset * CHAR_DELAY_MS;
          parts.push(`<span class="char char--space" style="transition-delay:${delay}ms">&nbsp;</span>`);
          offset += 1;
        }
      });

      return { html: parts.join(''), charCount: offset - charOffset };
    };

    /**
     * Processa una riga HTML che può contenere tag <em>.
     * Preserva <em> come wrapper semantico ma wrappa il contenuto con wrapWords.
     */
    const processLine = (lineHTML, charOffset) => {
      let offset = charOffset;
      // Divide la riga in segmenti: testo puro o contenuto <em>...</em>
      const result = lineHTML.replace(
        /(<em>)([\s\S]*?)(<\/em>)|([^<]+)/g,
        (match, openEm, emInner, closeEm, plainText) => {
          if (plainText !== undefined) {
            // Testo puro fuori da <em>
            const { html, charCount } = wrapWords(plainText, offset);
            offset += charCount;
            return html;
          } else {
            // Contenuto <em>: wrappa i char dentro, mantieni il tag <em>
            const { html, charCount } = wrapWords(emInner, offset);
            offset += charCount;
            return `<em>${html}</em>`;
          }
        }
      );
      return { html: result, charCount: offset - charOffset };
    };

    // Ricostruisci innerHTML linea per linea
    let globalOffset = 0;
    const processedLines = lines.map((line, idx) => {
      const { html, charCount } = processLine(line.trim(), globalOffset);
      globalOffset += lineCharCounts[idx];
      return idx < lines.length - 1 ? html + '<br>' : html;
    });

    heading.innerHTML = processedLines.join('');

    // Trigger: aggiungi is-visible → CSS fa scattare la transition
    setTimeout(() => {
      heading.querySelectorAll('.char').forEach(span => {
        span.classList.add('is-visible');
      });
    }, INITIAL_DELAY_MS);
  }


  // ─── 2. FadeIn progressivo per .js-fadein ─────────────────────────────────
  document.querySelectorAll('.js-fadein').forEach(el => {
    const delay = parseInt(el.dataset.delay || '0', 10);
    setTimeout(() => {
      el.classList.add('is-visible');
    }, delay);
  });


  // ─── 3. Header trasparente sull'hero (IntersectionObserver) ───────────────
  const hero = document.getElementById('hero');

  if (hero) {
    const observer = new IntersectionObserver(
      ([entry]) => {
        document.documentElement.classList.toggle('hero-in-view', entry.isIntersecting);
      },
      { threshold: 0.05 }
    );
    observer.observe(hero);
  }


  // ─── 4. Parallax bg image via transform ───────────────────────────────────
  // L'immagine è scalata 1.12 in CSS: il translate resta dentro quel margine,
  // così non si scopre mai una banda vuota sotto la hero.
  const heroBgImg = document.querySelector('.hero__bg-img');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (heroBgImg && hero && !reduceMotion) {
    const SCALE = 1.12;
    const SHIFT = 5; // % dell'altezza hero, entro il 6% di margine per lato
    let rafId = null;

    const updateParallax = () => {
      const rect     = hero.getBoundingClientRect();
      // 0 = hero appena entrata dal basso, 1 = hero appena uscita dall'alto
      const progress = Math.max(0, Math.min(1, -rect.top / rect.height));
      const shift    = -SHIFT + (SHIFT * 2) * progress;
      heroBgImg.style.transform = `translate3d(0, ${shift.toFixed(2)}%, 0) scale(${SCALE})`;
      rafId = null;
    };

    const onScroll = () => {
      if (rafId === null) rafId = requestAnimationFrame(updateParallax);
    };

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
    updateParallax();
  }


  // ─── 5. Reduced motion: disabilita animazioni char se prefers-reduced-motion
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (prefersReduced && heading) {
    // Rende visibili tutti i char immediatamente, senza animazione
    heading.querySelectorAll('.char').forEach(span => {
      span.style.transitionDuration = '0ms';
      span.classList.add('is-visible');
    });
    document.querySelectorAll('.js-fadein').forEach(el => {
      el.style.transitionDuration = '0ms';
      el.classList.add('is-visible');
    });
  }

});
