#!/usr/bin/env node
/**
 * Blocca i token CSS usati ma mai dichiarati.
 *
 * Un `var(--space-5)` che non esiste non è un errore rumoroso: il browser scarta l'intera
 * dichiarazione e la proprietà torna al valore iniziale. `gap: var(--space-5)` diventa
 * `gap: normal`, cioè zero, e le card della griglia finiscono a toccarsi. Nessun avviso,
 * né in build né in console: il bug è arrivato in produzione ed è rimasto.
 *
 * Qui si confronta ogni `var(--x)` con i token dichiarati, e la build si ferma.
 *
 *   node scripts/check-tokens.mjs
 */
import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';

const ROOT = new URL('../assets/src/css', import.meta.url).pathname;

function sorgenti(dir) {
	return readdirSync(dir).flatMap((n) => {
		const p = join(dir, n);
		return statSync(p).isDirectory() ? sorgenti(p) : p.endsWith('.css') ? [p] : [];
	});
}

const files = sorgenti(ROOT);
const dichiarati = new Set();
const usati = new Map(); // nome -> [{file, riga}]

for (const f of files) {
	readFileSync(f, 'utf8').split('\n').forEach((linea, i) => {
		// uso:  var(--nome)  — con fallback, var(--nome, 16px), il crollo non avviene: si ignora
		for (const m of linea.matchAll(/var\(\s*(--[\w-]+)\s*\)/g)) {
			if (!usati.has(m[1])) usati.set(m[1], []);
			usati.get(m[1]).push({ file: relative(ROOT, f), riga: i + 1 });
		}

		// dichiarazione:  --nome: valore
		// Prima si tolgono i var(--x), altrimenti verrebbero contati come dichiarazioni.
		// E si cercano OVUNQUE nella riga, non solo in testa: la scala sta tutta su poche
		// righe, più token per riga ("--space-1: 4px; --space-2: 8px;").
		const senzaVar = linea.replace(/var\([^)]*\)/g, '');
		for (const m of senzaVar.matchAll(/(--[\w-]+)\s*:/g)) dichiarati.add(m[1]);
	});
}

const orfani = [...usati.entries()].filter(([nome]) => !dichiarati.has(nome));

if (orfani.length === 0) {
	console.log(`✓ token CSS: ${dichiarati.size} dichiarati, nessun uso orfano`);
	process.exit(0);
}

console.error('\n✗ TOKEN CSS USATI MA MAI DICHIARATI\n');
console.error('  Il browser scarta la dichiarazione che li contiene: la proprietà torna al');
console.error('  valore iniziale, in silenzio. Dichiarali, oppure usa un token della scala.\n');
for (const [nome, punti] of orfani) {
	console.error(`  ${nome}`);
	for (const p of punti) console.error(`      ${p.file}:${p.riga}`);
}
console.error('');
process.exit(1);
