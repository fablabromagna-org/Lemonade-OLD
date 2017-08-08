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
            <p>Utenti</p>
          </div>
          <div class="descrizione">
            <p>Pannello di gestione degli utenti.</p>
            <a href="/gestione/utenti/" class="button">Apri Gestione Utenti</a>
          </div>
        </div>
        <div class="box">
          <div class="titolo">
            <p>Generale</p>
          </div>
          <div class="descrizione">
            <p>Pannello di gestione della dashboard e di altre funzioni.</p>
            <a href="/gestione/generale/" class="button">Apri Gestione Generale</a>
          </div>
        </div>
        <div class="box">
          <div class="titolo">
            <p>Maker Space</p>
          </div>
          <div class="descrizione">
            <p>Pannello di gestione dei Maker Space.</p>
            <a href="/gestione/makerspace/" class="button">Apri Gestione Maker Space</a>
          </div>
        </div>
        <div class="box">
          <div class="titolo">
            <p>Log</p>
          </div>
          <div class="descrizione">
            <p>Elenco dei log del portale.</p>
            <a href="/gestione/logs.php" class="button">Vai a Log</a>
          </div>
        </div>
      </div>
    </div>
    <?php
      include_once('../inc/footer.inc.html');
    ?>
  </body>
</html>
