<?php
  require_once('../../inc/autenticazione.inc.php');

  $permessiUtente = $permessi -> whatCanHeDo($autenticazione -> id);
  $dashboard = $permessiUtente['dashboard']['stato'];
  $templates = $permessiUtente['visualizzareTemplate']['stato'];
  $dizionario = $permessiUtente['dizionario']['stato'];

  if(!$dizionario && !$templates && !$dashboard)
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
      <h1>Gestione generale</h1>
      <div id="contenitoreBox">
      <?php
        if($dashboard) {
      ?>
        <div class="box">
          <div class="titolo">
            <p>Messaggi sulla dashboard</p>
          </div>
          <div class="descrizione">
            <p>Gestione dei messaggi sulla dashboard.</p>
            <a href="/gestione/generale/dashboard.php" class="button">Apri Messaggi sulla dashboard</a>
          </div>
        </div>
        <?php
          }

          if($templates) {
        ?>
        <div class="box">
          <div class="titolo">
            <p>Templates</p>
          </div>
          <div class="descrizione">
            <p>Gestione dei templates.</p>
            <a href="/gestione/generale/templates/" class="button">Apri Templates</a>
          </div>
        </div>
        <?php
          }

          if($dizionario) {
        ?>
        <div class="box">
          <div class="titolo">
            <p>Dizionario</p>
          </div>
          <div class="descrizione">
            <p>Gestione del dizionario del portale.</p>
            <p style="padding: 3px 5px; background: #f44336; display: inline-block; color: #fff; font-weight: 700; border-radius: 3px;">ATTENZIONE! Questa è una sezione molto delicata!</p>
            <a href="/gestione/generale/dizionario.php" class="button">Apri Dizionario</a>
          </div>
        </div>
        <?php
          }
        ?>
      </div>
    </div>
    <?php
      include_once('../../inc/footer.inc.php');
    ?>
  </body>
</html>
