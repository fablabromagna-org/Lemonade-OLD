<?php
  require_once('../../inc/autenticazione.inc.php');

  if($autenticazione -> gestionePortale != 1)
    header('Location: /');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../../inc/header.inc.php');
    ?>
    <link type="text/css" rel="stylesheet" media="screen" href="/css/dashboard.css" />
  </head>
  <body>
    <?php
      include_once('../../inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <h1>Gestione utenti</h1>
      <div id="contenitoreBox">
        <div class="box">
          <div class="titolo">
            <p>Ricerca utenti</p>
          </div>
          <div class="descrizione">
            <p>Pannello per la ricerca nel database degli utenti.</p>
            <a href="ricerca.php" class="button">Vai a Ricerca utenti</a>
          </div>
        </div>
        <div class="box">
          <div class="titolo">
            <p>Categorie utenti</p>
          </div>
          <div class="descrizione">
            <p>Gestione delle categorie degli utenti</p>
            <a href="categorie.php" class="button">Vai a Categorie utenti</a>
          </div>
        </div>
      </div>
    </div>
    <?php
      include_once('../../inc/footer.inc.html');
    ?>
  </body>
</html>