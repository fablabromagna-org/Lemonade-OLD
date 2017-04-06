<?php
  require_once('../inc/autenticazione.inc.php');

  if($autenticazione -> gestionePortale != 1)
    header('Location: /');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../inc/header.inc.php');
    ?>
    <link type="text/css" rel="stylesheet" media="screen" href="/css/dashboard.css" />
  </head>
  <body>
    <?php
      include_once('../inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <h1>Gestione</h1>
      <div id="contenitoreBox">
        <div class="box">
          <div class="titolo">
            <p>Messaggi sulla dashboard</p>
          </div>
          <div class="descrizione">
            <p>Gestione dei messaggi sulla dashboard.</p>
            <a href="/gestione/gestioneDashboard.php" class="button">Apri Messaggi sulla dashboard</a>
          </div>
        </div>
      </div>
    </div>
    <?php
      include_once('../inc/footer.inc.html');
    ?>
  </body>
</html>