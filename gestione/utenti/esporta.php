<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');

use \FabLabRomagna\Utente;
use \FabLabRomagna\Log;
use \FabLabRomagna\SQLOperator\Like;
use \FabLabRomagna\SQLOperator\Equals;
use \FabLabRomagna\SQLOperator\NotEquals;
use FabLabRomagna\Data\CSVDataGrid;
use FabLabRomagna\Data\HTMLDataGrid;

try {
    if (!\FabLabRomagna\Firewall::controllo()) {
        \FabLabRomagna\Firewall::firewall_redirect();
    }

    $sessione = \FabLabRomagna\Autenticazione::get_sessione_attiva();

    if ($sessione === null) {
        exit();
    }

    $sessione->aggiorna_token(true);

    $utente = \FabLabRomagna\Utente::ricerca([
        new FabLabRomagna\SQLOperator\Equals('id_utente', $sessione->id_utente)
    ]);

    if (count($utente) !== 1) {
        exit();
    }

    $utente = $utente->risultato[0];

    $permessi = \FabLabRomagna\Permesso::what_can_i_do($utente);

    if (!$permessi['gestione.utenti.visualizzare_utenti']['reale']) {
        exit();
    }

} catch (Exception $e) {
    Log::crea(null, 3, '/gestione/utenti/esporta.php', 'page_request',
        'Impossibile completare la richiesta.', (string)$e);
    reply(500, 'Internal Server Error');
}

Log::crea($utente, 0, '/gestione/utenti/esporta.php', 'view',
    'L\'utente ha esportato la finestra di ricerca utente con i seguenti parametri: ' . $_SERVER['QUERY_STRING']);

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=export_" . time() . ".csv");
header("Pragma: no-cache");
header("Expires: 0");

$dati = [];

// Pulisco i dati
$nome = isset($_GET['nome']) ? trim($_GET['nome']) : '';
$cognome = isset($_GET['cognome']) ? trim($_GET['cognome']) : '';
$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$id = isset($_GET['id']) ? trim($_GET['id']) : '';
$cf = isset($_GET['cf']) ? trim($_GET['cf']) : '';
$conferma_email = isset($_GET['confermaEmail']) ? trim($_GET['confermaEmail']) : '';
$sesso = isset($_GET['sesso']) ? trim($_GET['sesso']) : '';
$sospensione = isset($_GET['sospensione']) ? trim($_GET['sospensione']) : '';
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

$ricerca = false;
try {
    $order = HTMLDataGrid::dataTableOrder2array();
    $order_ok = [];
    $fields = utente::getDataGridTableHeaders();

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
    $dataset = new CSVDataGrid($ricerca);
    $dataset->remove_field('secretato');
    $dataset->remove_field('id_foto');

    if (!$permessi['gestione.utenti.visualizzare_anagrafiche']['reale']) {
        $dataset->remove_field('data_nascita');
        $dataset->remove_field('codice_fiscale');
        $dataset->remove_field('luogo_nascita');
        $dataset->remove_field('sesso');
    }

    echo $dataset->render([]);
endif;
