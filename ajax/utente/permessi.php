<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');

use FabLabRomagna\Utente;
use FabLabRomagna\Permesso;
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

    $permessi = Permesso::what_can_i_do($utente);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        if (!$permessi['gestione.permessi.visualizzare']['reale']) {
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
            'id_utente',
            '_'
        ];

        if (count($dati) !== count($campi_modificabili)) {
            reply(400, 'Bad Request', null, true);
        }

        if (!preg_match('/^[0-9]{1,11}$/', $dati['id_utente'])) {
            reply(400, 'Bad Request', null, true);
        }

        $utente_ricerca = Utente::ricerca(array(
            new Equals('id_utente', $dati['id_utente'])
        ));

        if (count($utente_ricerca) !== 1) {
            reply(404, 'Not Found', null, true);
        }

        $utente_ricerca = $utente_ricerca->risultato[0];

        /**
         * @var Utente $utente_ricerca
         */

        $sql = "SELECT * FROM permessi WHERE utente = ? AND id_utente_gruppo = ?";
        $stmt = $mysqli->prepare($sql);

        if ($stmt === false) {
            throw new \Exception('Unable to prepare the query!');
        }

        $true = true;
        $false = false;

        $permessi_gruppo = ELENCO_PERMESSI;

        if (!$stmt->bind_param('ii', $true, $utente_ricerca->id_utente)) {
            throw new \Exception('Unable to bind params!');
        }

        if (!$stmt->execute()) {
            throw new \Exception('Unable to execute the query!');
        }

        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            if (isset($permessi_gruppo[$row['permesso']])) {
                $permessi_gruppo[$row['permesso']]['reale'] = (bool)$row['valore'];
            }
        }

        reply(200, 'Ok', $permessi_gruppo, true);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (!$permessi['gestione.permessi.modificare_utenti']['reale']) {
            reply(401, 'Unauthorized', null, true);
        }

        $dati = json_decode(file_get_contents('php://input'), true);

        if ($dati === null) {
            reply(400, 'Bad Request', null, true);
        }

        if (!is_array($dati)) {
            reply(400, 'Bad Request', null, true);
        }

        $campi_modificabili = array_keys(ELENCO_PERMESSI);
        $campi_modificabili[] = 'id_utente';

        // Controllo che tutti i campi inviati siano tra quelli modificabili
        foreach ($dati as $key => $value) {
            if (!in_array($key, $campi_modificabili)) {
                reply(400, 'Bad Request', null, true);
            }
        }

        foreach ($dati as $key => $value) {
            if ($key === 'id_utente') {
                continue;
            }

            if (!is_bool($value) && $value !== null) {
                reply(400, 'Bad Request', null, true);
            }
        }

        if (count($dati) !== count($campi_modificabili)) {
            reply(400, 'Bad Request', null, true);
        }


        $utente_ricerca = Utente::ricerca(array(
            new Equals('id_utente', $dati['id_utente'])
        ));

        if (count($utente_ricerca) !== 1) {
            reply(404, 'Not Found', array(
                'alert' => 'Utente inesistente!'
            ));
        }

        $utente_ricerca = $utente_ricerca->risultato[0];

        /**
         * @var Utente $utente_ricerca
         */

        foreach ($dati as $key => $value) {
            if ($key === 'id_utente') {
                continue;
            }

            if ($value !== null) {
                Permesso::aggiungi_permesso($utente_ricerca, $key, $value);
            } elseif (($permesso = Permesso::get_permission($utente_ricerca, $key)) !== null) {
                $permesso->rimuovi();
            }
        }

        Log::crea($utente, 1, 'ajax/utente/permessi.php', 'crea',
            'Modificati permessi all\'utente ID: ' . $utente_ricerca->id_utente . ' Nome: ' . $utente_ricerca->cognome . ' Nome: ' . $utente_ricerca->cognome);

        reply(204, 'No Content', null, true);

    } else {
        reply(405, 'Method Not Allowed');
    }

} catch (Exception $e) {

    if ($utente instanceof Utente) {
        Log::crea($utente, 3, 'ajax/utente/permessi.php', 'update',
            'Impossibile completare la richiesta.', (string)$e);
    } else {
        Log::crea(null, 3, 'ajax/utente/permessi.php', 'update',
            'Impossibile completare la richiesta.', (string)$e);
    }

    reply(500, 'Internal Server Error', array(
        'alert' => 'Impossibile completare la richiesta.'
    ), true);
}
?>
