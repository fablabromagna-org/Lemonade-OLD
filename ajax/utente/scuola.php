<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');

use FabLabRomagna\Utente;
use FabLabRomagna\Autenticazione;
use FabLabRomagna\SQLOperator\Equals;
use FabLabRomagna\SQLOperator\NotEquals;
use FabLabRomagna\Log;
use FabLabRomagna\Firewall;
use FabLabRomagna\Scuola;
use FabLabRomagna\RelazioneScolastica;

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

    if (!$permessi['gestione.utenti.modificare_anagrafiche']['reale']) {

        reply(401, 'Unauthorized', null, true);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $dati = json_decode(file_get_contents('php://input'), true);

        if ($dati === null) {
            reply(400, 'Bad Request', null, true);
        }

        if (!is_array($dati)) {
            reply(400, 'Bad Request', null, true);
        }

        $campi_modificabili = [
            'scuola',
            'ruolo',
            'classe',
            'sezione',
            'id_utente'
        ];

        // Controllo che tutti i campi inviati siano tra quelli modificabili
        foreach ($dati as $key => $value) {

            if ($key === 'sezione' && $value === '') {
                unset($dati['sezione']);
            }

            if (!in_array($key, $campi_modificabili)) {
                reply(400, 'Bad Request', null, true);
            }
        }

        if (!Utente::valida_campo('id_utente', $dati['id_utente'])) {
            reply(400, 'Bad Request', array(), true);
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

        $scuola = Scuola::ricerca(array(
            new Equals('codice', $dati['scuola'])
        ));

        if (count($scuola) !== 1) {
            reply(400, 'Bad Request', null, true);
        }

        $scuola = $scuola->risultato[0];

        /**
         * @var Scuola $scuola
         */

        $relazione = RelazioneScolastica::ricerca(array(
            new Equals('scuola', $scuola->codice),
            new Equals('utente', $utenteModifica->id_utente)
        ));

        if (count($relazione) !== 0) {
            reply(400, 'Bad Request', array(
                'alert' => 'Relazione con la scuola già presente!'
            ), true);
        }

        if (!isset($dati['ruolo'])) {
            reply(400, 'Bad Request', null, true);
        }

        if (!is_int($dati['ruolo'])) {
            reply(400, 'Bad Request', null, true);
        }

        if ($dati['ruolo'] === 0) {

            if (!isset($dati['classe']) || !isset($dati['sezione'])) {
                reply(400, 'Bad Request', null, true);
            }

            if (!is_int($dati['classe'])) {
                reply(400, 'Bad Request', array(
                    'field' => 'classe',
                    'alert' => 'Classe non valida!'
                ), true);
            }

            if (!is_string($dati['sezione'])) {
                reply(400, 'Bad Request', array(
                    'field' => 'sezione',
                    'alert' => 'Sezione non valida!'
                ), true);
            }

            if ($dati['classe'] < 1 || $dati['classe'] > 5) {
                reply(400, 'Bad Request', array(
                    'field' => 'classe',
                    'alert' => 'Classe non valida!'
                ), true);
            }

            RelazioneScolastica::crea($utenteModifica, $scuola, 0, $dati['classe'], $dati['sezione']);

            Log::crea($utente, 1, 'ajax/utente/scuola.php', 'add',
                'Aggiunta scuola ' . $scuola . ' (studente, classe ' . $dati['classe'] . ', sez. ' . $dati['sezione'] .
                ') all\'utente ' . $utenteModifica->nome . ' ' . $utenteModifica->cognome . '.');

        } elseif ($dati['ruolo'] === 1 || $dati['ruolo'] === 2) {

            RelazioneScolastica::crea($utenteModifica, $scuola, $dati['ruolo']);

            $ruolo = $dati['ruolo'] === 1 ? 'insegnante' : 'personale';

            Log::crea($utente, 1, 'ajax/utente/scuola.php', 'add',
                'Aggiunta scuola ' . $scuola . ' (' . $ruolo . ') all\'utente ' . $utenteModifica->nome . ' ' .
                $utenteModifica->cognome . ' (ID: ' . $utenteModifica->id_utente . ').');

        } else {
            reply(400, 'Bad Request', null, true);
        }


    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

        $dati = json_decode(file_get_contents('php://input'), true);

        if ($dati === null) {
            reply(400, 'Bad Request', null, true);
        }

        if (!is_array($dati)) {
            reply(400, 'Bad Request', null, true);
        }

        $campi_modificabili = [
            'id_relazione',
            'id_utente'
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

        $relazione = RelazioneScolastica::ricerca(array(
            new Equals('id_relazione', $dati['id_relazione']),
            new Equals('utente', $utenteModifica->id_utente)
        ));

        if (count($relazione) !== 1) {
            reply(400, 'Bad Request', array(
                'alert' => 'Relazione inesistente!'
            ), true);
        }

        $relazione = $relazione->risultato[0];

        /**
         * @var RelazioneScolastica $relazione
         */

        $relazione->elimina();


        Log::crea($utente, 2, 'ajax/utente/scuola.php', 'delete',
            'Eliminata scuola all\'utente ' . $utenteModifica->nome . ' ' .
            $utenteModifica->cognome . ' (ID: ' . $utenteModifica->id_utente . ').');

    } else {
        reply(405, 'Method Not Allowed');
    }

    reply(200, 'Ok', array(
        'redirect' => '/gestione/utenti/utente.php?id=' . $utenteModifica->id_utente
    ));

} catch (Exception $e) {
    reply(500, 'Internal Server Error', array(
        'alert' => 'Impossibile completare la richiesta.'
    ), true);

    if ($utente instanceof Utente) {
        Log::crea($utente, 3, 'ajax/utente/scuola.php', 'update',
            'Impossibile completare la richiesta.', (string)$e);
    } else {
        Log::crea(null, 3, 'ajax/utente/scuola.php', 'update',
            'Impossibile completare la richiesta.', (string)$e);
    }

}
?>
