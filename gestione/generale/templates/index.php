<?php
  require_once('../../../inc/autenticazione.inc.php');

  if(!$permessi -> whatCanHeDo($autenticazione -> id)['visualizzareTemplate']['stato'])
    header('Location: /');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../../../inc/header.inc.php');
    ?>
  </head>
  <body>
    <?php
      include_once('../../../inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <h2>Templates</h1>
      <div style="overflow-x: auto;">
        <?php
          $sql = "SELECT * FROM templates";

          if($query = $mysqli -> query($sql)) {
        ?>
        <table>
          <thead>
            <tr>
              <th>Titolo</th>
              <th>Azioni</th>
            </tr>
          </thead>
          <tbody>
            <?php
              // Stampo gli utenti
              while($row = $query -> fetch_assoc()) {

                echo "<tr>";
                echo "<td>{$row['titolo']}</td>";
                echo "<td><a href=\"/gestione/generale/templates/modifica.php?id={$row['id']}\">Modifica</a></td>";
                echo "</tr>";
              }
            ?>
          </tbody>
        </table>
        <?php
          } else {
            $console -> alert('Impossibile comunicare con il database! '.$mysqli -> error, $autenticazione -> id);
            echo "<p>Impossibile completare la richiesta!</p>";
          }
        ?>
      </div>
    </div>
    <?php
      include_once('../../../inc/footer.inc.php');
    ?>
  </body>
</html>
