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

        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        $codice = isset($_GET['c']) ? trim($_GET['c']) : '';

        if (!preg_match('/^[0-9]{1,11}$/', $id) || !preg_match('/^[0-9a-f]{13}$/', $codice)):

            ?>
            <div class="contenuto">
                <h1 class="title is-1 has-text-centered">Errore</h1>
                <h3 class="subtitle is-3 has-text-centered">ID utente o codice di attivazione non valido.</h3>
            </div>
        <?php
        else:

            $utente = \FabLabRomagna\Utente::ricerca(array(
                new \FabLabRomagna\SQLOperator\Equals('id_utente', (int)$id),
                new \FabLabRomagna\SQLOperator\Equals('codice_attivazione', $codice)
            ));

            if (count($utente) === 1):
                $utente->risultato[0]->set_campo('codice_attivazione', null);
                ?>
                <div class="contenuto">
                    <h1 class="title is-1 has-text-centered">Fantastico!</h1>
                    <h3 class="subtitle is-3 has-text-centered">Hai verificato correttamente il tuo indirizzo
                        email.</h3>
                    <h3 class="subtitle is-3 has-text-centered">Ora puoi effettuare l'accesso.</h3>
                </div>
            <?php
            else:
                ?>
                <div class="contenuto">
                    <h1 class="title is-1 has-text-centered">Errore</h1>
                    <h3 class="subtitle is-3 has-text-centered">ID utente o codice di attivazione non valido.</h3>
                </div>
            <?php
            endif;

        endif;

        include_once('inc/footer.inc.php');
        ?>
    </body>
</html>
