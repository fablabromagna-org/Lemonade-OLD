<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');

use FabLabRomagna\Utente;
use FabLabRomagna\Autenticazione;
use FabLabRomagna\SQLOperator\Equals;
use FabLabRomagna\SQLOperator\NotEquals;
use FabLabRomagna\SQLOperator\Like;
use FabLabRomagna\SQLOperator\SQLOr;
use FabLabRomagna\Log;
use FabLabRomagna\Firewall;
use FabLabRomagna\Scuola;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(405, 'Method Not Allowed');
}

json();

try {
    if (!Firewall::controllo()) {
        reply(429, 'Too Many Requests');
    }

    $sessione = Autenticazione::get_sessione_attiva();

    if ($sessione === null) {
        reply(401, 'Unauthorized', null, true);
    }

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

    if (!$permessi['gestione.utenti.modificare_anagrafiche']['reale']) {
        reply(401, 'Unauthorized', null, true);
    }

    $dati = json_decode(file_get_contents('php://input'), true);

    if ($dati === null) {
        reply(400, 'Bad Request', null, true);
    }

    if (!is_array($dati)) {
        reply(400, 'Bad Request', null, true);
    }

    $campi_accettabili = [
        'ricerca'
    ];

    // Controllo che tutti i campi inviati siano tra quelli modificabili
    foreach ($dati as $key => $value) {
        if (!in_array($key, $campi_accettabili)) {
            reply(400, 'Bad Request', null, true);
        }
    }

    $ricerca = Scuola::ricerca([
        new Like('codice', '%' . $dati['ricerca'] . '%'),
        new SQLOr(),
        new Like('denominazione', '%' . $dati['ricerca'] . '%')
    ]);

    $res = [];

    foreach ($ricerca->risultato as $scuola) {

        /**
         * @var Scuola $scuola
         */

        $res[] = [
            'text' => $scuola . ' - ' . $scuola->comune->nome,
            'value' => $scuola->codice
        ];
    }

    reply(200, 'Ok', $res, true);

} catch (Exception $e) {
    reply(500, 'Internal Server Error', array(
        'alert' => 'Impossibile completare la richiesta.'
    ), true);

    if ($utente instanceof Utente) {
        Log::crea($utente, 3, 'ajax/utente/.php', 'update',
            'Impossibile completare la richiesta.', (string)$e);
    } else {
        Log::crea(null, 3, 'ajax/utente/libgeri.php', 'update',
            'Impossibile completare la richiesta.', (string)$e);
    }

}
?>
