<?php
require_once(__DIR__ . '/../class/autoload.inc.php');

try {
    if (!\FabLabRomagna\Firewall::controllo()) {
        \FabLabRomagna\Firewall::firewall_redirect();
    }

    if (\FabLabRomagna\Autenticazione::get_sessione_attiva() !== null) {
        header('Location: /dashboard.php');
        exit();
    }
} catch (Exception $e) {
    \FabLabRomagna\Log::crea(null, 3, '/login.php', 'page_request',
        'Impossibile completare la richiesta.', (string)$e);
    reply(500, 'Internal Server Error');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        require_once('../inc/header.inc.php');
        ?>
    </head>
    <body>
        <?php
        include_once('../inc/nav.inc.php');
        ?>
        <div class="contenuto">
            <h1 class="title is-1 has-text-centered">Recupero password</h1>
            <div class="container is-fluid">
                <div class="columns">
                    <form action="/ajax/recupero.php" class="column is-4 is-offset-4 has-text-centered">
                        <div class="field">
                            <div class="control">
                                <input class="input is-primary" type="text" name="email" placeholder="E-Mail"/>
                            </div>
                        </div>
                        <p style="margin: 10px 0" class="is-size-7 has-text-grey">Se l'indirizzo email che hai inserito
                            è associato ad un account valido riceverai una nuova password (potrai cambiarla
                            successivamente effettuando l'accesso e recandoti nelle impostazioni).</p>
                        <div class="notification">
                            <h3 class="is-3">Verifica di sicurezza</h3>
                            <img class="captcha-img" src="/ajax/captcha.php" alt/>
                            <div class="field">
                                <div class="control">
                                    <input class="input is-primary" type="text" name="captcha"
                                           placeholder="Codice di verifica"/>
                                </div>
                            </div>
                        </div>
                        <button class="button is-primary">Recupera</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        include_once('../inc/footer.inc.php');
        ?>
    </body>
</html>
