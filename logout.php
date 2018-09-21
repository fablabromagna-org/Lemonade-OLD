<?php
require_once(__DIR__ . '/class/autoload.inc.php');

try {
    if (!\FabLabRomagna\Firewall::controllo()) {
        \FabLabRomagna\Firewall::firewall_redirect();
    }

    $sessione = \FabLabRomagna\Autenticazione::get_sessione_attiva();
    if ($sessione === null) {
        header('Location: /login.php');
        exit();
    }

    $utente = \FabLabRomagna\Utente::ricerca([
        new FabLabRomagna\SQLOperator\Equals('id_utente', $sessione->id_utente)
    ]);

    if (count($utente) !== 1) {
        header('Location: /login.php');
        exit();
    }

    $utente = $utente->risultato[0];
    $permessi = \FabLabRomagna\Permesso::what_can_i_do($utente);

    if (!$permessi['gestione.utenti.visualizzare_utenti']['reale']) {
        header('Location: /dashboard.php');
        exit();
    }

    $sessione->termina(true);
    $sessione = null;

} catch (Exception $e) {
    \FabLabRomagna\Log::crea(null, 3, '/logout.php', 'page_request',
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
        <script type="text/javascript" src="js/accesso.js"></script>
    </head>
    <body>
        <?php
        include_once('./inc/nav.inc.php');
        ?>
        <div class="contenuto">
            <h1 class="title is-1 has-text-centered">Arrivederci</h1>
            <h3 class="subtitle is-3 has-text-centered">Ti sei disconnesso correttamente.</h3>
        </div>
        <?php
        include_once('inc/footer.inc.php');
        ?>
    </body>
</html>