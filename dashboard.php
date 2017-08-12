<?php
  require_once('inc/autenticazione.inc.php');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('inc/header.inc.php');
    ?>
    <link type="text/css" rel="stylesheet" media="screen" href="/css/dashboard.css" />
  </head>
  <body>
    <?php
      include_once('inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <h1>Ciao <?php echo $autenticazione -> nome ?></h1>
      <div id="contenitoreBox">
        <?php
          // Estraggo i box della dashboard
          $sql = "SELECT * FROM dashboard ORDER BY id ASC";

          // Eseguo la query
          if($query = $mysqli -> query($sql)) {

            // Stampo i box
            while($row = $query -> fetch_assoc()) {

              if($row['tipo'] == 2) {
              ?>
              <div class="box">
                <div class="titolo">
                  <p><?php echo $row['titolo'] ?></p>
                </div>
                <div class="descrizione">
                  <p><?php echo $row['descrizione'] ?></p>
                </div>
              </div>
              <?php
              } else {
              ?>
              <div class="box">
                <div class="titolo">
                  <p><?php echo $row['titolo'] ?></p>
                </div>
                <div class="descrizione">
                  <p><?php echo $row['descrizione'] ?></p>
                  <a href="<?php echo $row['linkBottone'] ?>" target="_blank" class="button"><?php echo $row['titoloLink'] ?></a>
                </div>
              </div>
              <?php
              }
            }

          // Errore nell'estrazione dei box dalla dashboard
          } else
            echo '<p style="margin-top: 20px">Impossibile caricare la dashboard (errore durante la comunicazione col database).</p>';
        ?>
        <div class="box">
          <div class="titolo">
            <p>Attività svolte</p>
          </div>
          <div class="descrizione">
            <p>Puoi vedere tutte le attività che hai svolto presso FabLab Romagna e che ti sono state riconosciute alla pagina "Attività".</p>
            <a href="/account/attivita.php" class="button">Vai ad attività</a>
          </div>
        </div>
        <div class="box">
          <div class="titolo">
            <p>FabCoin</p>
          </div>
          <div class="descrizione">
            <?php
              $sql = "SELECT SUM(valore) AS somma FROM transazioniFabCoin WHERE idUtente = '{$autenticazione -> id}' AND annullata  = FALSE";
              $query = $mysqli -> query($sql);

              if($query) {
                $row = $query -> fetch_assoc();

                if($row['somma'] != '')
                  echo '<p style="margin-top: 20px;">Il tuo saldo FabCoin è di <b>'.$row['somma'].'</b>.</p>';

                else
                  echo '<p style="margin-top: 20px;">Non hai nessun FabCoin</b>.</p>';

              } else {
                echo '<p>Impossibile completare la richiesta.</p>';
                $console -> alert("Impossibile estrarre saldo fabcoin sulla dashboard. ".$mysqli -> error, $autenticazione -> id);
              }
            ?>
            <a href="/account/transazioni/fabcoin.php" class="button">Vai alle transazioni</a>
          </div>
        </div> <!-- Fine box -->
        <div class="box">
          <div class="titolo">
            <p>Presenze</p>
          </div>
          <div class="descrizione">
            <?php
              $sql = "SELECT SUM(presenze.fine - presenze.inizio) AS somma FROM presenze WHERE annullata IS NOT TRUE AND idUtente = {$autenticazione -> id} AND fine >= ".strtotime(date('Y-01-01'));
              $query = $mysqli -> query($sql);

              if($query) {
                $row = $query -> fetch_assoc();

                if((int)$row['somma'] === 0)
                  echo '<p>Non sei mai stato presente.</p>';

                else
                  echo '<p>Sei stato presente '.sec2str((int)$row['somma']).'.</p>';

              } else {
                $console -> alert('Impossibile caricare le presenze sulla dashboard. '.$mysqli -> error, $autenticazione -> id);
                echo '<p>Impossibile completare la richiesta.</p>';
              }
            ?>
            <a href="/account/presenze.php" class="button">Vai alle presenze</a>
          </div>
        </div>
      </div>
    </div>
    <?php
      include_once('inc/footer.inc.html');
    ?>
  </body>
</html>
