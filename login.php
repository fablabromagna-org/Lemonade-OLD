<?php
require_once(__DIR__ . '/class/autoload.inc.php');

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
        require_once('./inc/header.inc.php');
        ?>
    </head>
    <body>
        <?php
        include_once('./inc/nav.inc.php');
        ?>
        <div class="contenuto">
            <h1 class="title is-1 has-text-centered">Accesso</h1>
            <div class="container is-fluid">
                <div class="columns">
                    <form action="/ajax/accesso.php" class="column is-4 is-offset-4 has-text-centered">
                        <div class="field">
                            <div class="control">
                                <input class="input is-primary" type="text" name="email" placeholder="E-Mail"/>
                            </div>
                        </div>
                        <div class="field">
                            <div class="control">
                                <input class="input is-primary" type="password" name="password" placeholder="Password"/>
                            </div>
                        </div>
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
                        <button class="button is-primary">Accedi</button>
                        <a class="is-block" href="/account/recupero.php">Ho dimenticato la password</a>
                    </form>
                </div>
            </div>
        </div>
        <?php
        include_once('inc/footer.inc.php');
        ?>
    </body>
</html>
