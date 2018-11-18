<?php
require_once(__DIR__ . '/class/autoload.inc.php');

try {
    if (!\FabLabRomagna\Firewall::controllo()) {
        \FabLabRomagna\Firewall::firewall_redirect();
    }
} catch (Exception $e) {
    \FabLabRomagna\Log::crea(null, 3, '/confermaMail.php', 'page_request',
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
            <h1 class="title is-1 has-text-centered">Manca poco...</h1>
            <h3 class="subtitle is-3 has-text-centered">Per completare la registrazione,<br/>verifica il tuo account
                visitando il link indicato nell'email.</h3>
        </div>
        <?php
        include_once('inc/footer.inc.php');
        ?>
    </body>
</html>
