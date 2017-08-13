<?php
  require_once('../../inc/autenticazione.inc.php');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../../inc/header.inc.php');
    ?>
    <script type="text/javascript" src="/js/social.js"></script>
    <style type="text/css">
      .box { display: table; border: 1px solid <?php echo TEMA_BG_PRINCIPALE ?>; width: 100%; border-radius: 3px; margin-bottom: 15px; }
      .box > div { display: table-cell; vertical-align: top; padding: 10px; }
      .box > div:first-child { background: <?php echo TEMA_BG_PRINCIPALE ?>; width: 120px; font-size: 20px;  }

      #contenuto > h1 { margin-bottom: 20px; }
    </style>
  </head>
  <body>
    <?php
      include_once('../../inc/nav.inc.php');

      $id = $mysqli -> real_escape_string(isset($_GET['id']) ? trim($_GET['id']) : '');
    ?>
    <div id="contenuto">
      <h1>Social Networks</h1>
      <div class="box">
        <div>Telegram</div>
        <div>
          <?php
            if($dizionario -> getValue('telegramBotName') === false || $dizionario -> getValue('telegramBotName') === null)
              echo '<p>Il bot Telegram non è disponibile.</p>';

            else {
              $sql = "SELECT * FROM socialNetworks WHERE idUtente = {$id} AND tipo = 'telegram' AND authCode IS NULL LIMIT 0, 1";
              $query = $mysqli -> query($sql);

              if($query) {

                // L'utente non ha ancora un token di registrazione
                if($query -> num_rows == 0)
                  echo '<p>Collegamento non effettuato.</p>';

                else {
                  $row = $query -> fetch_assoc();

                  $codiceAutenticazione = $row['authCode'];

                  echo '<p>Collegamento effettuato.</p>';
                  echo '<p>ID Telegram: <b>'.$row['idSocial'].'</b></p>';
                }
              } else {

                $console -> alert('Impossibile richiedere i dati Telegram. '.$mysqli -> error, $autenticazione -> id);
                echo '<p>Impossibile completare la richiesta.</p>';
              }
            }
          ?>
        </div>
      </div>
      <div class="box">
        <div>Facebook</div>
        <div>
          <?php
            if($dizionario -> getValue('facebookAppId') === false || $dizionario -> getValue('facebookAppId') === null)
              echo '<p>L\'accesso con Facebook non è disponibile.</p>';

            else {
              $sql = "SELECT * FROM socialNetworks WHERE idUtente = {$id} AND tipo = 'facebook' LIMIT 0, 1";
              $query = $mysqli -> query($sql);

              if($query) {

                // L'utente non ha ancora un token di registrazione
                if($query -> num_rows == 0) {
                ?>
                  <p>Collegamento non effettuato.</p>
              <?php
                } else {
                  $row = $query -> fetch_assoc();

                  echo '<p>Collegamento effettuato.</p>';
                  echo '<p>ID Facebook: <b>'.$row['idSocial'].'</b></p>';
                }
              } else {

                $console -> alert('Impossibile richiedere i dati Telegram. '.$mysqli -> error, $autenticazione -> id);
                echo '<p>Impossibile completare la richiesta.</p>';
              }
            }
          ?>
        </div>
      </div>
    </div>
    <?php
      include_once('../../inc/footer.inc.php');
    ?>
  </body>
</html>
