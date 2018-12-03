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
        <script type="text/javascript">
            $(document).ready(function () {
                $('#ruolo').change(function () {
                    if (parseInt($(this).val()) > 0) {
                        $('#solo-studente').hide()

                    } else
                        $('#solo-studente').show()
                })
            })

            function elimina_scuola (id_utente, id_relazione) {
                $.ajax({
                    method: 'delete'
                    , url: '/ajax/utente/scuola.php'
                    , dataType: 'json'
                    , cache: false
                    , contentType: 'application/json; charset=utf-8'
                    , data: JSON.stringify({
                        id_utente: id_utente,
                        id_relazione: id_relazione
                    })
                    , complete: function (res) {
                        if (res.responseJSON !== undefined) {
                            if (res.responseJSON.alert !== undefined)
                                alert(res.responseJSON.alert)

                            if (res.responseJSON.redirect !== undefined)
                                location.href = res.responseJSON.redirect

                        } else if (res.status !== 200 && res.status !== 204)
                            alert('Impossibile completare la richiesta.')
                    }
                })
            }
        </script>
    </head>
    <body>
        <?php
        include_once('../../inc/nav.inc.php');

        $ricerca = false;

        $id_utente = $_GET['id'];

        if (!preg_match('/^[0-9]{1,11}$/', $id_utente)) {
            echo '<h1 class="title is-1 has-text-centered">ID utente non valido.</h1>';

        } else {

            try {
                $ricerca = Utente::ricerca([
                    new Equals('id_utente', $id_utente)
                ]);
            } catch (Exception $e) {
                Log::crea($utente, 3, '/gestione/utenti/utente.php', 'estrazione_utente',
                    'Impossibile completare la richiesta.', (string)$e);
                echo '<h1 class="title is-1 has-text-centered">Impossibile completare la richiesta.</h1>';
            }
        }

        if ($ricerca !== false):
            if (count($ricerca) === 0):
                echo '<h1 class="title is-1 has-text-centered">Utente insesistente!</h1>';
            else:

                $ricerca = $ricerca->risultato[0];

                /**
                 * @var Utente $ricerca
                 */

                Log::crea($utente, 0, '/gestione/utenti/utente.php', 'visualizzazione_profilo',
                    'L\'utente ha visualizzato il profilo con ID ' . $ricerca->id_utente, (string)$e);
                ?>
                <form action="/ajax/utente/aggiorna.php" class="is-modal">
                    <div id="modal-sesso" class="modal">
                        <div class="modal-background"></div>
                        <div class="modal-card">
                            <header class="modal-card-head">
                                <p class="modal-card-title">Modifica sesso</p>
                                <button class="delete" aria-label="close" type="reset"></button>
                            </header>
                            <input type="hidden" value="<?php echo $ricerca->id_utente; ?>" data-type="integer"
                                   name="id_utente"/>
                            <section class="modal-card-body">
                                <p>Sesso attuale:
                                    <b><?php echo $ricerca->sesso === null ? 'Non specificato' : ($ricerca->sesso ? 'Femminile' : 'Maschile'); ?></b>
                                </p>
                                <p style="margin-top: 20px">Modifica in:</p>
                                <div class="select">
                                    <select name="sesso" data-type="boolean">
                                        <option <?php if ($ricerca->sesso === null) echo 'selected' ?> value="null">Non
                                            specificato
                                        </option>
                                        <option <?php if ($ricerca->sesso === false) echo 'selected' ?> value="false">
                                            Maschile
                                        </option>
                                        <option <?php if ($ricerca->sesso === true) echo 'selected' ?> value="true">
                                            Femminile
                                        </option>
                                    </select>
                                </div>
                            </section>
                            <footer class="modal-card-foot">
                                <button class="button" type="reset">Annulla</button>
                                <button class="button is-primary">Salva</button>
                            </footer>

                        </div>
                    </div>
                </form>
                <form action="/ajax/utente/aggiorna.php" class="is-modal">
                    <div id="modal-cf" class="modal">
                        <div class="modal-background"></div>
                        <div class="modal-card">
                            <header class="modal-card-head">
                                <p class="modal-card-title">Modifica codice fiscale</p>
                                <button class="delete" aria-label="close" type="reset"></button>
                            </header>
                            <input type="hidden" value="<?php echo $ricerca->id_utente; ?>" data-type="integer"
                                   name="id_utente"/>
                            <section class="modal-card-body">
                                <p>Codice fiscale attuale:
                                    <b><?php echo $ricerca->sesso === null ? 'Non specificato' : $ricerca->codice_fiscale; ?></b>
                                </p>
                                <p style="margin-top: 20px">Modifica in:</p>
                                <div class="field">
                                    <div class="control">
                                        <input class="input is-primary" type="text" name="codice_fiscale"
                                               placeholder="Codice fiscale"
                                               value="<?php echo $ricerca->codice_fiscale; ?>"/>
                                    </div>
                                </div>
                            </section>
                            <footer class="modal-card-foot">
                                <button class="button" type="reset">Annulla</button>
                                <button class="button is-primary">Salva</button>
                            </footer>

                        </div>
                    </div>
                </form>
                <form action="/ajax/utente/aggiorna.php" class="is-modal">
                    <div id="modal-data" class="modal">
                        <div class="modal-background"></div>
                        <div class="modal-card">
                            <header class="modal-card-head">
                                <p class="modal-card-title">Modifica data di nascita</p>
                                <button class="delete" aria-label="close" type="reset"></button>
                            </header>
                            <input type="hidden" value="<?php echo $ricerca->id_utente; ?>" data-type="integer"
                                   name="id_utente"/>
                            <section class="modal-card-body">
                                <p>Data di nascita attuale:
                                    <b><?php echo $ricerca->data_nascita === null ? 'Non specificato' : date('d/m/Y',
                                            $ricerca->data_nascita); ?></b>
                                </p>
                                <p style="margin-top: 20px">Modifica in:</p>
                                <div class="field">
                                    <div class="control">
                                        <input class="input is-primary calendar-input" type="text" name="data_nascita"
                                               placeholder="Data di nascita"
                                               value="<?php echo $ricerca->data_nascita === null ? '' : date('d/m/Y',
                                                   $ricerca->data_nascita) ?>"/>
                                    </div>
                                </div>
                            </section>
                            <footer class="modal-card-foot">
                                <button class="button" type="reset">Annulla</button>
                                <button class="button is-primary">Salva</button>
                            </footer>

                        </div>
                    </div>
                </form>
                <form action="/ajax/utente/aggiorna.php" class="is-modal">
                    <div id="modal-luogo-nascita" class="modal">
                        <div class="modal-background"></div>
                        <div class="modal-card">
                            <header class="modal-card-head">
                                <p class="modal-card-title">Modifica luogo di nascita</p>
                                <button class="delete" aria-label="close" type="reset"></button>
                            </header>
                            <input type="hidden" value="<?php echo $ricerca->id_utente; ?>" data-type="integer"
                                   name="id_utente"/>
                            <section class="modal-card-body">
                                <p>Luogo di nascita attuale:
                                    <b><?php echo $ricerca->luogo_nascita === null ? 'Non specificato' : $ricerca->luogo_nascita; ?></b>
                                </p>
                                <p style="margin-top: 20px">Ricerca nuova città o stato estero:</p>
                                <div class="field">
                                    <div class="control">
                                        <input class="input is-primary ajax-exclude autocomplete" type="text"
                                               placeholder="Ricerca città o stato estero"
                                               value="<?php echo $ricerca->luogo_nascita; ?>"
                                               id="ricercaCitta"
                                               data-realinput="#luogo_nascita"
                                               data-url="/ajax/utente/ricerca_luogo.php"/>
                                    </div>
                                    <ul class="autocomplete-list invisible"></ul>
                                </div>
                                <input type="hidden" name="luogo_nascita"
                                       id="luogo_nascita"
                                       value="<?php echo $ricerca->luogo_nascita->codice_belfiore; ?>"/>
                            </section>
                            <footer class="modal-card-foot">
                                <button class="button" type="reset">Annulla</button>
                                <button class="button is-primary">Salva</button>
                            </footer>
                        </div>
                    </div>
                </form>
                <form action="/ajax/utente/scuola.php" class="is-modal">
                    <div id="modal-scuola" class="modal">
                        <div class="modal-background"></div>
                        <div class="modal-card">
                            <header class="modal-card-head">
                                <p class="modal-card-title">Aggiungi scuola</p>
                                <button class="delete" aria-label="close" type="reset"></button>
                            </header>
                            <input type="hidden" value="<?php echo $ricerca->id_utente; ?>" data-type="integer"
                                   name="id_utente"/>
                            <section class="modal-card-body">
                                <div class="select">
                                    <select name="ruolo" id="ruolo" data-type="integer">
                                        <option value="0" selected>Studente</option>
                                        <option value="1">Docente</option>
                                        <option value="2">Personale</option>
                                    </select>
                                </div>
                                <p style="margin-top: 20px">Ricerca istituto scolastico (inserire cod. meccanografico o
                                    denominazione):</p>
                                <div class="field">
                                    <div class="control">
                                        <input class="input is-primary ajax-exclude autocomplete" type="text"
                                               placeholder="Ricerca scuola"
                                               id="ricercaCitta"
                                               data-realinput="#classe"
                                               data-url="/ajax/utente/ricerca_scuola.php"/>
                                    </div>
                                    <ul class="autocomplete-list invisible"></ul>
                                </div>
                                <input type="hidden" name="scuola"
                                       id="classe"/>
                                <div id="solo-studente">
                                    <p><b>Classe e sezione</b></p>
                                    <div class="columns">
                                        <div class="column is-2">
                                            <div class="select">
                                                <select name="classe" data-type="integer">
                                                    <option value="1" selected>1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="column is-4">
                                            <div class="field">
                                                <div class="control">
                                                    <input class="input is-primary" type="text"
                                                           placeholder="Sezione" name="sezione" style="width: 90px"
                                                           maxlength="5"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <footer class="modal-card-foot">
                                <button class="button" type="reset">Annulla</button>
                                <button class="button is-primary">Salva</button>
                            </footer>
                        </div>
                    </div>
                </form>
                <div class="container">
                    <h1 class="title is-1 has-text-centered"><?php echo $ricerca->nome . ' ' . $ricerca->cognome; ?></h1>
                    <?php if ($ricerca->sospeso): ?>
                        <h4 class="subtitle is-4 has-text-centered"><span class="tag is-danger">SOSPESO</span></h4>
                    <?php
                    endif;

                    if ($ricerca->codice_attivazione !== null):
                        ?>
                        <h4 class="subtitle is-4 has-text-centered"><span
                                    class="tag is-info">ACCOUNT NON VERIFICATO</span></h4>
                    <?php
                    endif;
                    ?>
                    <div class="level-right is-mobile">
                        <div class="dropdown is-hoverable is-right">
                            <div class="dropdown-trigger">
                                <button class="button" aria-haspopup="true" aria-controls="dropdown-menu">
                                    <span>Collegamenti</span>
                                    <span class="icon is-small">
                                    <i class="fas fa-angle-down" aria-hidden="true"></i>
                                </span>
                                </button>
                            </div>
                            <div class="dropdown-menu" id="dropdown-menu" role="menu">
                                <div class="dropdown-content">
                                    <a href="/gestione/utente/timeline.php?id=<?php echo $ricerca->id_utente; ?>"
                                       class="dropdown-item">
                                        Timeline
                                    </a>
                                    <a href="/gestione/badge/utente.php?id=<?php echo $ricerca->id_utente; ?>"
                                       class="dropdown-item">
                                        Gestione badge
                                    </a>
                                    <a href="/gestione/attestati/utente.php?id=<?php echo $ricerca->id_utente; ?>"
                                       class="dropdown-item">
                                        Attestati
                                    </a>
                                    <a href="/gestione/utenti/notifiche.php?id=<?php echo $ricerca->id_utente; ?>"
                                       class="dropdown-item">
                                        Notifiche
                                    </a>
                                    <a href="/gestione/utenti/sessioni.php?id=<?php echo $ricerca->id_utente; ?>"
                                       class="dropdown-item">
                                        Sessioni attive
                                    </a>
                                    <a href="/gestione/permessi/utente/?id=<?php echo $ricerca->id_utente; ?>"
                                       class="dropdown-item">
                                        Permessi personali
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="container" style="margin-top: 20px">
                        <div class="box">
                            <h3 class="title is-3">Profilo</h3>
                            <div>
                                <!-- <img src="/images/utente.png" id="imgUtente" alt/> -->
                                <form id="form-profilo" action="/ajax/utente/aggiorna.php">
                                    <div class="columns">
                                        <div class="column is-half">
                                            <div class="field">
                                                <div class="control">
                                                    <input class="input is-primary" type="text" name="nome"
                                                           placeholder="Nome"
                                                           value="<?php echo $ricerca->nome; ?>"/>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="control">
                                                    <input class="input is-primary" type="text" name="cognome"
                                                           placeholder="Cognome"
                                                           value="<?php echo $ricerca->cognome; ?>"/>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="control">
                                                    <input class="input is-primary" type="text" name="email"
                                                           placeholder="E-Mail"
                                                           value="<?php echo $ricerca->email; ?>"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="id_utente" data-type="integer"
                                           value="<?php echo $ricerca->id_utente; ?>"/>
                                    <button class="button is-primary">Salva</button>
                                </form>
                                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #aaa;">
                                    <p style="margin-bottom: 7px;">Se modifichi l'indirizzo email, l'utente riceverà un
                                        messaggio di
                                        avviso al vecchio indirizzo e dovrà confermare il nuovo cliccando sul link
                                        inviato alla
                                        nuova casella.</p>
                                </div>
                                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #aaa;">
                                    <ul>
                                        <li>ID utente: <b><?php echo $ricerca->id_utente; ?> </b></li>
                                        <li>Data di iscrizione: <b><?php echo date('d/m/Y H:i:s',
                                                    $ricerca->data_registrazione); ?></b></li>
                                        <li>IP al momento della registrazione:
                                            <b><?php echo $ricerca->ip_registrazione; ?></b>
                                        </li>
                                    </ul>
                                </div>
                                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #aaa;">
                                    <form action="/ajax/utente/aggiorna.php">
                                        <div>
                                            <div class="field">
                                                <input id="sospensione" type="checkbox" name="sospeso"
                                                       class="switch" <?php echo $ricerca->sospeso ? 'checked' : ''; ?>/>
                                                <label for="sospensione">Sospensione</label>
                                            </div>
                                        </div>
                                        <div style="margin-top: 10px">
                                            <div class="field">
                                                <input id="verificaEmail" name="codice_attivazione" type="checkbox"
                                                       class="switch" <?php echo $ricerca->codice_attivazione === null ? 'checked' : ''; ?>/>
                                                <label for="verificaEmail">Verifica E-Mail</label>
                                            </div>
                                        </div>
                                        <input type="hidden" name="id_utente" data-type="integer"
                                               value="<?php echo $ricerca->id_utente; ?>"/>
                                        <button class="button is-warning" style="margin-top: 10px">Salva</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php
                        if ($permessi['gestione.utenti.modificare_gruppi_utenti']['reale']):
                            ?>
                            <div class="box">
                                <h3 class="title is-3">Gruppi</h3>
                                <form action="/ajax/utente/imposta_gruppi.php">
                                    <?php
                                    try {
                                        $gruppi = \FabLabRomagna\Gruppo::Ricerca(array());

                                        echo '<div class="select is-multiple">';
                                        echo '<select name="gruppi" multiple data-type="integer">';

                                        foreach ($gruppi->risultato as $gruppo) {
                                            echo '<option value="' . $gruppo->id_gruppo . '"' . ($gruppo->fa_parte($ricerca) ? 'selected' : '') . '>' . $gruppo->nome . '</option>';
                                        }

                                        echo '</select>';
                                        echo '</div>';

                                    } catch (Exception $e) {
                                        echo 'Impossibile caricare i gruppi.';
                                    }
                                    ?>
                                    <input type="hidden" name="id_utente" data-type="integer"
                                           value="<?php echo $ricerca->id_utente; ?>"/>
                                    <input type="submit" class="button is-primary" value="Salva"/>
                                </form>
                            </div>
                        <?php
                        endif;

                        if ($permessi['gestione.utenti.visualizzare_anagrafiche']['reale']):
                            ?>
                            <div class="box">
                                <h3 class="title is-3">Anagrafiche</h3>
                                <div>
                                    <?php

                                    if ($ricerca->data_nascita !== null && $ricerca->luogo_nascita !== null && $ricerca->codice_fiscale !== null && $ricerca->sesso !== null):
                                        $subject = new \CodiceFiscale\Subject(
                                            array(
                                                'name' => $ricerca->nome,
                                                'surname' => $ricerca->cognome,
                                                'birthDate' => date('Y-m-d', $ricerca->data_nascita),
                                                'gender' => $ricerca->sesso ? 'F' : 'M',
                                                'belfioreCode' => $ricerca->luogo_nascita->codice_belfiore
                                            )
                                        );
                                        $checker = new \CodiceFiscale\Checker($subject, array(
                                            "codiceFiscaleToCheck" => $ricerca->codice_fiscale,
                                            "omocodiaLevel" => \CodiceFiscale\Checker::ALL_OMOCODIA_LEVELS
                                        ));
                                        if (!$checker->check()):
                                            ?>
                                            <article class="message is-warning">
                                                <div class="message-header">
                                                    <p>Attenzione</p>
                                                </div>
                                                <div class="message-body">
                                                    Il controllo sul codice fiscale ha dato esito negativo!<br/>Ricontrolla
                                                    i dati
                                                    anagrafici.
                                                </div>
                                            </article>
                                        <?php
                                        endif;
                                    endif;
                                    ?>
                                    <p>Data di nascita: <b><?php echo $ricerca->data_nascita !== null ? date('d/m/Y',
                                                $ricerca->data_nascita) : '' ?></b>
                                        <a class="open-modal" data-open="modal-data">Modifica</a></p>

                                    <p>Luogo di nascita: <b><?php echo $ricerca->luogo_nascita ?></b>
                                        <a class="open-modal" data-open="modal-luogo-nascita">Modifica</a></p>

                                    <p>Sesso:
                                        <b><?php echo $ricerca->sesso === null ? '' : ($ricerca->sesso ? 'Femminile' : 'Maschile'); ?></b>
                                        <a class="open-modal" data-open="modal-sesso">Modifica</a></p>

                                    <p>Codice Fiscale: <b><?php echo $ricerca->codice_fiscale ?></b>
                                        <a class="open-modal" data-open="modal-cf">Modifica</a></p>
                                </div>
                                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #aaa;">
                                    <div><b>Informazioni sulla scuola:</b></div>
                                    <?php
                                    $relazioni = false;
                                    try {
                                        $relazioni = RelazioneScolastica::ricerca(array(
                                            new Equals('utente', $ricerca->id_utente)
                                        ));

                                    } catch (Exception $e) {
                                        Log::crea($utente, 3, '/gestione/utenti/utente.php', 'estrazione_utente',
                                            'Impossibile completare la richiesta.', (string)$e);
                                        echo '<p>Impossibile completare la richiesta.';
                                    }

                                    if ($relazioni !== false):

                                        $relazioni = $relazioni->risultato;

                                        /**
                                         * @var RelazioneScolastica[] $relazioni
                                         */

                                        foreach ($relazioni as $relazione) {
                                            if ($relazione->ruolo === 0): ?>

                                                <p>Studente presso <b><?php echo $relazione->scuola; ?></b> (classe
                                                    <b><?php echo $relazione->classe; ?></b>, sez.
                                                    <b><?php echo $relazione->sezione; ?></b>). <a
                                                            onclick="elimina_scuola(<?php echo $ricerca->id_utente ?>, <?php echo $relazione->id_relazione ?>)">Elimina</a>
                                                    -
                                                    <a href="/gestione/scuole/scuola.php?cod_mec=<?php echo $relazione->scuola->codice ?>">Visualizza
                                                        scuola</a></p>

                                            <?php
                                            else:

                                                $ruolo = $relazione->ruolo === 1 ? 'Insegnante' : 'Personale';
                                                ?>


                                                <p><?php echo $ruolo ?> presso <b><?php echo $relazione->scuola; ?></b>.
                                                    <a
                                                            onclick="elimina_scuola(<?php echo $ricerca->id_utente ?>, <?php echo $relazione->id_relazione ?>)">Elimina</a>
                                                    -
                                                    <a href="/gestione/scuole/scuola.php?cod_mec=<?php echo $relazione->scuola->codice ?>">Visualizza
                                                        scuola</a></p>

                                            <?php
                                            endif;
                                        }

                                    endif;
                                    ?>
                                    <div class="level-right">
                                        <button class="button is-primary open-modal" data-open="modal-scuola">Aggiungi
                                            scuola
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php
                        endif;

                        if ($permessi['gestione.utenti.modificare_anagrafiche']['reale']):
                            ?>
                            <div class="box">
                                <h3 class="title is-3">Password</h3>
                                <div>
                                    <form action="/ajax/utente/reset_password.php">
                                        <input type="hidden" name="id_utente" data-type="integer"
                                               value="<?php echo $ricerca->id_utente; ?>"/>
                                        <button id="cambiaPwd" class="button is-warning">Invia password</button>
                                    </form>
                                    <p style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #aaa;">Puoi
                                        inviare
                                        una nuova
                                        password all'utente cliccando il bottone sopra. L'invio di una nuova password
                                        comporta la
                                        revoca
                                        della precedente.</p>
                                </div>
                            </div>
                        <?php
                        endif;
                        ?>
                    </div>
                </div>
            <?php
            endif;
        endif;
        include_once('../../inc/footer.inc.php');
        ?>
    </body>
</html>
