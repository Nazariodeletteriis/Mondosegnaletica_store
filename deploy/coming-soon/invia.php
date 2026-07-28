<?php
/**
 * Mondo Segnaletica — ricezione richieste dalla pagina "coming soon".
 * Invia a info@mondosegnaletica.it usando la posta del server.
 * Compatibile PHP 8.1 (handler ea-php81 impostato in .htaccess).
 */

declare(strict_types=1);

const DESTINATARIO  = 'info@mondosegnaletica.it';
const MITTENTE      = 'info@mondosegnaletica.it';   // stesso dominio: SPF/DKIM allineati
const ATTESA_MINIMA = 3;      // secondi tra caricamento pagina e invio (anti-bot)
const ATTESA_TRA_INVII = 60;  // secondi tra due invii dallo stesso IP

/** Risponde in JSON alle chiamate fetch, con redirect per i browser senza JS. */
function rispondi(bool $ok, string $messaggio, int $codice = 200): never
{
    $vuoleJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

    if ($vuoleJson) {
        http_response_code($codice);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'messaggio' => $messaggio], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: /index.html?esito=' . ($ok ? 'ok' : 'errore'), true, 303);
    }
    exit;
}

/** Rimuove i caratteri che permetterebbero di iniettare intestazioni SMTP. */
function pulisci(string $v, int $max = 500): string
{
    $v = str_replace(["\r", "\n", "\0", '%0a', '%0d'], ' ', $v);
    return mb_substr(trim($v), 0, $max);
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    rispondi(false, 'Metodo non consentito.', 405);
}

// Campo esca: invisibile agli umani, i bot lo compilano.
if (pulisci($_POST['sito'] ?? '') !== '') {
    rispondi(true, 'Richiesta ricevuta.');   // risposta identica a quella vera
}

// Un form compilato in meno di 3 secondi non è stato compilato da una persona.
$aperturaPagina = (int) ($_POST['ts'] ?? 0);
if ($aperturaPagina > 0 && (time() - $aperturaPagina) < ATTESA_MINIMA) {
    rispondi(true, 'Richiesta ricevuta.');
}

// Un invio per minuto per indirizzo IP.
$ip      = $_SERVER['REMOTE_ADDR'] ?? 'ignoto';
$spia    = sys_get_temp_dir() . '/ms-form-' . md5($ip);
$ultimo  = is_file($spia) ? (int) file_get_contents($spia) : 0;
if ($ultimo && (time() - $ultimo) < ATTESA_TRA_INVII) {
    rispondi(false, 'Hai già inviato una richiesta poco fa. Riprova tra un minuto.', 429);
}

$nome    = pulisci($_POST['nome'] ?? '', 120);
$azienda = pulisci($_POST['azienda'] ?? '', 120);
$email   = pulisci($_POST['email'] ?? '', 180);
$tel     = pulisci($_POST['tel'] ?? '', 40);
$msg     = mb_substr(trim((string) ($_POST['msg'] ?? '')), 0, 4000);
$privacy = ($_POST['privacy'] ?? '') !== '';

if ($nome === '' || mb_strlen($nome) < 2) {
    rispondi(false, 'Inserisci il tuo nome e cognome.', 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    rispondi(false, "Inserisci un'email valida.", 422);
}
if (!$privacy) {
    rispondi(false, "Devi accettare il trattamento dei dati per inviare la richiesta.", 422);
}

$corpo = implode("\n", [
    'Nuova richiesta dal sito mondosegnaletica.it',
    str_repeat('-', 46),
    'Nome:          ' . $nome,
    'Azienda/Ente:  ' . ($azienda !== '' ? $azienda : '-'),
    'Email:         ' . $email,
    'Telefono:      ' . ($tel !== '' ? $tel : '-'),
    '',
    'Richiesta:',
    $msg !== '' ? $msg : '(nessun messaggio)',
    '',
    str_repeat('-', 46),
    'Ricevuta il ' . date('d/m/Y \a\l\l\e H:i') . ' — IP ' . $ip,
]);

$oggetto = 'Richiesta dal sito — ' . $nome;

$intestazioni = [
    'From: Mondo Segnaletica <' . MITTENTE . '>',
    'Reply-To: ' . $nome . ' <' . $email . '>',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'X-Mailer: mondosegnaletica-form',
];

$inviata = mail(
    DESTINATARIO,
    '=?UTF-8?B?' . base64_encode($oggetto) . '?=',
    $corpo,
    implode("\r\n", $intestazioni),
    '-f' . MITTENTE
);

if (!$inviata) {
    error_log('[form] invio fallito da IP ' . $ip);
    rispondi(false, 'Invio non riuscito. Scrivici a ' . DESTINATARIO, 500);
}

file_put_contents($spia, (string) time());
rispondi(true, 'Richiesta inviata. Ti rispondiamo entro 4 ore lavorative.');
