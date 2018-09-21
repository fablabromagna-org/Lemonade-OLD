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

    $sessione->aggiorna_token(true);

    $utente = \FabLabRomagna\Utente::ricerca([
        new FabLabRomagna\SQLOperator\Equals('id_utente', $sessione->id_utente)
    ]);

    if (count($utente) !== 1) {
        header('Location: /login.php');
        exit();
    }

    $utente = $utente->risultato[0];
    $permessi = \FabLabRomagna\Permesso::what_can_i_do($utente);

} catch (Exception $e) {
    \FabLabRomagna\Log::crea(null, 3, '/login.php', 'page_request',
        'Impossibile completare la richiesta.', (string)$e);
    reply(500, 'Internal Server Error');
}

\FabLabRomagna\Log::crea($utente, 0, '/dashboard.php', 'view', 'L\'utente ha aperto la propria dashboard.');
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <?php
        require_once('inc/header.inc.php');
        ?>
    </head>
    <body>
        <?php
        include_once('inc/nav.inc.php');
        ?>
        <div class="contenuto">

            <h1 class="title is-1 has-text-centered">Benvenuto <?php echo $utente->nome ?></h1>

        </div>
        <?php
        include_once('inc/footer.inc.php');
        ?>
    </body>
</html>
