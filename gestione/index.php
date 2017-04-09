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
            <a href="/gestione/gestioneUtenti.php" class="button">Apri Gestione Utenti</a>
          </div>
        </div>
        <div class="box">
          <div class="titolo">
            <p>Generale</p>
          </div>
          <div class="descrizione">
            <p>Pannello di gestione della dashboard e di altre funzioni.</p>
            <a href="/gestione/gestioneGenerale.php" class="button">Apri Gestione Dashboard</a>
          </div>
        </div>
        <div class="box">
          <div class="titolo">
            <p>Newsletter</p>
          </div>
          <div class="descrizione">
            <p>Pannello di gestione della newsletter.</p>
            <a href="/gestione/gestioneNewsletter.php" class="button disabled">Apri Gestione Newsletter</a>
          </div>
        </div>
        <div class="box">
          <div class="titolo">
            <p>Corsi</p>
          </div>
          <div class="descrizione">
            <p>Pannello per la gesestione dei corsi.</p>
            <a href="/gestione/corsi" class="button">Vai a Corsi</a>
          </div>
        </div>
      </div>
    </div>
    <?php
      include_once('../inc/footer.inc.html');
    ?>
  </body>
</html>