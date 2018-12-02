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

Log::crea($utente, 0, '/gestione/logs.php', 'view',
    'L\'utente ha aperto la finestra di ricerca log con i seguenti parametri: ' . $_SERVER['QUERY_STRING']);
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <?php
        require_once('../../inc/header.inc.php');
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                $('.avanzate').hide()

                $('#avanzate').click(function () {
                    $('.avanzate').toggle()
                })

            })
        </script>
    </head>
    <body>
        <?php
        include_once('../../inc/nav.inc.php');

        $dati = [];

        // Pulisco i dati

        $n_risultati = isset($_GET['nRisultati']) ? (int)trim($_GET['nRisultati']) : 0;
        $pagina = isset($_GET['p']) ? (int)trim($_GET['p']) : 0;

        if ($n_risultati < 1) {
            $n_risultati = 10;

        } elseif ($n_risultati > 100) {
            $n_risultati = 100;
        }

        if ($pagina < 1) {
            $pagina = 1;
        }


        ?>
        <div class="container is-fluid contenuto">
            <h1 class="title is-1 has-text-centered">Ricerca log</h1>
            <form method="get">


            </form>
            <?php
            $ricerca = false;
            try {
                $order = HTMLDataGrid::dataTableOrder2array();
                $order_ok = [];
                $fields = Log::getDataGridTableHeaders();

                foreach ($order as $value) {

                    if (isset($fields[$value['column']])) {
                        $order_ok[] = [
                            $value['column'],
                            !(bool)$value['order']
                        ];
                    }
                }

                if (count($order_ok) === 0) {
                    $order_ok[] = ['id_log', false];
                }

                $ricerca = Log::ricerca($dati, $n_risultati, $n_risultati * ($pagina - 1), $order_ok);
            } catch (Exception $e) {

                echo '<p style="margin-top: 20px;">Impossibile completare la richiesta.</p>';
                Log::crea($utente, 3, '/gestione/logs.php', 'page_request',
                    'Impossibile completare la richiesta.', (string)$e);
            }

            if ($ricerca !== false):


                $dataset = new HTMLDataGrid($ricerca);
                $dataset->remove_field('debug');
                echo $dataset->render([
                    'pagina_attuale' => $pagina,
                    'qs_pagina' => 'p',
                    'headers' => Log::getDataGridTableHeaders()
                ]);
            endif;
            ?>
        </div>
        <?php
        include_once('../../inc/footer.inc.php');
        ?>
    </body>
</html>
