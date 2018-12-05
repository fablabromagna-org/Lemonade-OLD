<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');

use \FabLabRomagna\Utente;
use \FabLabRomagna\SQLOperator\Equals;
use \FabLabRomagna\SQLOperator\NotEquals;
use \FabLabRomagna\Log;
use \FabLabRomagna\RelazioneScolastica;

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
        exit();
    }

    $utente = $utente->risultato[0];
    $permessi = \FabLabRomagna\Permesso::what_can_i_do($utente);

    if (!$permessi['gestione.utenti.visualizzare_utenti']['reale']) {
        header('Location: /dashboard.php');
        exit();
    }

} catch (Exception $e) {
    Log::crea(null, 3, '/gestione/utenti/utente.php', 'page_request',
        'Impossibile completare la richiesta.', (string)$e);
    reply(500, 'Internal Server Error');
}
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
        <div class="container contenuto">
            <h1 class="title is-1 has-text-centered">Crea utente</h1>
            <div id="container" style="margin-top: 20px">
                <div class="box">
                    <h3 class="title is-3">Dati del nuovo utente</h3>
                    <div>
                        <form action="/ajax/utente/crea.php">
                            <div class="columns">
                                <div class="column is-half">
                                    <div class="field">
                                        <div class="control">
                                            <input class="input is-primary" type="text" name="nome"
                                                   placeholder="Nome"/>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <div class="control">
                                            <input class="input is-primary" type="text" name="cognome"
                                                   placeholder="Cognome"/>
                                        </div>
                                    </div>
                                    <div class="field">
                                        <div class="control">
                                            <input class="input is-primary" type="text" name="email"
                                                   placeholder="E-Mail"/>
                                        </div>
                                    </div>
                                    <input class="styled" type="checkbox" id="invio_mail" name="invio_mail" checked
                                           data-type="boolean">
                                    <label for="invio_mail">
                                        Non inviare email di verifica dell'indirizzo
                                    </label>
                                    <div style="margin-top: 20px;">
                                        <span class="tag is-warning"><b>IMPORTANTE</b></span>
                                        <p style="margin: 10px 0 0 0" class="is-size-7 has-text-grey">Se l'utente ha
                                            meno di 16 anni ricordati di caricare dopo la creazione, nel suo fascicolo
                                            personale, la liberatoria firmata da un genitore e di inserire i suoi dati
                                            anagrafici completi (es. data di nascita, scuola, ecc).</p>
                                    </div>
                                    <div style="margin-top: 20px;">
                                        <span class="tag is-info"><b>NOTA BENE</b></span>
                                        <p style="margin: 10px 0 0 0" class="is-size-7 has-text-grey">L'utente per
                                            effettuare il primo accesso deve cliccare su "ho dimenticato la password" o
                                            chiedere un ripristino della stessa.</p>
                                    </div>
                                </div>
                            </div>
                            <button class="button is-primary">Crea</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include_once('../../inc/footer.inc.php');
        ?>
    </body>
</html>
