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
        <link type="text/css" rel="stylesheet" media="screen" href="css/index.css"/>
        <script type="text/javascript" src="js/accesso.js"></script>
    </head>
    <body>
        <div id="header">
            <a href="/" class="button" id="bottoneFlottante">Registrazione</a>
            <div>
                <p><?php echo NOME_SITO; ?><p>
            </div>
            <img src="images/logo.png" alt="Logo"/>
        </div>
        <div id="accesso">
            <h2>Accesso</h2>
            <form id="formAccesso">
                <div id="contenitoreBox">
                    <div class="box" id="credenziali">
                        <input type="text" id="email" placeholder="E-Mail"/>
                        <input type="password" id="password" placeholder="Password"/>
                        <a style="display: block;" href="/account/recupero.php">Ho dimenticato la password</a>
                    </div>
                    <div class="box" style="display: none;" id="captchaBox">
                        <p>Dimostra di non essere un robot</p>
                        <img id="captchaImg" src="/ajax/captcha.php" alt/>
                        <input type="text" id="captcha" placeholder="Codice di verifica"/>
                    </div>
                </div>
                <div class="box" id="spinnerBox" style="display: none;">
                    <p>Attendi mentre elaboriamo la tua richiesta...</p>
                    <div class="spinner" style="margin: 0 auto 10px auto">
                        <div class="rect1"></div>
                        <div class="rect2"></div>
                        <div class="rect3"></div>
                        <div class="rect4"></div>
                        <div class="rect5"></div>
                    </div>
                </div>
                <input type="submit" value="Avanti" id="invioForm"/>
            </form>
        </div>
        <?php
        include_once('inc/footer.inc.php');
        ?>
    </body>
</html>
