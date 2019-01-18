<?php
require_once(__DIR__ . '/../class/autoload.inc.php');
require_once(__DIR__ . '/../vendor/autoload.php');

use FabLabRomagna\Utente;
use FabLabRomagna\Autenticazione;
use FabLabRomagna\SQLOperator\Equals;
use FabLabRomagna\Log;
use FabLabRomagna\Firewall;
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

    if ($sessione !== null) {
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
        'email',
        'captcha'
    ];

    // Controllo che tutti i campi inviati siano tra quelli modificabili
    foreach ($dati as $key => $value) {
        if (!in_array($key, $campi_modificabili)) {
            reply(400, 'Bad Request', null, true);
        }
    }

    if (count($dati) !== 2) {
        reply(400, 'Bad Request', null, true);
    }

    if (!Utente::valida_campo('email', $dati['email'])) {
        reply(400, 'Bad Request', array(
            'field' => 'email'
        ), true);
    }

    $utenteModifica = Utente::ricerca([
        new Equals('email', $dati['email'])
    ]);

    if (count($utenteModifica) !== 1) {
        reply(204, 'No Content');
    }

    $utenteModifica = $utenteModifica->risultato[0];

    /**
     * @var Utente $utenteModifica
     */

    $password = Autenticazione::generatePassword();

    $email = TemplateEmail::ricerca(array(
        new Equals('nome', 'recupero_password')
    ));

    foreach ($utenteModifica->getDataGridFields() as $campo => $valore) {
        $email->replace('utente.' . $campo, $valore);
    }

    $email->replace('password', $password);

    $sender = new Sender($config, $email);
    $sender->send([$utenteModifica->email]);

    Autenticazione::set_user_password($utenteModifica, $password);

    reply(204, 'No Content');

} catch (Exception $e) {

    if ($utente instanceof Utente) {
        Log::crea($utente, 3, 'ajax/recupero.php', 'update_password',
            'Impossibile completare la richiesta.', (string)$e);
    } else {
        Log::crea(null, 3, 'ajax/recupero.php', 'update_password',
            'Impossibile completare la richiesta.', (string)$e);
    }

    reply(500, 'Internal Server Error', array(
        'alert' => 'Impossibile completare la richiesta.'
    ), true);
}
?>
