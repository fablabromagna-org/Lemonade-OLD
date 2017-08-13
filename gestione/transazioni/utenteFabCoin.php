<?php
  require_once('../../inc/autenticazione.inc.php');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../../inc/header.inc.php');
    ?>
    <style type="text/css">
      #contenuto > h1 { margin-bottom: 20px; }
    </style>
  </head>
  <body>
    <?php
      include_once('../../inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <h1>Borsello FabCoin</h1>
      <p>Le transazioni <del>barrate</del> sono state annullate.</p>
      <?php
        $id = $mysqli -> real_escape_string(isset($_GET['id']) ? trim($_GET['id']) : '');

        $sql = "SELECT SUM(valore) AS somma FROM transazioniFabCoin WHERE idUtente = '{$id}' AND annullata  = FALSE";
        $query = $mysqli -> query($sql);
        if($query) {
          $row = $query -> fetch_assoc();

          if($row['somma'] == '')
            $row['somma'] = 0;

          echo '<p style="margin-top: 20px;">Il saldo FabCoin dell\'utente è di <b>'.$row['somma'].'</b>.</p>';
        }
      ?>
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>FabCoin</th>
              <th>Data</th>
              <th>Descrizione</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $sql = "SELECT * FROM transazioniFabCoin WHERE idUtente = '{$id}' ORDER BY id DESC";

              $pagina = $mysqli -> real_escape_string(isset($_GET['p']) ? trim($_GET['p']) : '');

              if(!preg_match("/^[0-9]+$/", $pagina))
                $pagina = 1;

              $query = new Paginator($mysqli, $sql, $pagina, 10);

              if(!$query -> result) {
                echo '<p>Impossibile completare la richiesta.</p>';
                $console -> alert('Impossibile estrarre le attività dal database! '.$query -> mysqli -> error, $autenticazione -> id);

              } else {

                // Stampo le attività
                while($row = $query -> result -> fetch_assoc()) {

                  if(strlen($row['descrizione']) > 30)
                    $row['descrizione'] = substr($row['descrizione'], 0, 27).'...';

                  $row['descrizione'] = strip_tags($row['descrizione']);

                  $annullata = '';
                  if($row['annullata'] == true)
                    $annullata = ' style="text-decoration: line-through;"';

                  echo "<tr{$annullata}>";
                  echo "<td>{$row['id']}</td>";
                  echo "<td>{$row['valore']}</td>";
                  echo "<td>".date("d/m/Y H:i", $row['dataInserimento'])."</td>";
                  echo "<td>{$row['descrizione']}</td>";
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
      include_once('../../inc/footer.inc.php');
    ?>
  </body>
</html>
