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
  </head>
  <body>
    <?php
      include_once('../../inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <?php


        $id = $mysqli -> real_escape_string(isset($_GET['id']) ? trim($_GET['id']) : '');

        // Estraggo il profilo dell'utente
        $sql = "SELECT * FROM attivita WHERE id = '{$id}'";

        if(!$query = $mysqli -> query($sql))
          echo '<h1>Errore!</h1>';

        else {

          if($query -> num_rows != 1)
            echo '<h1>Attività inesistente!</h1>';

          else {

            $row = $query -> fetch_assoc();

            $fabcoin = ($row['fabcoin'] === null) ? '<em>nessun FabCoin è stato assegnato</em>': 'F'.$row['fabcoin'];

            echo '<h1>Riepilogo attività</h1>';
            
            echo "<p style=\"margin-top: 20px\"><b>ID attività:</b> {$id}</p>";
            echo "<p><b>Registrata il </b> ".date("d/m/Y", $row['aggiuntoIl'])." <b>alle</b> ".date("H:i", $row['aggiuntoIl'])."<b>.</b></p>";
            echo "<p><b>Iniziata il </b> ".date("d/m/Y", $row['inizio'])." <b>alle</b> ".date("H:i", $row['inizio'])."<b>.</b></p>";
            echo "<p><b>Terminata il </b> ".date("d/m/Y", $row['fine'])." <b>alle</b> ".date("H:i", $row['fine'])."<b>.</b></p>";

            echo "<p style=\"margin-top: 20px\"><b>FabCoin assegnati:</b> {$fabcoin}.</p>";

            echo "<p style=\"margin-top: 20px\"><b>Descrizione dell'attività:</b></p>";
            echo "{$row['descrizione']}";
          }
        }
      ?>
    </div>
    <?php
      include_once('../../inc/footer.inc.php');
    ?>
  </body>
</html>