<?php
  require_once('../../inc/autenticazione.inc.php');

  $permessiUtente = $permessi -> whatCanHeDo($autenticazione -> id);
  $ricerca = $permessiUtente['visualizzareUtenti']['stato'];
  $gruppi = $permessiUtente['visualizzareGruppi']['stato'];
  $badge = $permessiUtente['visualizzareBadge']['stato'];

  if(!$ricerca && !$gruppi && !$badge)
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
        <?php if($ricerca) { ?>
        <div class="box">
          <div class="titolo">
            <p>Ricerca utenti</p>
          </div>
          <div class="descrizione">
            <p>Pannello per la ricerca nel database degli utenti.</p>
            <a href="ricerca.php" class="button">Vai a Ricerca utenti</a>
          </div>
        </div>
        <?php
          }

          if($gruppi) {
        ?>
        <div class="box">
          <div class="titolo">
            <p>Categorie utenti</p>
          </div>
          <div class="descrizione">
            <p>Gestione delle categorie degli utenti</p>
            <a href="categorie.php" class="button">Vai a Categorie utenti</a>
          </div>
        </div>
        <?php
          }

          if($badge) {
        ?>
        <div class="box">
          <div class="titolo">
            <p>Ricerca badge</p>
          </div>
          <div class="descrizione">
            <p>Pannello per la ricerca nel database dei badge.</p>
            <a href="/gestione/badge/" class="button">Vai a Ricerca Badge</a>
          </div>
        </div>
        <?php } ?>
      </div>
    </div>
    <?php
      include_once('../../inc/footer.inc.php');
    ?>
  </body>
</html>
