<?php
  require_once('../../inc/autenticazione.inc.php');

  if(!$permessi -> whatCanHeDo($autenticazione -> id)['visualizzarePresenze']['stato'])
    header('Location: /');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../../inc/header.inc.php');
    ?>
    <script type="text/javascript" src="/js/presenze.js"></script>
    <style type="text/css">
      #contenuto > h1 { margin-bottom: 20px; }
    </style>
  </head>
  <body>
    <?php
      include_once('../../inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <h1>Presenze</h1>
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Durata</th>
              <th>Data inizio</th>
              <th>Data fine</th>
              <th>Azioni</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $id = $mysqli -> real_escape_string(isset($_GET['id']) ? trim($_GET['id']) : '');

              $sql = "SELECT * FROM presenze WHERE idUtente = '{$id}' ORDER BY inizio DESC";

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
                  echo "<td><a onclick=\"annulla(this, {$row['id']})\">Annulla</a></td>";
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
