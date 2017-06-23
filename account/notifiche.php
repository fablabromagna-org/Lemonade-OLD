<?php
  require_once('../inc/autenticazione.inc.php');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../inc/header.inc.php');
    ?>
    <script type="text/javascript" src="/js/notifiche/elimina.js"></script>
    <script type="text/javascript" src="/js/notifiche/letto.js"></script>
  </head>
  <body>
    <?php
      include_once('../inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <h1>Notifiche</h1>
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>Data</th>
              <th>Descrizione</th>
              <th>Azioni</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $sql = "SELECT * FROM notifiche WHERE idUtente = '{$autenticazione -> id}' ORDER BY id DESC";

              if(!$query = $mysqli -> query($sql)) {
                $console -> alert('Impossibile estrarre le notifiche '.$mysqli -> error, $autenticazione -> id);
                echo '<p>Impossibile estrarre le notifiche dal database.</p>';

              } else {

                // Stampo le notifiche
                while($row = $query -> fetch_assoc()) {

                  $nonLettoCSS = "";

                  if($row['letto'] == false)
                    $nonLettoCSS = " style=\"background: rgba(0, 98, 217, 0.1)\"";

                  echo "<tr{$nonLettoCSS}>";
                  echo "<td>".date("d/m/Y H:i", $row['data'])."</td>";
                  echo "<td>{$row['descrizione']}</td>";
                  echo "<td>";

                  if($row['link'] != null)
                    echo "<a href=\"{$row['link']}\">Apri</a>";

                  if($row['letto'] == false)
                    echo " <a onclick=\"letto(this, {$row['id']})\">Letto</a>";

                  echo " <a onclick=\"elimina(this, {$row['id']})\">Elimina</a>";

                  echo "</td>";
                  echo "</tr>";
                }
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php
      include_once('../inc/footer.inc.html');
    ?>
  </body>
</html>