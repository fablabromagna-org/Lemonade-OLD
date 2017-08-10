<?php
  require_once('inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);
  if($autenticazione -> isLogged())
    header('Location: /dashboard.php');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('./inc/header.inc.php')
    ?>
    <link type="text/css" rel="stylesheet" media="screen" href="css/index.css" />
    <script type="text/javascript" src="js/registrazione.js"></script>
  </head>
  <body>
    <div id="header">
      <a href="/login.php" class="button" id="bottoneFlottante">Accesso</a>
      <div>
        <p><?php echo NOME_SITO; ?><p>
      </div>
      <img src="images/logo.png" alt="Logo" />
    </div>
    <div id="registrazione">
      <h2>Registrazione</h2>
      <?php
        if($dizionario -> getValue('bloccoIscrizioni') !== 'true') {
      ?>
      <form id="formRegistrazione">
        <input type="text" id="nome" autocomplete="off" placeholder="Nome" />
        <input type="text" id="cognome" autocomplete="off" placeholder="Cognome" />
        <input type="text" id="email" autocomplete="off" placeholder="E-Mail" />
        <input type="password" id="pwd" autocomplete="off" placeholder="Password" />
        <input type="hidden" id="tipoAccount" value="0" />
        <input type="submit" id="invioForm" value="Registrati" />
        <p style="color: #aaa; margin-top: 15px;">Registrandoti accetti la Privacy Policy ed i Termini di utilizzo.</p>
      </form>
      <?php
        } else {
      ?>
      <h3 style="margin-top: 20px;">Le registrazioni sono temporaneamente bloccate.</h3>
      <?php
        }
      ?>
    </div>
    <?php
      require_once('inc/footer.inc.html');
    ?>
  </body>
</html>
