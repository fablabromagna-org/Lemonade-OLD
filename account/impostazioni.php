<?php
require_once(__DIR__ . '/../class/autoload.inc.php');

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

    Log::crea($utente, 3, '/account/impostazioni.php', 'page_request',
        'Impossibile completare la richiesta.', (string)$e);

} catch (Exception $e) {
    Log::crea(null, 3, '/account/impostazioni.php', 'page_request',
        'Impossibile completare la richiesta.', (string)$e);
    reply(500, 'Internal Server Error');
}
?>
<!DOCTYPE html>
<html lang="it">
    <head>
        <?php
        require_once('../inc/header.inc.php');
        ?>
    </head>
    <body>
        <?php
        include_once('../inc/nav.inc.php');
        ?>
        <div class="container">
            <h1 class="title is-1 has-text-centered"><?php echo $utente->nome . ' ' . $utente->cognome; ?></h1>
            <div id="container" style="margin-top: 20px">
                <div class="box">
                    <h3 class="title is-3">Profilo</h3>
                    <div>
                        <p>Nome: <b><?php echo $utente->nome ?></b></p>
                        <p>Cognome: <b><?php echo $utente->cognome ?></b></p>
                        <p>E-Mail: <b><?php echo $utente->email ?></b></p>
                    </div>
                </div>
                <div class="box">
                    <h3 class="title is-3">Anagrafiche</h3>
                    <div>
                        <?php

                        if ($utente->data_nascita !== null && $utente->luogo_nascita !== null && $utente->codice_fiscale !== null && $utente->sesso !== null):
                            $subject = new \CodiceFiscale\Subject(
                                array(
                                    'name' => $utente->nome,
                                    'surname' => $utente->cognome,
                                    'birthDate' => date('Y-m-d', $utente->data_nascita),
                                    'gender' => $utente->sesso ? 'F' : 'M',
                                    'belfioreCode' => $utente->luogo_nascita->codice_belfiore
                                )
                            );
                            $checker = new \CodiceFiscale\Checker($subject, array(
                                "codiceFiscaleToCheck" => $utente->codice_fiscale,
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
                        <p>Data di nascita: <b><?php echo $utente->data_nascita !== null ? date('d/m/Y',
                                    $utente->data_nascita) : '' ?></b></p>

                        <p>Luogo di nascita: <b><?php echo $utente->luogo_nascita ?></b></p>

                        <p>Sesso:
                            <b><?php echo $utente->sesso === null ? '' : ($utente->sesso ? 'Femminile' : 'Maschile'); ?></b>
                        </p>

                        <p>Codice Fiscale: <b><?php echo $utente->codice_fiscale ?></b></p>
                    </div>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #aaa;">
                        <div><b>Informazioni sulla scuola:</b></div>
                        <?php
                        $relazioni = false;
                        try {
                            $relazioni = RelazioneScolastica::ricerca(array(
                                new Equals('utente', $utente->id_utente)
                            ));

                        } catch (Exception $e) {
                            Log::crea($utente, 3, '/account/impostazioni.php', 'estrazione_relazioni_scuola',
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
                                        <b><?php echo $relazione->sezione; ?></b>).</p>

                                <?php
                                else:

                                    $ruolo = $relazione->ruolo === 1 ? 'Insegnante' : 'Personale';
                                    ?>
                                    <p><?php echo $ruolo ?> presso <b><?php echo $relazione->scuola; ?></b>.</p>

                                <?php
                                endif;
                            }

                        endif;
                        ?>
                    </div>
                </div>
                <div class="box">
                    <h3 class="title is-3">Password</h3>
                    <div class="columns">
                        <form action="/ajax/utente/imposta_password.php" class="column is-half">
                            <div class="field">
                                <div class="control">
                                    <input class="input is-primary" type="password" name="password_attuale"
                                           placeholder="Password attuale"/>
                                </div>
                            </div>
                            <div class="field">
                                <div class="control">
                                    <input class="input is-primary" type="password" name="nuova_password"
                                           placeholder="Nuova password"/>
                                </div>
                            </div>
                            <p style="margin: 10px 0" class="is-size-7 has-text-grey">La password deve contenere
                                almeno un carattere minuscolo, uno maiuscolo, un numero e un carattere speciale. La
                                lunghezza minima consentita è di sei caratteri. <b>Non puoi impostare una password
                                    utilizzata negli ultimi dodici mesi.</b></p>
                            <div class="field">
                                <div class="control">
                                    <input class="input is-primary" type="password" name="conferma_password"
                                           placeholder="Conferma nuova password"/>
                                </div>
                            </div>
                            <button class="button is-primary">Cambia</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include_once('../inc/footer.inc.php');
        ?>
    </body>
</html>
