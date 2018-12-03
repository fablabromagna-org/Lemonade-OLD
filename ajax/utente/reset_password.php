<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');
require_once(__DIR__ . '/../../vendor/autoload.php');

use FabLabRomagna\Utente;
use FabLabRomagna\Autenticazione;
use FabLabRomagna\SQLOperator\Equals;
use FabLabRomagna\SQLOperator\NotEquals;
use FabLabRomagna\Log;
use FabLabRomagna\Firewall;
use Aws\Ses\SesClient;

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

    $campi_modificabili = [
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

    $password = Autenticazione::generatePassword();

    $client = new SesClient(array(
        'version' => '2010-12-01',
        'region' => AWS_REGION,
        'credentials' => [
            'key' => AWS_MAIL_KEY,
            'secret' => AWS_MAIL_SECRET,
        ]
    ));

    $email = \FabLabRomagna\TemplateEmail::ricerca(array(
        new \FabLabRomagna\SQLOperator\Equals('nome', 'reset_password')
    ));

    foreach ($utenteModifica->getDataGridFields() as $campo => $valore) {
        $email->replace('utente.' . $campo, $valore);
    }

    $email->replace('password', $password);

    $client->sendEmail([
        'Destination' => [
            'ToAddresses' => [$utenteModifica->email],
        ],
        'ReplyToAddresses' => [EMAIL_REPLY_TO],
        'Source' => EMAIL_FROM,
        'Message' => [
            'Body' => [
                'Html' => [
                    'Charset' => 'UTF-8',
                    'Data' => $email->file,
                ]
            ],
            'Subject' => [
                'Charset' => 'UTF-8',
                'Data' => 'Nuova password',
            ]
        ]
    ]);

    Autenticazione::set_user_password($utenteModifica, $password);

    reply(200, 'Ok', array(
        'redirect' => '/gestione/utenti/utente.php?id=' . $utenteModifica->id_utente
    ));

} catch (Exception $e) {
    reply(500, 'Internal Server Error', array(
        'alert' => 'Impossibile completare la richiesta.'
    ), true);

    if ($utente instanceof Utente) {
        Log::crea($utente, 3, 'ajax/utente/reset_password.php', 'update_password',
            'Impossibile completare la richiesta.', (string)$e);
    } else {
        Log::crea(null, 3, 'ajax/utente/reset_password.php', 'update_password',
            'Impossibile completare la richiesta.', (string)$e);
    }

}
?>
