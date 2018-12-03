<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');

use FabLabRomagna\Utente;
use FabLabRomagna\Autenticazione;
use FabLabRomagna\SQLOperator\Equals;
use FabLabRomagna\SQLOperator\NotEquals;
use FabLabRomagna\Log;
use FabLabRomagna\Firewall;
use FabLabRomagna\Gruppo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(405, 'Method Not Allowed');
}

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
        ''
    ];

    // Controllo che tutti i campi inviati siano tra quelli modificabili
    foreach ($dati as $key => $value) {
        if (!in_array($key, $campi_modificabili)) {
            reply(400, 'Bad Request', null, true);
        }
    }

    if (!Utente::valida_campo('id_utente', $dati['id_utente'])) {
        reply(400, 'Bad Request', array(), true);
    }

    foreach ($dati['gruppi'] as $key => $value) {
        $res = FabLabRomagna\Gruppo::ricerca(array(
            new FabLabRomagna\SQLOperator\Equals('id_gruppo', $value),
            new FabLabRomagna\SQLOperator\Equals('eliminato', false)
        ));

        if (count($res) !== 1) {
            reply(400, 'Bad Request', array(), true);
        }
    }

    $utenteModifica = Utente::ricerca([
        new Equals('id_utente', $dati['id_utente'])
    ]);

    if (count($utenteModifica) !== 1) {
        reply(400, 'Bad Request', null, true);
    }

    $utenteModifica = $utenteModifica->risultato[0];

    /**
     * @var Utente $utenteModifica
     */

    $tutti = Gruppo::ricerca(array());

    foreach ($tutti->risultato as $gruppo) {

        /**
         * @var Gruppo $gruppo
         */

        if (in_array($gruppo->id_gruppo, $dati['gruppi']) && !$gruppo->fa_parte($utenteModifica)) {
            $gruppo->inserisci_utente($utenteModifica);
            Log::crea($utente, 1, 'ajax/anagrafiche/aggiorna.php', 'update',
                'Utente ' . $utenteModifica->nome . ' . ' . $utenteModifica->cognome . ' (ID: ' . $utenteModifica->id_utente . ') aggiunto al gruppo ' . $gruppo->nome . ' (ID: ' . $gruppo->id_gruppo . ').');

        } elseif (!in_array($gruppo->id_gruppo, $dati['gruppi']) && $gruppo->fa_parte($utenteModifica)) {
            $gruppo->rimuovi_utente($utenteModifica);
            Log::crea($utente, 1, 'ajax/anagrafiche/aggiorna.php', 'update',
                'Utente ' . $utenteModifica->nome . ' . ' . $utenteModifica->cognome . ' (ID: ' . $utenteModifica->id_utente . ') rimosso dal gruppo ' . $gruppo->nome . ' (ID: ' . $gruppo->id_gruppo . ').');
        }
    }

    reply(200, 'Ok', array(
        'redirect' => '/gestione/utenti/utente.php?id=' . $utenteModifica->id_utente
    ));

} catch (Exception $e) {
    reply(500, 'Internal Server Error', array(
        'alert' => 'Impossibile completare la richiesta.'
    ), true);

    if ($utente instanceof Utente) {
        Log::crea($utente, 3, 'ajax/utente/imposta_gruppi.php', 'update',
            'Impossibile completare la richiesta.', (string)$e);
    } else {
        Log::crea(null, 3, 'ajax/utente/imposta_gruppi.php', 'update',
            'Impossibile completare la richiesta.', (string)$e);
    }

}
?>
