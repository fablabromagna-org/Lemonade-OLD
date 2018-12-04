<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');

use \FabLabRomagna\Utente;
use \FabLabRomagna\Autenticazione;
use \FabLabRomagna\Permesso;
use \FabLabRomagna\SQLOperator\Equals;
use \FabLabRomagna\SQLOperator\NotEquals;
use \FabLabRomagna\Log;
use \FabLabRomagna\Scuola;

try {
    if (!\FabLabRomagna\Firewall::controllo()) {
        \FabLabRomagna\Firewall::firewall_redirect();
    }

    $sessione = Autenticazione::get_sessione_attiva();

    if ($sessione === null) {
        header('Location: /login.php');
        exit();
    }

    $sessione->aggiorna_token(true);

    $utente = Utente::ricerca([
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
    $permessi = Permesso::what_can_i_do($utente);

    if (!$permessi['gestione.scuola.visualizzare']['reale']) {
        header('Location: /dashboard.php');
        exit();
    }

} catch (Exception $e) {
    Log::crea(null, 3, '/gestione/scuole/scuola.php', 'page_request',
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

        $ricerca = false;

        $cod_mec = $_GET['cod_mec'];

        try {
            $ricerca = Scuola::ricerca([
                new Equals('codice', $cod_mec)
            ]);
        } catch (Exception $e) {
            Log::crea($utente, 3, '/gestione/scuole/scuola.php', 'estrazione_scuola',
                'Impossibile completare la richiesta.', (string)$e);
            echo '<h1 class="title is-1 has-text-centered">Impossibile completare la richiesta.</h1>';
        }


        if ($ricerca !== false):
            if (count($ricerca) === 0):
                echo '<h1 class="title is-1 has-text-centered">Scuola insesistente!</h1>';
            else:

                $ricerca = $ricerca->risultato[0];

                /**
                 * @var Scuola $ricerca
                 */

                Log::crea($utente, 0, '/gestione/scuole/scuola.php', 'visualizzazione_scuola',
                    'L\'utente ha visualizzato la scuola ' . $ricerca, (string)$e);
                ?>
                <div class="container contenuto">
                    <h1 class="title is-1 has-text-centered"><?php echo $ricerca->denominazione; ?></h1>

                    <div class="box">
                        <h3 class="title is-3">Dati della scuola</h3>
                        <div>
                            <div class="columns">
                                <div class="column is-half">
                                    <p>Denominazione: <b><?php echo htmlspecialchars($ricerca->denominazione); ?></b>
                                    </p>
                                    <p>Codice meccanografico: <b><?php echo $ricerca->codice; ?></b></p>
                                    <p>Indirizzo: <b><?php echo $ricerca->indirizzo; ?></b></p>
                                    <p>Comune: <b><?php echo $ricerca->comune; ?></b></p>
                                    <p>Provincia: <b><?php echo $ricerca->provincia; ?></b></p>
                                    <p>Regione: <b><?php echo $ricerca->regione; ?></b></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            endif;
        endif;
        include_once('../../inc/footer.inc.php');
        ?>
    </body>
</html>
