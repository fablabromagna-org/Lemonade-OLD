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
        require_once('../../inc/header.inc.php');
        ?>
    </head>
    <body>
        <?php
        include_once('../../inc/nav.inc.php');
        ?>
        <form action="/ajax/utente/aggiorna.php" class="is-modal" method="post">
            <div id="modal-ticket" class="modal is-active">
                <div class="modal-background"></div>
                <div class="modal-card">
                    <header class="modal-card-head">
                        <p class="modal-card-title">Nuovo ticket</p>
                        <button class="delete" aria-label="close" type="reset"></button>
                    </header>
                    <input type="hidden" value="<?php echo $ricerca->id_utente; ?>" data-type="integer"
                           name="id_utente"/>
                    <section class="modal-card-body">
                        <div class="field">
                            <label for="oggetto"><b>Oggetto:</b></label>
                            <div class="control">
                                <input type="text" class="input is-primary" id="oggetto" name="oggetto"
                                       placeholder="Oggetto"/>
                            </div>
                        </div>
                        <div class="field">
                            <label for="descrizione"><b>Descrizione del problema:</b></label>
                            <div class="control">
                                <textarea type="text" class="textarea is-primary" id="descrizione" name="descrizione"
                                          placeholder="Descrizione del problema"></textarea>
                            </div>
                        </div>
                        <label for="descrizione"><b>Eventuali allegati:</b></label>
                        <div class="file has-name is-fullwidth">
                            <label class="file-label">
                                <input class="file-input" type="file" name="allegati" multiple/>
                                <span class="file-cta">
                                    <span class="file-icon">
                                       <i class="fas fa-upload"></i>
                                    </span>
                                    <span class="file-label">Scegli un file</span>
                                </span>
                                <span class="file-name"></span>
                            </label>
                        </div>
                    </section>
                    <footer class="modal-card-foot">
                        <button class="button" type="reset">Annulla</button>
                        <button class="button is-primary">Invia</button>
                    </footer>

                </div>
            </div>
        </form>
        <div class="contenuto">
            <h1 class="has-text-centered is-1 title">Supporto</h1>
            <h4 class="has-text-centered is-4 subtitle">In questa sezione pui richiedere aiuto tecnico e
                amministrativo.</h4>
            <div class="has-text-centered">
                <button class="button is-primary is-large open-modal" data-open="modal-ticket">Nuovo ticket</button>
            </div>
        </div>
        <?php
        include_once('../../inc/footer.inc.php');
        ?>
    </body>
</html>
