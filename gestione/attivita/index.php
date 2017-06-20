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
    <style type="text/css">
      .box { display: table; border: 1px solid <?php echo TEMA_BG_PRINCIPALE ?>; width: 100%; border-radius: 3px; margin-bottom: 15px; }
      .box > div { display: table-cell; vertical-align: top; padding: 10px; }
      .box > div:first-child { background: <?php echo TEMA_BG_PRINCIPALE ?>; width: 120px; font-size: 20px;  }

      #imgUtente { max-width: 75px; border-radius: 50%; }

      #cambioPwd { border-bottom: 1px solid #aaa; width: 100%; margin-bottom: 15px; padding-bottom: 15px; }
      #cambioPwd input { display: block; margin-bottom: 5px; }

      #contenuto > h1 { margin-bottom: 20px; }

      form input, form select { margin-bottom: 10px; }
      form input:last-child, select { margin-bottom: 0; }
    </style>
    <script type="text/javascript">
      function elimina(id) {
        var xhr = new XMLHttpRequest()
        xhr.open('POST', '/ajax/attivita/elimina.php', true)
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

        xhr.send('id='+encodeURIComponent(id))

        xhr.onreadystatechange = function() {

         if(xhr.readyState === 4 && xhr.status === 200) {

           var res = JSON.parse(xhr.response)

           if(res.errore === true)
            alert(res.msg)

           else {
             alert('Attività eliminata con successo!')
             location.href = location.href
           }


         } else if(xhr.readyState === 4)
          alert('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
        }
      }
    </script>
  </head>
  <body>
    <?php
      include_once('../../inc/nav.inc.php');

      // Estraggo tutte le categorie degli utenti
      $sql = "SELECT id, nome FROM categorieUtenti";

      $categorieUtenti = array();

      if($query = $mysqli -> query($sql)) {

        while($key = $query -> fetch_array(MYSQLI_ASSOC))
          $categorieUtenti[$key['id']] = $key['nome'];


      } else {
        echo 'Impossibile estrarre le categorie degli utenti.';
        exit();
      }

      $id = $mysqli -> real_escape_string(isset($_GET['id']) ? trim($_GET['id']) : '');

      // Estraggo il profilo dell'utente
      $sql = "SELECT * FROM attivita WHERE idUtente = '{$id}' ORDER BY id DESC";

      if(!$query = $mysqli -> query($sql))
        echo '<div id="contenuto"><h1>Errore!</h1></div>';

      else {


    ?>
    <div id="contenuto">
      <h1>Riepilogo attività</h1>
      <a href="aggiungi.php?id=<?php echo $id; ?>" class="button">Aggiungi</a>
      <div style="overflow-x: auto;">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>FabCoin</th>
              <th>Data inizio</th>
              <th>Data fine</th>
              <th>Descrizione</th>
              <th>Azioni</th>
            </tr>
          </thead>
          <tbody>
            <?php
                // Stampo le attività
                while($row = $query -> fetch_assoc()) {

                  if(strlen($row['descrizione']) > 30)
                    $row['descrizione'] = substr($row['descrizione'], 0, 27).'...';

                  if($row['fabcoin'] === null)
                    $row['fabcoin'] = '--';

                  echo "<tr>";
                  echo "<td><a href=\"attivita.php?id={$row['id']}\" style=\"padding: 3px 5px;\" class=\"button\">{$row['id']}</a></td>";
                  echo "<td>{$row['fabcoin']}</td>";
                  echo "<td>".date("d/m/Y H:i", $row['inizio'])."</td>";
                  echo "<td>".date("d/m/Y H:i", $row['fine'])."</td>";
                  echo "<td>{$row['descrizione']}</td>";
                  echo "<td><a href=\"modifica.php?id={$row['id']}\">Modifica</a> </br/><a onclick=\"elimina({$row['id']})\">Elimina</a></td>";
                  echo "</tr>";
                }
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php
      include_once('../../inc/footer.inc.html');
    ?>
  </body>
</html>