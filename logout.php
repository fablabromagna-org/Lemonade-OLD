<?php
  require_once('inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);
  if(!$autenticazione -> isLogged())
    header('Location: /');

  else {
    // Cancello il cookie
    setcookie(COOKIE_NAME, '', 1, '/');

    // Cancello la sessione dal database
    $sql = "DELETE FROM sessioni WHERE idUtente = '".$autenticazione -> id."' AND hashSessione = '".$autenticazione -> hashSessione."'";
    $mysqli -> query($sql);
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <?php
      require_once('./inc/header.inc.php');
    ?>
    <link type="text/css" rel="stylesheet" media="screen" href="/css/index.css" />
  </head>
  <body>
    <div id="header">
      <a href="/login.php" class="button" id="bottoneFlottante">Accesso</a>
      <div>
        <p><?php echo NOME_SITO; ?><p>
      </div>
      <img src="/images/logo.png" alt="Logo" />
    </div>
    <div id="accesso">
      <h2>Sei uscito</h2>
      <p style="margin-top: 20px;"><a href="/login.php">Clicca qui</a> per tornare alla pagina di accesso.</p>
    </div>
    <?php
      require_once('inc/footer.inc.html');
    ?>
  </body>
</html>