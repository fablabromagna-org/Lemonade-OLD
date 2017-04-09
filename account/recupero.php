<?php
  require_once('../inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);
  if($autenticazione -> isLogged())
    header('Location: /dashboard.php');
?>
<!DOCTYPE html>
<html>
  <head>
    <?php
      require_once('../inc/header.inc.php');
    ?>
    <link type="text/css" rel="stylesheet" media="screen" href="/css/index.css" />
    <script type="text/javascript" src="/js/recuperoPwd.js"></script>
  </head>
  <body>
    <div id="header">
      <a href="/" class="button" id="bottoneFlottante">Registrazione</a>
      <div>
        <p><?php echo NOME_SITO; ?><p>
      </div>
      <img src="/images/logo.png" alt="Logo" />
    </div>
    <div style="margin: 10px auto 30px auto; max-width: 520px; text-align: center; padding: 0 20px;">
      <h2>Ripristina la tua password</h2>
      <p style="margin-top: 10px;">Inserisci il tuo indirizzo email, riceverai una mail da "<?php echo MITTENTE_EMAIL.' '.INDIRIZZO_MITTENTE.''; ?>" con la tua nuova password.</p>
      <form id="formRecupero">
        <input type="email" style="display: block; margin: 20px auto 20px auto;" id="email" autocomplete="off" placeholder="E-Mail" />
        <input type="submit" value="Ripristina" id="invioForm" />
      </form>
    </div>
    <?php
      include_once('../inc/footer.inc.html');
    ?>
  </body>
</html>
