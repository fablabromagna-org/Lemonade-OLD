<?php

set_time_limit(0);

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

    $dati = json_decode(file_get_contents('php://input'), true);

    if ($dati === null) {
        reply(400, 'Bad Request', null, true);
    }

    if (!is_array($dati)) {
        reply(400, 'Bad Request', null, true);
    }

    $campi_modificabili = [
        'password_attuale',
        'nuova_password',
        'conferma_password'
    ];

    // Controllo che tutti i campi inviati siano tra quelli modificabili
    foreach ($dati as $key => $value) {
        if (!in_array($key, $campi_modificabili)) {
            reply(400, 'Bad Request', null, true);
        }
    }

    if (count($utente) !== 1) {
        reply(400, 'Bad Request', null, true);
    }

    foreach ($dati as $key => $value) {
        if (!Autenticazione::is_valid_password($value)) {
            reply(400, 'Bad Request', array(
                'field' => $key
            ), true);
        }
    }

    if ($dati['conferma_password'] !== $dati['nuova_password']) {
        reply(400, 'Bad Request', array(
            'field' => 'conferma_password',
            'alert' => 'Le password non corrispondono!'
        ), true);
    }

    if (!Autenticazione::verify_password_hash($utente, $dati['password_attuale'])) {
        reply(400, 'Bad Request', array(
            'field' => 'password_attuale',
            'alert' => 'Password attuale sbagliata!'
        ), true);
    }

    if ($dati['nuova_password'] === $dati['password_attuale']) {
        reply(400, 'Bad Request', array(
            'field' => 'nuova_password',
            'alert' => 'La password attuale è uguale a quella nuova!'
        ), true);
    }

    if (Autenticazione::already_used_password($utente, $dati['nuova_password'])) {
        reply(400, 'Bad Request', array(
            'field' => 'nuova_password',
            'alert' => 'Password già utilizzata negli ultimi dodici mesi!'
        ), true);
    }

    $client = new SesClient(array(
        'version' => '2010-12-01',
        'region' => AWS_REGION,
        'credentials' => [
            'key' => AWS_MAIL_KEY,
            'secret' => AWS_MAIL_SECRET,
        ]
    ));

    $email = \FabLabRomagna\TemplateEmail::ricerca(array(
        new \FabLabRomagna\SQLOperator\Equals('nome', 'cambio_password')
    ));

    foreach ($utente->getDataGridFields() as $campo => $valore) {
        $email->replace('utente.' . $campo, $valore);
    }

    $client->sendEmail([
        'Destination' => [
            'ToAddresses' => [$utente->email],
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
                'Data' => 'Cambio password',
            ]
        ]
    ]);

    Autenticazione::set_user_password($utente, $dati['nuova_password']);

    reply(200, 'Ok', array(
        'alert' => 'Password cambiata con successo!'
    ));

} catch (Exception $e) {
    reply(500, 'Internal Server Error', array(
        'alert' => 'Impossibile completare la richiesta.',
        'redirect' => '/account/impostazioni.php'
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
