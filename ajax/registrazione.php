<?php
require_once(__DIR__ . '/../class/autoload.inc.php');
require_once(__DIR__ . '/../vendor/autoload.php');

use FabLabRomagna\Utente;
use FabLabRomagna\Autenticazione;
use FabLabRomagna\Gruppo;
use FabLabRomagna\Firewall;
use FabLabRomagna\SQLOperator\Equals;
use FabLabRomagna\Log;
use FabLabRomagna\Email\TemplateEmail;
use FabLabRomagna\Email\Configuration;
use FabLabRomagna\Email\Sender;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(405, 'Method Not Allowed');
}

$config = new Configuration(SMTP_HOST, SMTP_PORT, SMTP_USERNAME, SMTP_PWD);

json();

try {
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

    $campi_modificabili = ['nome', 'cognome', 'email', 'password', 'captcha'];

    // Controllo che tutti i campi inviati siano tra quelli modificabili
    foreach ($dati as $key => $value) {
        if (!in_array($key, $campi_modificabili)) {
            reply(400, 'Bad Request', null, true);
        }
    }

    $nome = $dati['nome'];
    $cognome = $dati['cognome'];
    $email = $dati['email'];
    $password = $dati['password'];
    $captcha = $dati['captcha'];

    $builder = new Gregwar\Captcha\CaptchaBuilder(isset($_SESSION['captcha']) ? $_SESSION['captcha'] : null);

    if (!$builder->testPhrase($captcha)) {
        reply(400, 'Bad Request', array(
            'field' => 'captcha',
            'refreshCaptcha' => true
        ));
    }

    foreach ($dati as $key => $value) {
        if ($key === 'captcha') {
            continue;
        }

        if ($key === 'password') {

            if (!Autenticazione::is_valid_password($value)) {
                reply(400, 'Bad Request', array(
                    'field' => $key
                ), true);
            }

        } elseif (!Utente::valida_campo($key, $value)) {
            reply(400, 'Bad Request', array(
                'field' => $key
            ), true);
        }
    }

    // Controllo che non siano presenti altri utenti con lo stesso indirizzo email
    $utente = Utente::ricerca(array(
        new Equals('email', $email)
    ));

    // Sono presenti alcuni record nel db
    if (count($utente) !== 0) {

        reply(409, 'Conflict', array(
                'field' => 'email',
                'alert' => 'E-Mail già in uso!'
            ), true);

    } else {

        $codice_attivazione = uniqid();

        $utente = Utente::crea_utente(array(
            'nome' => $nome,
            'cognome' => $cognome,
            'email' => $email,
            'sospeso' => false,
            'secretato' => false,
            'codice_attivazione' => $codice_attivazione,
            'data_registrazione' => time(),
            'ip_registrazione' => Firewall::get_valid_ip()
        ));
    }

    /**
     * @var Utente $utente
     */

    Autenticazione::set_user_password($utente, $password);

    $link = URL_SITO . 'confermaMail.php?id=' . $utente->id_utente . '&c=' . $codice_attivazione;

    $email = TemplateEmail::ricerca(array(
        new Equals('nome', 'registrazione')
    ));

    foreach ($utente->getDataGridFields() as $campo => $valore) {
        $email->replace('utente.' . $campo, $valore);
    }

    $email->replace('link', $link);

    $sender = new Sender($config, $email);
    $sender->send([$utente->email]);

    // Aggiungo l'utente ai gruppi di default
    $gruppi = Gruppo::ricerca(array(
        new Equals('default', true)
    ));

    foreach ($gruppi->risultato as $gruppo) {
        $gruppo->inserisci_utente($utente);
    }

    reply(200, 'Ok', array(
        'redirect' => '/completaRegistrazione.php'
    ), true);

} catch (Exception $e) {

    if ($utente instanceof Utente) {
        Log::crea($utente, 3, 'ajax/registrazione.php', 'registrazione',
            'Impossibile completare la richiesta.', (string)$e);
    } else {
        Log::crea(null, 3, 'ajax/registrazione.php', 'registrazione',
            'Impossibile completare la richiesta.', (string)$e);
    }

    reply(500, 'Internal Server Error', array(
        'alert' => 'Impossibile completare la richiesta.' . $e
    ), true);
}
