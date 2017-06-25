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

              $pagina = $mysqli -> real_escape_string(isset($_GET['p']) ? trim($_GET['p']) : '');

              if(!preg_match("/^[0-9]+$/", $pagina))
                $pagina = 1;

              $query = new Paginator($mysqli, $sql, $pagina, 10);

              if(!$query -> result) {
                $console -> alert('Impossibile estrarre le notifiche '.$query -> mysqli -> error, $autenticazione -> id);
                echo '<p>Impossibile completare la richiesta.</p>';

              } else {

                // Stampo le notifiche
                while($row = $query -> result -> fetch_assoc()) {

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
      <div style="margin: 20px 0; text-align: center;"><?php echo $query -> getButtons('p'); ?></div>
    </div>
    <?php
      include_once('../inc/footer.inc.html');
    ?>
  </body>
</html>