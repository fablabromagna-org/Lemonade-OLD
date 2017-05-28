<?php
  require_once('../inc/autenticazione.inc.php');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../inc/header.inc.php');
    ?>
    <style type="text/css">
      .box { display: table; border: 1px solid <?php echo TEMA_BG_PRINCIPALE ?>; width: 100%; border-radius: 3px; margin-bottom: 15px; }
      .box > div { display: table-cell; vertical-align: top; padding: 10px; }
      .box > div:first-child { background: <?php echo TEMA_BG_PRINCIPALE ?>; width: 120px; font-size: 20px;  }

      #imgUtente { width: 100px; border-radius: 50%; }

      #cambioPwd { border-bottom: 1px solid #aaa; width: 100%; margin-bottom: 15px; padding-bottom: 15px; }
      #cambioPwd input { display: block; margin-bottom: 5px; }

      #verifica div div { border-bottom: 1px solid #aaa; margin-bottom: 15px; padding-bottom: 15px; }
      #verifica div div:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

      #contenuto > h1 { margin-bottom: 20px; }
    </style>
  </head>
  <body>
    <?php
      include_once('../inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <h1>Attività svolte</h1>
      <p>In questa pagina sono presenti tutte le attività che hai svolto all'interno di FabLab Romagna e ti sono state riconosciute.</p>
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>FabCoin</th>
              <th>Data inizio</th>
              <th>Data fine</th>
              <th>Descrizione</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $sql = "SELECT * FROM attivita WHERE idUtente = '{$autenticazione -> id}' ORDER BY id ASC";

              if(!$query = $mysqli -> query($sql))
                echo '<p>Impossibile estrarre le attività dal database.</p>';

              else {

                // Stampo le attività
                while($row = $query -> fetch_assoc()) {

                  if(strlen($row['descrizione']) > 30)
                    $row['descrizione'] = substr($row['descrizione'], 0, 27).'...';

                  if($row['fabcoin'] === null)
                    $row['fabcoin'] = '--';

                  echo "<tr>";
                  echo "<td><a href=\"/account/visualizzaAttivita.php?id={$row['id']}\" style=\"padding: 3px 5px;\" class=\"button\">{$row['id']}</a></td>";
                  echo "<td>{$row['fabcoin']}</td>";
                  echo "<td>".date("d/m/Y H:i", $row['inizio'])."</td>";
                  echo "<td>".date("d/m/Y H:i", $row['fine'])."</td>";
                  echo "<td>{$row['descrizione']}</td>";
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