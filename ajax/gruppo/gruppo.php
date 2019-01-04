<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');

use FabLabRomagna\Utente;
use FabLabRomagna\Gruppo;
use FabLabRomagna\Autenticazione;
use FabLabRomagna\SQLOperator\Equals;
use FabLabRomagna\SQLOperator\NotEquals;
use FabLabRomagna\Log;
use FabLabRomagna\Firewall;

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

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        if (!$permessi['gestione.gruppi.visualizzare']['reale']) {
            reply(401, 'Unauthorized', null, true);
        }

        $dati = $_GET;

        if ($dati === null) {
            reply(400, 'Bad Request', null, true);
        }

        if (!is_array($dati)) {
            reply(400, 'Bad Request', null, true);
        }

        $campi_modificabili = [
            'id_gruppo',
            '_'
        ];

        if (count($dati) !== count($campi_modificabili)) {
            reply(400, 'Bad Request', null, true);
        }

        if (!preg_match('/^[0-9]{1,11}$/', $dati['id_gruppo'])) {
            reply(400, 'Bad Request', null, true);
        }

        $gruppo = Gruppo::ricerca(array(
            new Equals('id_gruppo', $dati['id_gruppo'])
        ));

        if (count($gruppo) !== 1) {
            reply(404, 'Not Found', null, true);
        }

        $gruppo = $gruppo->risultato[0];

        /**
         * @var Gruppo $gruppo
         */

        reply(200, 'Ok', array(
            'id_gruppo' => $gruppo->id_gruppo,
            'nome' => $gruppo->nome,
            'descrizione' => $gruppo->descrizione,
            'default' => $gruppo->default
        ));

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (!$permessi['gestione.gruppi.creare']['reale']) {
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
            'descrizione',
            'default'
        ];

        // Controllo che tutti i campi inviati siano tra quelli modificabili
        foreach ($dati as $key => $value) {
            if (!in_array($key, $campi_modificabili)) {
                reply(400, 'Bad Request', null, true);
            }
        }

        if (count($dati) !== 3) {
            reply(400, 'Bad Request', null, true);
        }

        $dati['nome'] = trim(htmlspecialchars($dati['nome']));

        if (strlen($dati['nome']) < 3) {
            reply(400, 'Bad Request', array(
                'alert' => 'Nome troppo breve!',
                'field' => 'nome'
            ), true);
        }

        if ($dati['descrizione'] === '') {
            $dati['descrizione'] = null;
        } else {
            $dati['descrizione'] = trim(htmlspecialchars($dati['descrizione']));
        }

        if (!is_bool($dati['default'])) {
            reply(400, 'Bad Request', array(
                'alert' => 'Valore di default non valido!',
                'field' => 'default'
            ), true);
        }

        $gruppo = Gruppo::crea($dati['nome'], $dati['descrizione'], $dati['default']);

        Log::crea($utente, 1, 'ajax/gruppo/gruppo.php', 'crea',
            'Creato gruppo ID: ' . $gruppo->id_gruppo . ' Nome: ' . $gruppo->nome);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

        if (!$permessi['gestione.gruppi.eliminare']['reale']) {
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
            'id_gruppo'
        ];

        // Controllo che tutti i campi inviati siano tra quelli modificabili
        foreach ($dati as $key => $value) {
            if (!in_array($key, $campi_modificabili)) {
                reply(400, 'Bad Request', null, true);
            }
        }

        if (count($dati) !== count($campi_modificabili)) {
            reply(400, 'Bad Request', null, true);
        }

        if (!is_int($dati['id_gruppo'])) {
            reply(400, 'Bad Request', array(
                'alert' => 'Valore di default non valido!',
                'field' => 'default'
            ), true);
        }

        $gruppo = Gruppo::ricerca(array(
            new Equals('id_gruppo', $dati['id_gruppo'])
        ));

        if (count($gruppo) !== 1) {
            reply(404, 'Not Found', array(
                'alert' => 'Gruppo inesistente!'
            ));
        }

        $gruppo = $gruppo->risultato[0];

        /**
         * @var Gruppo $gruppo
         */

        $gruppo->elimina();

        Log::crea($utente, 2, 'ajax/gruppo/gruppo.php', 'crea',
            'Eliminato gruppo ID: ' . $gruppo->id_gruppo . ' Nome: ' . $gruppo->nome);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {

        if (!$permessi['gestione.gruppi.modificare']['reale']) {
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
            'descrizione',
            'default',
            'id_gruppo'
        ];

        // Controllo che tutti i campi inviati siano tra quelli modificabili
        foreach ($dati as $key => $value) {
            if (!in_array($key, $campi_modificabili)) {
                reply(400, 'Bad Request', null, true);
            }
        }

        if (count($dati) !== count($campi_modificabili)) {
            reply(400, 'Bad Request', null, true);
        }

        $dati['nome'] = trim(htmlspecialchars($dati['nome']));

        if (strlen($dati['nome']) < 3) {
            reply(400, 'Bad Request', array(
                'alert' => 'Nome troppo breve!',
                'field' => 'nome'
            ), true);
        }

        if ($dati['descrizione'] === '') {
            $dati['descrizione'] = null;
        } else {
            $dati['descrizione'] = trim(htmlspecialchars($dati['descrizione']));
        }

        if (!is_bool($dati['default'])) {
            reply(400, 'Bad Request', array(
                'alert' => 'Valore di default non valido!',
                'field' => 'default'
            ), true);
        }

        if (!is_int($dati['id_gruppo'])) {
            reply(400, 'Bad Request', array(
                'alert' => 'Valore di default non valido!',
                'field' => 'default'
            ), true);
        }

        $gruppo = Gruppo::ricerca(array(
            new Equals('id_gruppo', $dati['id_gruppo'])
        ));

        if (count($gruppo) !== 1) {
            reply(404, 'Not Found', array(
                'alert' => 'Gruppo inesistente!'
            ));
        }

        $gruppo = $gruppo->risultato[0];

        /**
         * @var Gruppo $gruppo
         */

        foreach ($dati as $field => $value) {
            if ($field === 'id_gruppo') {
                continue;
            }

            $gruppo->modifica($field, $value);
        }

        Log::crea($utente, 1, 'ajax/gruppo/gruppo.php', 'crea',
            'Modificato gruppo ID: ' . $gruppo->id_gruppo . ' Nome: ' . $gruppo->nome);

    } else {
        reply(405, 'Method Not Allowed');
    }

    reply(200, 'Ok', array(
        'redirect' => '/gestione/gruppi/'
    ));

} catch (Exception $e) {

    if ($utente instanceof Utente) {
        Log::crea($utente, 3, 'ajax/utente/scuola.php', 'update',
            'Impossibile completare la richiesta.', (string)$e);
    } else {
        Log::crea(null, 3, 'ajax/utente/scuola.php', 'update',
            'Impossibile completare la richiesta.', (string)$e);
    }

    reply(500, 'Internal Server Error', array(
        'alert' => 'Impossibile completare la richiesta.'
    ), true);
}
?>
