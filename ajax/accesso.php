<?php
set_time_limit(0);

require_once(__DIR__ . '/../class/autoload.inc.php');
require_once(__DIR__ . '/../vendor/autoload.php');

use FabLabRomagna\Utente;
use FabLabRomagna\Autenticazione;
use FabLabRomagna\OggettoRegistro;
use FabLabRomagna\Firewall;
use FabLabRomagna\SQLOperator\Equals;
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

    $dati = json_decode(file_get_contents('php://input'), true);

    if ($dati === null) {
        reply(400, 'Bad Request', array(
            'refreshCaptcha' => true
        ));
    }

    if (!is_array($dati)) {
        reply(400, 'Bad Request', array(
            'refreshCaptcha' => true
        ));
    }

    $campi_validi = ['email', 'password', 'captcha'];

    foreach ($dati as $key => $value) {
        if (!in_array($key, $campi_validi)) {
            reply(400, 'Bad Request', array(
                'refreshCaptcha' => true
            ));
        }
    }

    if (count($dati) !== 3) {
        reply(400, 'Bad Request', array(
            'refreshCaptcha' => true
        ));
    }

    $captcha = $dati['captcha'];
    $email = $dati['email'];
    $password = $dati['password'];

    $builder = new Gregwar\Captcha\CaptchaBuilder(isset($_SESSION['captcha']) ? $_SESSION['captcha'] : null);

    if (!$builder->testPhrase($captcha)) {
        reply(401, 'Unauthorized', array(
            'field' => 'captcha',
            'refreshCaptcha' => true
        ));
    }


    if (!Utente::valida_campo('email', $email)) {
        reply(400, 'Bad Request', array(
            'field' => 'email',
            'refreshCaptcha' => true
        ));
    }

    if (!Autenticazione::is_valid_password($password)) {
        reply(400, 'Bad Request', array(
            'field' => 'password',
            'refreshCaptcha' => true
        ));
    }

    $utente = Utente::ricerca([
        new Equals('email', $email)
    ]);

    if (count($utente) !== 1) {

        OggettoRegistro::crea('FabLabRomagna\Autenticazione', 'login', $ip);

        if (count(OggettoRegistro::ricerca_da_ip('FabLabRomagna\Autenticazione', 'login', $ip, 300)) > 3) {
            Firewall::aggiungi_regola($ip, 32, 'reject', 900);
        }

        reply(401, 'Unauthorized', array(
            'field' => 'email',
            'refreshCaptcha' => true
        ));

    }

    $utente = $utente->risultato[0];

    /**
     * @var Utente $utente
     */
    if ($utente->sospeso || $utente->secretato) {
        reply(401, 'Unauthorized', array(
            'alert' => 'Account non disponibile. Per ulteriori informazioni contatta l\'associazione.',
            'refreshCaptcha' => true
        ));
    }

    if (!Autenticazione::verify_password_hash($utente, $password)) {

        OggettoRegistro::crea('FabLabRomagna\Autenticazione', 'login', $ip);

        if (count(OggettoRegistro::ricerca_da_ip('FabLabRomagna\Autenticazione',
                'login', $ip, 300)) > 3) {

            $email = TemplateEmail::ricerca(array(
                new Equals('nome', 'accessi_bloccati')
            ));

            foreach ($utente->getDataGridFields() as $campo => $valore) {
                $email->replace('utente.' . $campo, $valore);
            }

            $sender = new Sender($config, $email);
            $sender->send([$utente->email]);

            Firewall::aggiungi_regola($ip, 32, 'reject', 900);
        }

        reply(401, 'Unauthorized', array(
            'field' => 'password',
            'refreshCaptcha' => true
        ));
    }

    Autenticazione::create_session($utente, 'browser', $_SERVER['HTTP_USER_AGENT'],
        3600 * 24 * 7, true);

    FabLabRomagna\Log::crea($utente, 1, 'ajax/accesso.php', 'login',
        'Effettuato un nuovo accesso.');

    $email = TemplateEmail::ricerca(array(
        new Equals('nome', 'nuovo_accesso')
    ));

    foreach ($utente->getDataGridFields() as $campo => $valore) {
        $email->replace('utente.' . $campo, $valore);
    }

    $sender = new Sender($config, $email);
    $sender->send([$utente->email]);

    reply(200, 'Ok', array(
        'redirect' => '/dashboard.php'
    ));

} catch (Exception $e) {

    FabLabRomagna\Log::crea(null, 3, 'ajax/accesso.php', 'login',
        'Impossibile completare l\'accesso.', (string)$e);

    reply(500, 'Internal Server Error', array(
        'alert' => 'Impossibile completare la richiesta.',
        'refreshCaptcha' => false
    ), false);
}
?>
