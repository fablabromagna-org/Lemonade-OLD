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
            <h1 class="title is-1 has-text-centered">Ops... 404</h1>
            <h3 class="subtitle is-3 has-text-centered">Non abbiamo trovato la risorsa che hai richiesto...</h3>
        </div>
        <?php
        include_once('inc/footer.inc.php');
        ?>
    </body>
</html>
