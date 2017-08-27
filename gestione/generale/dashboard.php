<?php
  require_once('../../inc/autenticazione.inc.php');

  if(!$permessi -> whatCanHeDo($autenticazione -> id)['dashboard']['stato'])
    header('Location: /');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../../inc/header.inc.php');
    ?>
    <link type="text/css" rel="stylesheet" media="screen" href="/css/dashboard.css" />
    <script type="text/javascript" src="/js/gestione.dashboard.js"></script>
  </head>
  <body>
    <?php
      include_once('../../inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <h2>Aggiungi un messaggio</h1>
      <form id="aggiungiForm" style="margin-top: 20px;">
        <input type="text" id="titolo" placeholder="Titolo" />
        <input type="text" id="descrizione" placeholder="Descrizione" />
        <input type="text" id="link" placeholder="Link" />
        <input type="text" id="testo" placeholder="Testo del link" />
        <input type="submit" id="aggiungi" value="Aggiungi" />
      </form>
      <div style="overflow-x: auto;">
        <?php
          $sql = "SELECT * FROM dashboard";

          if($query = $mysqli -> query($sql)) {
        ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Titolo</th>
              <th>Descrizione</th>
              <th>Link</th>
              <th>Azioni</th>
            </tr>
          </thead>
          <tbody>
            <?php
              // Stampo gli utenti
              while($row = $query -> fetch_assoc()) {

                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['titolo']}</td>";
                echo "<td class=\"descrizione\">{$row['descrizione']}</td>";
                echo "<td><a href=\"{$row['linkBottone']}\" target=\"_blank\">{$row['titoloLink']}</a></td>";
                echo "<td><a onclick=\"elimina({$row['id']})\">Elimina</a></td>";
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
