<?php
  require_once('../../inc/autenticazione.inc.php');

  if(!$permessi -> whatCanHeDo($autenticazione -> id)['visualizzareBadge']['stato'])
    header('Location: /');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../../inc/header.inc.php');
    ?>
    <style type="text/css">
      form { margin-top: 20px; }
      form div { margin-bottom: 10px; }
      form div input { margin-right: 5px; margin-bottom: 10px; }
    </style>
    <script type="text/javascript" src="/js/badge/revoca.js"></script>
  </head>
  <body>
    <?php
      include_once('../../inc/nav.inc.php');

      // Pulisco i dati
      $idUtente = $mysqli -> real_escape_string(isset($_GET['idUtente']) ? trim($_GET['idUtente']) : '');
      $id = $mysqli -> real_escape_string(isset($_GET['id']) ? trim($_GET['id']) : '');
      $rfid = $mysqli -> real_escape_string(isset($_GET['rfid']) ? trim($_GET['rfid']) : '');

      $pagina = $mysqli -> real_escape_string(isset($_GET['p']) ? trim($_GET['p']) : '');

      if(!preg_match("/^[0-9]+$/", $pagina))
        $pagina = 1;
    ?>
    <div id="contenuto">
      <h1>Ricerca badge</h1>
      <form method="get">
        <div>
          <input type="text" name="rfid" placeholder="RFID" value="<?php echo $rfid; ?>" style="width: 46px; min-width: 160px;" maxlength="10" />
          <input type="text" name="id" placeholder="ID Badge" style="width: 46px; min-width: 65px;" value="<?php echo $id; ?>" />
          <input type="text" name="idUtente" placeholder="ID Utente" style="width: 54px; min-width: 70px;" value="<?php echo $idUtente; ?>" />
        </div>
        <a href="/gestione/badge/" class="button">Reset</a>
        <input type="submit" value="Cerca" />
      </form>
      <?php
        // Creo una query SQL generica
        $sql = "SELECT * FROM badge WHERE ";

        // Aggiungo i campi di ricerca
        if($rfid != "")
          $sql .= "rfid LIKE _utf8 '%".$rfid."%' AND ";

        if($id != "")
          $sql .= "id = '".$id."' AND ";

        if($idUtente != "")
          $sql .= "idUtente = '".$idUtente."' AND ";

        // Pulisco la query
        if(mb_substr($sql, -strlen(" AND ")) == " AND ")
          $sql = mb_substr($sql, 0, strlen($sql)-strlen(" AND "));

        if(mb_substr($sql, -strlen(" WHERE ")) == " WHERE ")
          $sql = mb_substr($sql, 0, strlen($sql)-strlen(" WHERE "));

        $sql .= " ORDER BY id DESC";

        $query = new Paginator($mysqli, $sql, $pagina, 10);

        // Eseguo la query
        if($query -> result) {

          // Sono presenti degli utenti con i criteri selezionati
          if($query -> result -> num_rows > 0) {

        ?>
          <p style="margin-top: 20px;">Trovato/i <?php echo $query -> result -> num_rows ?> badge/.</p>
          <div style="overflow-x: auto;">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Rilasciato da</th>
                  <th>ID Utente</th>
                  <th>RFID</th>
                  <th>Data rilascio</th>
                  <th>Revocato</th>
                  <th>Azioni</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  // Stampo i badge
                  while($row = $query -> result -> fetch_assoc()) {

                    echo "<tr>";
                    echo "<td>{$row['id']}</td>";
                    echo "<td><a href=\"/gestione/utenti/utente.php?id={$row['idUtenteRilascio']}\" class=\"button\" style=\"padding: 3px 5px;\">{$row['idUtenteRilascio']}</a></td>";
                    echo "<td><a href=\"/gestione/utenti/utente.php?id={$row['idUtenteRilascio']}\" class=\"button\" style=\"padding: 3px 5px;\">{$row['idUtente']}</a></td>";
                    echo "<td>{$row['rfid']}</td>";
                    echo "<td>".date("d/m/Y H:i:s", $row['dataRilascio'])."</td>";
                    echo ($row['revocato'] == false) ? '<td>NO</td>' : '<td><span style="padding: 3px 5px; border-radius: 3px; color: #fff; margin-top: 3px; display: inline-block; background: #f44336; font-weight: 700;">SI</span></td>';
                    echo ($row['revocato'] == false) ? "<td><a onclick=\"revoca(this, {$row['id']})\">Revoca</a></td>" : "<td></td>";
                    echo "</tr>";
                  }
                ?>
              </tbody>
            </table>
          </div>
          <div style="margin: 20px 0; text-align: center;"><?php echo $query -> getButtons('p'); ?></div>
          <?php
          } else
            echo "<p style=\"margin-top: 20px;\">Nessun badge è presente nel database con i criteri impostati.</p>";

        } else {
          echo "Impossibile completare la richiesta.";
          $console -> alert('Impossibile contattare il database '.$query -> mysqli -> error, $autenticazione -> id);
        }
      ?>
    </div>
    <?php
      include_once('../../inc/footer.inc.php');
    ?>
  </body>
</html>
