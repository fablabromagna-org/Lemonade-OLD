<?php
require_once(__DIR__ . '/../../class/autoload.inc.php');
require_once(__DIR__ . '/../../vendor/autoload.php');

use FabLabRomagna\Utente;
use FabLabRomagna\Autenticazione;
use FabLabRomagna\SQLOperator\Equals;
use FabLabRomagna\SQLOperator\NotEquals;
use FabLabRomagna\Log;
use FabLabRomagna\Firewall;
use Aws\Ses\SesClient;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    reply(405, 'Method Not Allowed');
}

json();

try {
    $ip = Firewall::get_valid_ip();

    if (!Firewall::controllo()) {
        reply(429, 'Too Many Requests');
    }

    $client = new SesClient(array(
        'version' => '2010-12-01',
        'region' => AWS_REGION,
        'credentials' => [
            'key' => AWS_MAIL_KEY,
            'secret' => AWS_MAIL_SECRET,
        ]
    ));

    $sessione = Autenticazione::get_sessione_attiva();

    if ($sessione === null) {
        reply(401, 'Unauthorized', null, true);
    }

    $sessione->aggiorna_token(true);

    $utente = Utente::ricerca([
        new Equals('id_utente', $sessione->id_utente),
        new Equals('codice_attivazione', null),
        new NotEquals('sospeso', true),
        new NotEquals('secretato', true)
    ]);

    if (count($utente) !== 1) {
        reply(401, 'Unauthorized', null, true);
    }

    $utente = $utente->risultato[0];

    $permessi = \FabLabRomagna\Permesso::what_can_i_do($utente);

    if (!$permessi['gestione.utenti.modificare_anagrafiche']['reale']) {

        reply(401, 'Unauthorized', null, true);
    }

    $dati = json_decode(file_get_contents('php://input'), true);

    if ($dati === null) {
        reply(400, 'Bad Request', null, true);
    }

    if (!is_array($dati)) {
        reply(400, 'Bad Request', null, true);
    }

    $campi_modificabili = [
        'id_utente',
        'nome',
        'cognome',
        'email',
        'sospeso',
        'codice_attivazione',
        'sesso',
        'codice_fiscale',
        'data_nascita',
        'luogo_nascita'
    ];

    // Controllo che tutti i campi inviati siano tra quelli modificabili
    foreach ($dati as $key => $value) {
        if (!in_array($key, $campi_modificabili)) {
            reply(400, 'Bad Request', null, true);
        }
    }

    foreach ($dati as $key => $value) {
        if ($key === 'codice_attivazione') {
            $value = null;
        } elseif ($key === 'codice_fiscale' && $value === '') {
            $dati[$key] = null;
            $value = null;
        } elseif ($key === 'luogo_nascita' && $value === '') {
            $dati[$key] = null;
            $value = null;
        } elseif ($key === 'data_nascita') {

            if ($value === '') {
                $dati[$key] = null;
                $value = null;
            } else {
                $tmp = explode('/', $value);

                if (count($tmp) !== 3) {
                    reply(400, 'Bad Request', array(
                        'field' => $key
                    ), true);
                }

                if (!checkdate($tmp[1], $tmp[0], $tmp[2])) {
                    reply(400, 'Bad Request', array(
                        'field' => $key
                    ), true);
                }

                $value = strtotime($tmp[2] . '-' . $tmp[1] . '-' . $tmp[0]);
                $dati[$key] = $value;
            }
        }

        if (!Utente::valida_campo($key, $value)) {
            reply(400, 'Bad Request', array(
                'field' => $key
            ), true);
        }
    }

    $utenteModifica = Utente::ricerca([
        new Equals('id_utente', $dati['id_utente'])
    ]);

    if (count($utenteModifica) !== 1) {
        reply(400, 'Bad Request', null, true);
    }


    $utenteModifica = $utenteModifica->risultato[0];

    /**
     * @var Utente $utenteModifica
     */
    foreach ($dati as $key => $value) {
        if ($key === 'id_utente') {
            continue;
        }

        switch ($key) {
            case 'nome':
            case 'cognome':
            case 'sospeso':
            case 'sesso':
            case 'codice_fiscale':
            case 'data_nascita':
            case 'luogo_nascita':
                if ($utenteModifica->{$key} !== $value) {
                    $dato = $utenteModifica->{$key};

                    $utenteModifica->set_campo($key, $value);

                    Log::crea($utente, 1, '/ajax/anagrafiche/aggiorna.php', 'aggiornamento_anagrafiche',
                        'Aggiornata anagrafica (utente: ' . $utenteModifica->id_utente . ') ' . $key . ' da ' . $dato . ' a ' . $value);
                }
                break;

            case 'email':
                if ($utenteModifica->{$key} !== $value) {

                    $vecchio_indirizzo = $utenteModifica->email;

                    // Aggiorno l'indirizzo
                    $utenteModifica->set_campo($key, $value);

                    $codice = uniqid();
                    $utenteModifica->set_campo('codice_attivazione', $codice);

                    $link = URL_SITO . 'confermaMail.php?id=' . $utenteModifica->id_utente . '&c=' . $codice;

                    $email = \FabLabRomagna\TemplateEmail::ricerca(array(
                        new \FabLabRomagna\SQLOperator\Equals('nome', 'nuova_email')
                    ));

                    foreach ($utenteModifica->getDataGridFields() as $campo => $valore) {
                        $email->replace('utente.' . $campo, $valore);
                    }

                    $email->replace('link', $link);

                    $client->sendEmail([
                        'Destination' => [
                            'ToAddresses' => [$utenteModifica->email],
                        ],
                        'ReplyToAddresses' => [EMAIL_REPLY_TO],
                        'Source' => EMAIL_FROM,
                        'Message' => [
                            'Body' => [
                                'Html' => [
                                    'Charset' => 'UTF-8',
                                    'Data' => $email->file,
                                ]
                            ],
                            'Subject' => [
                                'Charset' => 'UTF-8',
                                'Data' => 'Verifica dell\'indirizzo email',
                            ]
                        ]
                    ]);

                    $email = \FabLabRomagna\TemplateEmail::ricerca(array(
                        new \FabLabRomagna\SQLOperator\Equals('nome', 'vecchia_email')
                    ));

                    foreach ($utenteModifica->getDataGridFields() as $campo => $valore) {
                        $email->replace('utente.' . $campo, $valore);
                    }

                    $client->sendEmail([
                        'Destination' => [
                            'ToAddresses' => [$vecchio_indirizzo],
                        ],
                        'ReplyToAddresses' => [EMAIL_REPLY_TO],
                        'Source' => EMAIL_FROM,
                        'Message' => [
                            'Body' => [
                                'Html' => [
                                    'Charset' => 'UTF-8',
                                    'Data' => $email->file,
                                ]
                            ],
                            'Subject' => [
                                'Charset' => 'UTF-8',
                                'Data' => 'Indirizzo email modificato',
                            ]
                        ]
                    ]);


                    Log::crea($utente, 1, '/ajax/anagrafiche/aggiorna.php', 'aggiornamento_anagrafiche',
                        'Aggiornata email (utente: ' . $utenteModifica->id_utente . ') da ' . $dato . ' a ' . $dati);
                }
                break;

            case 'codice_attivazione':
                if ($value === true && $utenteModifica->codice_attivazione !== null) {
                    $utenteModifica->set_campo('codice_attivazione', null);

                } elseif ($value === false && $utenteModifica->codice_attivazione === null) {
                    $codice = uniqid();
                    $utenteModifica->set_campo('codice_attivazione', $codice);

                    $link = URL_SITO . 'confermaMail.php?id=' . $utenteModifica->id_utente . '&c=' . $codice;

                    $email = \FabLabRomagna\TemplateEmail::ricerca(array(
                        new \FabLabRomagna\SQLOperator\Equals('nome', 'ripeti_verifica_email')
                    ));

                    foreach ($utenteModifica->getDataGridFields() as $campo => $valore) {
                        $email->replace('utente.' . $campo, $valore);
                    }

                    $email->replace('link', $link);

                    $client->sendEmail([
                        'Destination' => [
                            'ToAddresses' => [$utenteModifica->email],
                        ],
                        'ReplyToAddresses' => [EMAIL_REPLY_TO],
                        'Source' => EMAIL_FROM,
                        'Message' => [
                            'Body' => [
                                'Html' => [
                                    'Charset' => 'UTF-8',
                                    'Data' => $email->file,
                                ]
                            ],
                            'Subject' => [
                                'Charset' => 'UTF-8',
                                'Data' => 'Verifica dell\'indirizzo email',
                            ]
                        ]
                    ]);

                    Log::crea($utente, 1, '/ajax/anagrafiche/aggiorna.php', 'verifica_email',
                        'Richiesta verifica email (utente: ' . $utenteModifica->id_utente . ')');
                }
                break;
        }
    }

    reply(200, 'Ok', array(
        'redirect' => '/gestione/utenti/utente.php?id=' . $utenteModifica->id_utente
    ));

} catch (Exception $e) {
    reply(500, 'Internal Server Error', array(
        'alert' => 'Impossibile completare la richiesta.' . $e
    ), true);

    if ($utente instanceof Utente) {
        Log::crea($utente, 3, 'ajax/anagrafiche/aggiorna.php', 'update',
            'Impossibile completare la richiesta.', (string)$e);
    } else {
        Log::crea(null, 3, 'ajax/anagrafiche/aggiorna.php', 'update',
            'Impossibile completare la richiesta.', (string)$e);
    }

}
?>
