<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');

use \FabLabRomagna\Utente;
use \FabLabRomagna\Gruppo;
use \FabLabRomagna\Log;
use \FabLabRomagna\SQLOperator\Like;
use \FabLabRomagna\SQLOperator\Equals;
use \FabLabRomagna\SQLOperator\NotEquals;
use \FabLabRomagna\Data\HTMLDataGrid;

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

    /**
     * @var Utente $utente
     */

    $permessi = \FabLabRomagna\Permesso::what_can_i_do($utente);

    if (!$permessi['gestione.gruppi.visualizzare']['reale']) {
        header('Location: /dashboard.php');
        exit();
    }

} catch (Exception $e) {
    Log::crea(null, 3, '/gestione/gruppi/index.php', 'page_request',
        'Impossibile completare la richiesta.', (string)$e);
    reply(500, 'Internal Server Error');
}

Log::crea($utente, 0, '/gestione/gruppi/index.php', 'view',
    'L\'utente ha aperto la finestra di ricerca gruppi con i seguenti parametri: ' . $_SERVER['QUERY_STRING']);
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <?php
        $titolo_pagina = 'Gruppi';
        require_once('../../inc/header.inc.php');
        ?>
        <script type="text/javascript" src="/js/gestione/gruppi/index.js"></script>
    </head>
    <body>
        <?php
        include_once('../../inc/nav.inc.php');

        $dati = [];

        $dati[] = new Equals('eliminato', false);

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
        <form action="/ajax/gruppo/gruppo.php" method="post" class="is-modal">
            <div id="modal-gruppo" class="modal">
                <div class="modal-background"></div>
                <div class="modal-card">
                    <header class="modal-card-head">
                        <p class="modal-card-title">Nuovo gruppo</p>
                        <button class="delete" aria-label="close" type="reset"></button>
                    </header>
                    <section class="modal-card-body">
                        <div class="field">
                            <label for="nome"><b>Nome:</b></label>
                            <div class="control">
                                <input type="text" class="input is-primary" id="nome" name="nome"
                                       placeholder="Nome"/>
                            </div>
                        </div>
                        <div class="field">
                            <label for="descrizione"><b>Descrizione del gruppo:</b></label>
                            <div class="control">
                                <textarea type="text" class="textarea" id="descrizione" name="descrizione"
                                          placeholder="Descrizione del gruppo"></textarea>
                            </div>
                        </div>
                        <label for="default">
                            <input type="checkbox" id="default" name="default"/>
                            Gruppo di default per i nuovi iscritti
                        </label>
                    </section>
                    <footer class="modal-card-foot">
                        <button class="button" type="reset">Annulla</button>
                        <button class="button is-primary">Crea</button>
                    </footer>
                </div>
            </div>
        </form>
        <div class="container is-fluid contenuto">
            <h1 class="title is-1 has-text-centered">Gruppi</h1>
            <?php
            if ($permessi['gestione.gruppi.creare']['reale']):
                ?>
                <div class="has-text-centered">
                    <button class="button is-primary is-large open-modal" data-open="modal-gruppo">Nuovo gruppo</button>
                </div>
            <?php
            endif;

            $ricerca = false;
            try {
                $order = HTMLDataGrid::dataTableOrder2array();
                $order_ok = [];
                $fields = Gruppo::getDataGridTableHeaders();

                foreach ($order as $value) {

                    if (isset($fields[$value['column']])) {
                        $order_ok[] = [
                            $value['column'],
                            !(bool)$value['order']
                        ];
                    }
                }

                if (count($order_ok) === 0) {
                    $order_ok[] = ['id_gruppo', true];
                }

                $ricerca = Gruppo::ricerca($dati, $n_risultati, $n_risultati * ($pagina - 1), $order_ok);
            } catch (Exception $e) {

                echo '<p style="margin-top: 20px;">Impossibile completare la richiesta.</p>';
                Log::crea($utente, 3, '/gestione/gruppi/index.php', 'page_request',
                    'Impossibile completare la richiesta.', (string)$e);
            }


            if ($ricerca !== false):
                try {
                    $dataset = new HTMLDataGrid($ricerca);

                    $dataset->remove_field('eliminato');
                    $dataset->aggiungiColonna('Azioni',
                        '<a class="button is-primary" onclick="apri_permessi({{id_gruppo}})">Permessi</a> ' .
                        '<a class="button is-primary" onclick="apri_modifica({{id_gruppo}})">Modifica</a> ' .
                        '<a class="button is-danger" onclick="elimina({{id_gruppo}})">Elimina</a>');

                    echo $dataset->render([
                        'pagina_attuale' => $pagina,
                        'qs_pagina' => 'p',
                        'headers' => Gruppo::getDataGridTableHeaders()
                    ]);
                } catch (Exception $e) {
                    echo '<p style="margin-top: 20px;">Impossibile completare la richiesta.</p>';
                }
            endif;


            ?>
        </div>
        <?php
        include_once('../../inc/footer.inc.php');
        ?>
    </body>
</html>
