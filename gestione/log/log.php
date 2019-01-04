<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');

use \FabLabRomagna\Utente;
use \FabLabRomagna\Log;
use \FabLabRomagna\SQLOperator\Like;
use \FabLabRomagna\SQLOperator\Equals;
use \FabLabRomagna\SQLOperator\NotEquals;
use FabLabRomagna\Data\HTMLDataGrid;

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

    if (!$permessi['gestione.sistema.visualizzare_log']['reale']) {
        header('Location: /dashboard.php');
        exit();
    }

} catch (Exception $e) {
    Log::crea(null, 3, '/gestione/logs.php', 'page_request',
        'Impossibile completare la richiesta.', (string)$e);
    reply(500, 'Internal Server Error');
}
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <?php
        $titolo_pagina = 'Log';
        require_once('../../inc/header.inc.php');
        ?>
    </head>
    <body>
        <?php
        include_once('../../inc/nav.inc.php');
        ?>
        <div class="container is-fluid contenuto">
            <h1 class="title is-1 has-text-centered">Visualizza log</h1>
            <?php
            $ricerca = false;
            try {

                $ricerca = Log::ricerca(array(
                    new Equals('id_log', $_GET['id'])
                ));

                Log::crea($utente, 0, '/gestione/log.php', 'page_request',
                    'L\'utente ha visualizzato i log n. ' . $_GET['id']);
            } catch (Exception $e) {

                echo '<p style="margin-top: 20px;">Impossibile completare la richiesta.</p>';
                Log::crea($utente, 3, '/gestione/log.php', 'page_request',
                    'Impossibile completare la richiesta.', (string)$e);
            }

            if ($ricerca !== false):
                if (count($ricerca) !== 1):
                    echo '<p style="margin-top: 20px;">Log inesistente!</p>';

                else:

                    $log = $ricerca->risultato[0];

                    /**
                     * @var Log $log
                     */

                    $utente_ricerca = Utente::ricerca(array(
                        new Equals('id_utente', $log->id_utente)
                    ));

                    if (count($utente_ricerca) !== 1) {
                        $utente_ricerca = false;
                    } else {
                        $utente_ricerca = $utente_ricerca->risultato[0];
                    }
                    ?>
                    <div class="box">
                        <?php
                        if ($utente_ricerca === false):
                            ?>
                            <p>Utente: non disponibile.</p>
                        <?php
                        else:
                            ?>
                            <p>Utente: <b><a
                                            href="/gestione/utenti/utente.php?id=<?php echo $log->id_utente ?>"><?php echo $utente->nome . ' ' . $utente->cognome ?></a></b>.
                            </p>
                            <p>ID log: <b><?php echo $log->id_log ?></b></p>
                            <p>IP: <b><?php echo $log->ip ?></b></p>
                            <p>Pacchetto/percorso: <b><?php echo $log->pacchetto ?></b></p>
                            <p>Oggetto: <b><?php echo $log->oggetto ?></b></p>
                            <p>Messaggio: <b><?php echo $log->testo ?></b></p>
                            <p>Data: <b><?php echo date('d/m/Y H:i:s', $log->ts) ?></b></p>
                            <p>Livello: <b><?php echo $log->HTMLDataGridFormatter('livello') ?></b></p>
                        <?php
                        endif;
                        ?>
                    </div>

                    <?php
                    if ($log->debug !== null):
                        ?>
                        <div class="box">
                            <p><b>Informazioni riguardanti l'eccezione:</b></p>
                            <pre>
                                <?php
                                echo $log->debug;
                                ?>
                            </pre>
                        </div>
                    <?php

                    endif;
                    ?>
                <?php
                endif;

            endif;
            ?>
        </div>
        <?php
        include_once('../../inc/footer.inc.php');
        ?>
    </body>
</html>
