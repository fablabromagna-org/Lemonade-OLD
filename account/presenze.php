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
      <h1>Le tue presenze</h1>
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Durata</th>
              <th>Data inizio</th>
              <th>Data fine</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $sql = "SELECT *FROM presenze WHERE idUtente = '{$autenticazione -> id}' ORDER BY inizio DESC";

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

                  $annullata = '';
                  if($row['annullata'] == true)
                    $annullata = ' style="text-decoration: line-through;"';

                  if($row['fine'] === null) {
                    $fine = '';
                    $durata = '';

                  } else {
                    $fine = date("d/m/Y H:i:s", $row['fine']);
                    $durata = sec2str((int)$row['fine'] - (int)$row['inizio']);
                  }

                  echo "<tr{$annullata}>";
                  echo "<td>{$row['id']}</td>";
                  echo "<td>".$durata."</td>";
                  echo "<td>".date("d/m/Y H:i:s", $row['inizio'])."</td>";
                  echo "<td>".$fine."</td>";
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
