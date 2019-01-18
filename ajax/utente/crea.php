<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');
require_once(__DIR__ . '/../../vendor/autoload.php');

use FabLabRomagna\Utente;
use FabLabRomagna\Autenticazione;
use FabLabRomagna\SQLOperator\Equals;
use FabLabRomagna\SQLOperator\NotEquals;
use FabLabRomagna\Log;
use FabLabRomagna\Firewall;
use FabLabRomagna\Gruppo;
use FabLabRomagna\Email\TemplateEmail;
use FabLabRomagna\Email\Configuration;
use FabLabRomagna\Email\Sender;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(405, 'Method Not Allowed');
}

$config = new Configuration(SMTP_HOST, SMTP_PORT, SMTP_USERNAME, SMTP_PWD);

json();

try {
    $ip = Firewall::get_valid_ip();

    if (!Firewall::controllo()) {
        reply(429, 'Too Many Requests');
    }

    $sessione = Autenticazione::get_sessione_attiva();

    if ($sessione === null) {
        reply(401, 'Unauthorized', null, true);
    }

    $sessione->aggiorna_token(true);

    $utente = Utente::ricerca([
        new Equals('id_utente', $sessione->id_utente),
        new Equals('codice_attivazione', null),
        new NotEquals('sospeso', true),
        new NotEquals('secretato', true)
    ]);

    if (count($utente) !== 1) {
        reply(401, 'Unauthorized', null, true);
    }

    $utente = $utente->risultato[0];

    $permessi = \FabLabRomagna\Permesso::what_can_i_do($utente);

    if (!$permessi['gestione.utenti.creare']['reale']) {

        reply(401, 'Unauthorized', null, true);
    }

    $dati = json_decode(file_get_contents('php://input'), true);

    if ($dati === null) {
        reply(400, 'Bad Request', null, true);
    }

    if (!is_array($dati)) {
        reply(400, 'Bad Request', null, true);
    }

    $campi_modificabili = [
        'nome',
        'cognome',
        'email',
        'invio_mail'
    ];

    // Controllo che tutti i campi inviati siano tra quelli modificabili
    foreach ($dati as $key => $value) {
        if (!in_array($key, $campi_modificabili)) {
            reply(400, 'Bad Request', null, true);
        }
    }

    foreach ($dati as $key => $value) {

        if ($key === 'email' && $value === '') {
            $value = null;
            $dati[$key] = null;
        }

        if ($key === 'invio_mail') {
            if (!is_bool($value)) {
                reply(400, 'Bad Request', null, true);
            }

        } elseif (!Utente::valida_campo($key, $value)) {
            reply(400, 'Bad Request', array(
                'field' => $key
            ), true);
        }
    }

    if ($dati['email'] !== null) {
        $utente_registrazione = Utente::ricerca(array(
            new Equals('email', $dati['email'])
        ));

        if (count($utente_registrazione) !== 0) {
            reply(409, 'Conflict', array(
                'field' => 'email',
                'alert' => 'Indirizzo email già in uso. Eventualmente, controlla l\'esistenza di un precedente profilo.'
            ), true);
        }
    }

    $codice_attivazione = !$dati['invio_mail'] && $dati['email'] !== null ? uniqid() : null;

    $utente_registrazione = Utente::crea_utente(array(
        'nome' => $dati['nome'],
        'cognome' => $dati['cognome'],
        'email' => $dati['email'],
        'sospeso' => false,
        'secretato' => false,
        'codice_attivazione' => $codice_attivazione,
        'data_registrazione' => time(),
        'ip_registrazione' => Firewall::get_valid_ip()
    ));

    if ($codice_attivazione !== null) {
        $link = URL_SITO . 'confermaMail.php?id=' . $utente_registrazione->id_utente . '&c=' . $codice_attivazione;

        $email = TemplateEmail::ricerca(array(
            new Equals('nome', 'registrazione')
        ));

        foreach ($utente_registrazione->getDataGridFields() as $campo => $valore) {
            $email->replace('utente.' . $campo, $valore);
        }

        $email->replace('link', $link);

        $sender = new Sender($config, $email);
        $sender->send([$utente_registrazione->email]);
    }

    // Aggiungo l'utente ai gruppi di default
    $gruppi = Gruppo::ricerca(array(
        new Equals('default', true)
    ));

    foreach ($gruppi->risultato as $gruppo) {

        /**
         * @var Gruppo $gruppo
         */

        $gruppo->inserisci_utente($utente_registrazione);
    }

    Log::crea($utente, 1, 'ajax/utente/crea.php', 'crea',
        'Creato utente ID: ' . $utente_registrazione->id_utente);

    reply(200, 'Ok', array(
        'redirect' => '/gestione/utenti/utente.php?id=' . $utente_registrazione->id_utente
    ), true);

} catch (Exception $e) {

    if ($utente instanceof Utente) {
        Log::crea($utente, 3, 'ajax/utente/crea.php', 'crea',
            'Impossibile completare la richiesta.', (string)$e);
    } else {
        Log::crea(null, 3, 'ajax/utente/crea.php', 'crea',
            'Impossibile completare la richiesta.', (string)$e);
    }

    reply(500, 'Internal Server Error', array(
        'alert' => 'Impossibile completare la richiesta.'
    ), true);
}
?>
