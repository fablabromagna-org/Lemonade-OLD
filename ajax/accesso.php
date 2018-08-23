<?php
require_once(__DIR__ . '/../class/autoload.inc.php');

use FabLabRomagna\Utente;
use FabLabRomagna\Autenticazione;
use FabLabRomagna\Fallimento;
use FabLabRomagna\Firewall;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(405, 'Method Not Allowed');
}

$email = isset($_POST['email']) ? $_POST['email'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$captcha = isset($_POST['captcha']) ? $_POST['captcha'] : '';

json();

try {
    $ip = Firewall::get_valid_ip();

    if (!Firewall::controllo()) {
        reply(429, 'Too Many Requests');
    }

    $builder = new Gregwar\Captcha\CaptchaBuilder(isset($_SESSION['captcha']) ? $_SESSION['captcha'] : null);

    if (!$builder->testPhrase($captcha)) {
        reply(401, 'Not Authorized', array(
            'field' => 'captcha',
            'refreshCaptcha' => true
        ));
    }


    if (!Utente::valida_campo('email', $email)) {
        reply(401, 'Not Authorized', array(
            'field' => 'email',
            'refreshCaptcha' => true
        ));
    }

    if (!Autenticazione::is_valid_password($password)) {
        reply(401, 'Not Authorized', array(
            'field' => 'password',
            'refreshCaptcha' => true
        ));
    }

    $utente = Utente::ricerca(array(
        'email' => $email
    ));

    if (count($utente) !== 1) {

        Fallimento::crea('FabLabRomagna\Autenticazione', 'login', $ip);

        if (count(Fallimento::ricerca_da_ip('FabLabRomagna\Autenticazione', 'login', $ip)) > 3) {
            Firewall::aggiungi_regola($ip, 32, 'reject', 900);
        }

        reply(401, 'Not Authorized', array(
            'field' => 'email',
            'refreshCaptcha' => true
        ));

    }

    $utente = $utente->risultato[0];

    if ($utente->sospeso || $utente->secretato) {
        reply(401, 'Not Authorized', array(
            'alert' => 'Account non disponibile. Per ulteriori informazioni contatta l\'amministratore.',
            'refreshCaptcha' => true
        ));
    }

    if (!Autenticazione::verify_password_hash($utente, $password)) {

        Fallimento::crea('FabLabRomagna\Autenticazione', 'login', $ip);

        if (count(Fallimento::ricerca_da_ip('FabLabRomagna\Autenticazione',
                'login', $ip, $utente->id_utente)) > 3) {
            Firewall::aggiungi_regola($ip, 32, 'reject', 900);
        }

        reply(401, 'Not Authorized', array(
            'field' => 'password',
            'refreshCaptcha' => true
        ));
    }

    Autenticazione::create_session($utente, 'browser', $_SERVER['HTTP_USER_AGENT'],
        3600 * 24 * 7, true);

    FabLabRomagna\Log::crea($utente, 0, 'ajax/accesso.php', 'login',
        'Effettuato un nuovo accesso.');

    reply();

} catch (Exception $e) {
    reply(500, 'Internal Server Error', array(
        'alert' => 'Impossibile completare la richiesta.',
        'refreshCaptcha' => false
    ), false);

    FabLabRomagna\Log::crea(null, 3, 'ajax/accesso.php', 'login',
        'Impossibile completare l\'accesso.', (string)$e);

}
?>
