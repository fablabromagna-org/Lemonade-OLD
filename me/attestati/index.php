<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');

use FabLabRomagna\SQLOperator\Equals;
use FabLabRomagna\SQLOperator\NotEquals;

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
        new Equals('id_utente', $sessione->id_utente),
        new Equals('codice_attivazione', null),
        new NotEquals('sospeso', true),
        new NotEquals('secretato', true)
    ]);

    if (count($utente) !== 1) {
        header('Location: /login.php');
        $sessione->termina();
        exit();
    }

    $utente = $utente->risultato[0];
    $permessi = \FabLabRomagna\Permesso::what_can_i_do($utente);

} catch (Exception $e) {
    \FabLabRomagna\Log::crea(null, 3, '/dashboard.php', 'page_request',
        'Impossibile completare la richiesta.', (string)$e);
    reply(500, 'Internal Server Error');
}

\FabLabRomagna\Log::crea($utente, 0, '/dashboard.php', 'view', 'L\'utente ha aperto la propria dashboard.');
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <?php
        $titolo_pagina = 'Attestati';
        require_once('../../inc/header.inc.php');
        ?>
    </head>
    <body>
        <?php
        include_once('../../inc/nav.inc.php');
        ?>
        <div class="contenuto">
            <h1 class="has-text-centered is-1 title">I tuoi attestati</h1>
            <h4 class="has-text-centered is-4 subtitle">In questa sezione puoi visualizzare i tuoi attestati.</h4>
        </div>
        <?php
        include_once('../../inc/footer.inc.php');
        ?>
    </body>
</html>
