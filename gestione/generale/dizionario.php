<?php
  require_once('../../inc/autenticazione.inc.php');

  if(!$permessi -> whatCanHeDo($autenticazione -> id)['dizionario']['stato'])
    header('Location: /');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../../inc/header.inc.php');
    ?>
    <link type="text/css" rel="stylesheet" media="screen" href="/css/dashboard.css" />
    <script type="text/javascript" src="/js/dizionario.js"></script>
  </head>
  <body>
    <?php
      include_once('../../inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <h2>Aggiungi un elemento</h2>
      <form id="aggiungiForm" style="margin-top: 20px;">
        <input type="text" id="chiave" placeholder="Chiave" />
        <input type="text" id="valore" placeholder="Valore" />
        <input type="submit" id="aggiungi" value="Aggiungi" />
      </form>
      <h2 style="margin-top: 20px;">Dizionario</h2>
      <div style="overflow-x: auto;">
        <?php
          $sql = "SELECT * FROM dizionario";

          if($query = $mysqli -> query($sql)) {
        ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Chiave</th>
              <th>Valore</th>
              <th>Azioni</th>
            </tr>
          </thead>
          <tbody>
            <?php
              // Stampo gli utenti
              while($row = $query -> fetch_assoc()) {

                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['chiave']}</td>";
                echo "<td><input type=\"text\" id=\"dizionario-id{$row['id']}\" value=\"{$row['valore']}\" placeholder=\"Valore\" /></td>";
                echo "<td><a onclick=\"salva({$row['id']})\">Salva</a> <a onclick=\"rimuovi({$row['id']})\">Elimina</a></td>";
                echo "</tr>";
              }
            ?>
          </tbody>
        </table>
        <?php
          } else
            echo "<p>Impossibile comunicare con il database!</p>";
        ?>
      </div>
    </div>
    <?php
      include_once('../../inc/footer.inc.php');
    ?>
  </body>
</html>
