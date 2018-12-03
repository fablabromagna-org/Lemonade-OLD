<?php
require_once(__DIR__ . '/class/autoload.inc.php');

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
/**
 * \FabLabRomagna\File::salva_mem(\FabLabRomagna\DocSigner::firma(__DIR__ . '/tests/2018-10-29.pdf', 'Verbale', 231, 'VCD'), 'application/pdf');
 */
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
            <h1 class="title is-1 has-text-centered">Ciao <?php echo $utente->nome ?></h1>
            <div class="container">
                <div class="tile is-ancestor">
                    <div class="tile is-4 is-vertical is-parent">
                        <div class="tile notification is-primary is-child box">
                            <p class="title">Attestati</p>
                            <p></p>
                        </div>
                        <div class="tile notification is-success is-child box">
                            <p class="title">Presenze</p>
                            <p></p>
                        </div>
                    </div>
                    <div class="tile is-4 is-vertical is-parent">
                        <div class="tile notification is-warning is-child box">
                            <p class="title">Attestati</p>
                            <p></p>
                        </div>
                        <div class="tile notification is-info is-child box">
                            <p class="title">Presenze</p>
                            <p></p>
                        </div>
                    </div>
                    <div class="tile is-4 is-vertical is-parent">
                        <div class="tile notification is-danger is-child box">
                            <p class="title">Attestati</p>
                            <p></p>
                        </div>
                        <div class="tile notification is-dark is-child box">
                            <p class="title">Presenze</p>
                            <p></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include_once('inc/footer.inc.php');
        ?>
    </body>
</html>
