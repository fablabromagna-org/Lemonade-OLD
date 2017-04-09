<?php
  require_once('inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);
  if($autenticazione -> isLogged())
    header('Location: /dashboard.php');
?>
<!DOCTYPE html>
<html>
  <head>
    <?php
      require_once('./inc/header.inc.php');
    ?>
    <link type="text/css" rel="stylesheet" media="screen" href="css/index.css" />
    <script type="text/javascript" src="js/accesso.js"></script>
  </head>
  <body>
    <div id="header">
      <a href="/" class="button" id="bottoneFlottante">Registrazione</a>
      <div>
        <p><?php echo NOME_SITO; ?><p>
      </div>
      <img src="images/logo.png" alt="Logo" />
    </div>
    <div id="accesso">
      <h2>Accesso</h2>
      <form id="formAccesso">
        <input type="text" id="email" autocomplete="off" placeholder="E-Mail" />
        <input type="password" id="pwd" autocomplete="off" placeholder="Password" />
        <a style="display: block;" href="/account/recupero.php">Ho dimenticato la password</a>
        <input type="submit" value="Accedi" id="invioForm" />
      </form>
    </div>
    <?php
      include_once('inc/footer.inc.html');
    ?>
  </body>
</html>
