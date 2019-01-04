<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');

use \FabLabRomagna\Utente;
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

    if (!$permessi['gestione.utenti.visualizzare_utenti']['reale']) {
        header('Location: /dashboard.php');
        exit();
    }

} catch (Exception $e) {
    Log::crea(null, 3, '/gestione/utenti/ricerca.php', 'page_request',
        'Impossibile completare la richiesta.', (string)$e);
    reply(500, 'Internal Server Error');
}

Log::crea($utente, 0, '/gestione/utenti/ricerca.php', 'view',
    'L\'utente ha aperto la finestra di ricerca utente con i seguenti parametri: ' . $_SERVER['QUERY_STRING']);
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <?php
        $titolo_pagina = 'Ricerca utenti';
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
        $nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';
        $cognome = isset($_GET['cognome']) ? trim($_GET['cognome']) : '';
        $email = isset($_GET['email']) ? trim($_GET['email']) : '';
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        $cf = isset($_GET['cf']) ? trim($_GET['cf']) : '';
        $conferma_email = isset($_GET['confermaEmail']) ? trim($_GET['confermaEmail']) : '';
        $sesso = isset($_GET['sesso']) ? trim($_GET['sesso']) : '';
        $sospensione = isset($_GET['sospensione']) ? trim($_GET['sospensione']) : 1;
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

        if ($nome !== '') {
            $dati[] = new Like('nome', $nome);
        }

        if ($cognome !== '') {
            $dati[] = new Like('cognome', $cognome);
        }

        if ($email !== '') {
            $dati[] = new Like('email', $email);
        }

        if ($id !== '') {
            $dati[] = new Like('id_utente', $id);
        }

        if ($cf !== '') {
            $dati[] = new Like('codice_fiscale', $cf);
        }

        if ($conferma_email === '2') {
            $dati[] = new NotEquals('codice_attivazione', null);

        } elseif ($conferma_email !== '1') {
            $dati[] = new Equals('codice_attivazione', null);
        }

        if ($sesso == 1) {
            $dati[] = new Equals('sesso', false);
        } elseif ($sesso == 2) {
            $dati[] = new Equals('sesso', true);
        } elseif ($sesso == 3) {
            $dati[] = new Equals('sesso', null);
        }

        if ($sospensione == 0) {
            $dati[] = new Equals('sospeso', false);
        } elseif ($sospensione == 2) {
            $dati[] = new Equals('sospeso', true);
        }
        ?>
        <div class="container is-fluid contenuto">
            <h1 class="title is-1 has-text-centered">Ricerca utenti</h1>
            <form method="get" class="no-traditional-sender">
                <div class="columns">
                    <div id="form_prop" class="column is-4 is-offset-4 has-text-centered">
                        <div class="columns">
                            <div class="column is-half">
                                <div class="field">
                                    <div class="control">
                                        <input class="input is-primary" type="text" name="nome"
                                               placeholder="Nome"
                                               value="<?php echo $nome; ?>"/>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-half">
                                <div class="field">
                                    <div class="control">
                                        <input class="input is-primary" type="text" name="cognome"
                                               placeholder="Cognome"
                                               value="<?php echo $cognome; ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="columns">
                            <div class="column">
                                <div class="field">
                                    <div class="control">
                                        <input class="input is-primary" type="text" name="email"
                                               placeholder="E-Mail"
                                               value="<?php echo $email; ?>"/>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-3">
                                <div class="field">
                                    <div class="control">
                                        <input class="input is-primary" type="text" name="id" placeholder="ID"
                                               value="<?php echo $id; ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="columns">
                            <div class="column is-half">
                                <div class="field">
                                    <div class="control">
                                        <input class="input is-primary" type="text" name="cf"
                                               placeholder="Codice Fiscale"
                                               value="<?php echo $cf; ?>"/>
                                    </div>
                                </div>
                            </div>
                            <div class="column is-half">
                                <a id="avanzate" class="button">Avanzate</a>
                            </div>
                        </div>
                        <div class="columns avanzate">
                            <div class="column is-half">
                                <label for="confermaEmail">Verifica E-Mail</label>
                                <div class="select">
                                    <select name="confermaEmail" id="confermaEmail">
                                        <option value="1" <?php echo $conferma_email == '1' ? 'selected' : ''; ?>>
                                            Tutti
                                        </option>
                                        <option value="0" <?php echo $conferma_email != '1' && $conferma_email != '2' ? 'selected' : ''; ?>>
                                            Solo verificate
                                        </option>
                                        <option value="2" <?php echo $conferma_email == '2' ? 'selected' : ''; ?>>
                                            Solo non verificate
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="column is-half">
                                <label for="sospensione">Sospensione</label>
                                <div class="select">
                                    <select name="sospensione" id="sospensione">
                                        <option value="1" <?php echo $sospensione != '0' && $sospensione != '2' ? 'selected' : ''; ?>>
                                            Tutti
                                        </option>
                                        <option value="0" <?php echo $sospensione == '0' ? 'selected' : ''; ?>>Non
                                            attiva
                                        </option>
                                        <option value="2" <?php echo $sospensione == '2' ? 'selected' : ''; ?>>Attiva
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="columns avanzate">
                            <div class="column is-half">
                                <label for="sesso">Sesso</label>
                                <div class="select">
                                    <select name="sesso" id="sesso">
                                        <option value="0" <?php echo $sesso != '1' && $sesso != '2' && $sesso != '3' ? 'selected' : ''; ?>>
                                            Tutti
                                        </option>
                                        <option value="1" <?php echo $sesso === '1' ? 'selected' : ''; ?>>Maschile
                                        </option>
                                        <option value="2" <?php echo $sesso == '2' ? 'selected' : ''; ?>>Femminile
                                        </option>
                                        <option value="3" <?php echo $sesso == '3' ? 'selected' : ''; ?>>Non
                                            specificato
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="column is-half">
                                <label for="nRisultati">N. risultati per pagina</label>
                                <div class="select">
                                    <select name="nRisultati" id="nRisultati">
                                        <option value="10" <?php echo $n_risultati != '25' && $n_risultati != '50' && $n_risultati != '100' ? 'selected' : ''; ?>>
                                            10
                                        </option>
                                        <option value="25" <?php echo $n_risultati === '25' ? 'selected' : ''; ?>>25
                                        </option>
                                        <option value="50" <?php echo $n_risultati == '50' ? 'selected' : ''; ?>>50
                                        </option>
                                        <option value="100" <?php echo $n_risultati == '100' ? 'selected' : ''; ?>>100
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="buttons has-addons is-centered">
                            <a href="/gestione/utenti/ricerca.php" class="button">Pulisci</a>
                            <a href="/gestione/utenti/esporta.php?<?php echo $_SERVER['QUERY_STRING'] ?>"
                               class="button" download>Esporta</a>
                            <input class="button is-primary" type="submit" value="Cerca"/>
                        </div>
                    </div>
                </div>
            </form>
            <?php
            $ricerca = false;
            try {
                $order = HTMLDataGrid::dataTableOrder2array();
                $order_ok = [];
                $fields = Utente::getDataGridTableHeaders();

                foreach ($order as $value) {

                    if (isset($fields[$value['column']])) {
                        $order_ok[] = [
                            $value['column'],
                            !(bool)$value['order']
                        ];
                    }
                }

                if (count($order_ok) === 0) {
                    $order_ok[] = ['id_utente', true];
                }

                $ricerca = Utente::ricerca($dati, $n_risultati, $n_risultati * ($pagina - 1), $order_ok);
            } catch (Exception $e) {

                echo '<p style="margin-top: 20px;">Impossibile completare la richiesta.</p>';
                Log::crea($utente, 3, '/gestione/utenti/ricerca.php', 'page_request',
                    'Impossibile completare la richiesta.', (string)$e);
            }


            if ($ricerca !== false):
                try {
                    $dataset = new HTMLDataGrid($ricerca);
                    $dataset->remove_field('secretato');
                    $dataset->remove_field('id_foto');

                    if (!$permessi['gestione.utenti.visualizzare_anagrafiche']['reale']) {
                        $dataset->remove_field('data_nascita');
                        $dataset->remove_field('codice_fiscale');
                        $dataset->remove_field('luogo_nascita');
                        $dataset->remove_field('sesso');
                    }

                    echo $dataset->render([
                        'pagina_attuale' => $pagina,
                        'qs_pagina' => 'p',
                        'headers' => Utente::getDataGridTableHeaders()
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
