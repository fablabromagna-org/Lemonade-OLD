<?php
require_once(__DIR__ . '/class/autoload.inc.php');

try {
    if (!\FabLabRomagna\Firewall::controllo()) {
        \FabLabRomagna\Firewall::firewall_redirect();
    }

    if (\FabLabRomagna\Autenticazione::get_sessione_attiva() !== null) {
        header('Location: /dashboard.php');
        exit();
    }
} catch (Exception $e) {
    \FabLabRomagna\Log::crea(null, 3, '/login.php', 'page_request',
        'Impossibile completare la richiesta.', (string)$e);
    reply(500, 'Internal Server Error');
}
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        require_once('./inc/header.inc.php');
        ?>
        <script type="text/javascript">
            $(document).ready(function () {

                var priv16 = false
                var priv = false

                $('#privacy16').change(function () {
                    priv16 = !priv16
                    privacy()
                })

                $('#privacy_policy').change(function () {
                    priv = !priv
                    privacy()
                })

                function privacy () {
                    if (priv && priv16)
                        $('#btnReg').removeAttr('disabled')

                    else
                        $('#btnReg').attr('disabled', 'yes')
                }
            })
        </script>
    </head>
    <body>
        <?php
        include_once('./inc/nav.inc.php');
        ?>
        <div class="contenuto">
            <h1 class="title is-1 has-text-centered">Registrazione</h1>
            <div class="container is-fluid">
                <div class="columns">
                    <form action="/ajax/registrazione.php" method="post"
                          class="column is-4 is-offset-4 has-text-centered">
                        <p>Se hai già avuto rapporti con l'associazione (partecipazione ai corsi, ecc) potresti essere
                            già stato registrato nel gestionale dal nostro personale.</p>
                        <p style="margin: 10px 0;">Contattaci per effettuare un controllo e renderti disponibile
                            l'accesso anche alle attività svolte in passato.</p>
                        <div class="field">
                            <div class="control">
                                <input class="input is-primary" name="nome" type="text" placeholder="Nome"/>
                            </div>
                        </div>
                        <div class="field">
                            <div class="control">
                                <input class="input is-primary" name="cognome" type="text" placeholder="Cognome"/>
                            </div>
                        </div>
                        <div class="field">
                            <div class="control">
                                <input class="input is-primary" name="email" type="email" placeholder="E-Mail"/>
                            </div>
                        </div>
                        <div class="field">
                            <div class="control">
                                <input class="input is-primary" type="password" name="password" placeholder="Password"/>
                            </div>
                            <p style="margin-top: 10px" class="is-size-7 has-text-grey">La password deve contenere
                                almeno un carattere minuscolo, uno maiuscolo, un numero e un carattere speciale. La
                                lunghezza minima consentita è di sei caratteri.</p>
                        </div>
                        <?php
                        include('./inc/captcha.php');
                        ?>
                        <input class="styled ajax-exclude" type="checkbox" id="privacy16">
                        <label for="privacy16">
                            Dichiaro di avere almeno 16 anni.
                        </label>
                        <p style="margin: 10px 0" class="is-size-7 has-text-grey">Se non hai raggiunto l'età ti
                            chiediamo di richiedere un modulo cartaceo da far firmare ad un genitore/tutore (ai sensi
                            del regolamento UE n. 679/2016 «GDPR»).</p>
                        <input class="styled ajax-exclude" type="checkbox" id="privacy_policy">
                        <label for="privacy_policy">
                            Dichiaro di accettare la <a href="<?php echo PRIVACY_POLICY_URL; ?>" target="_blank">Privacy
                                Policy</a>.
                        </label>
                        <p style="margin-top: 10px" class="is-size-7 has-text-grey">L'accettazione della Privacy Policy
                            è obbligatoria per poter erogare i servizi connessi.</p>

                        <button class="button is-primary" disabled id="btnReg" style="margin-top: 15px">Registrati
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        include_once('inc/footer.inc.php');
        ?>
    </body>
</html>
