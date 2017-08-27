<?php
  require_once('../../inc/autenticazione.inc.php');

  if(!$permessi -> whatCanHeDo($autenticazione -> id)['visualizzareMakerSpace']['stato'])
    header('Location: /');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../../inc/header.inc.php');
    ?>
    <script type="text/javascript" src="/js/makerspace/aggiungiElimina.js"></script>
  </head>
  <body>
    <?php
      include_once('../../inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <h2>Maker Space</h1>
      <form id="aggiungiForm" style="margin-top: 20px;">
        <input type="text" id="nome" placeholder="Nome" />
        <input type="submit" id="aggiungi" value="Aggiungi" />
      </form>
      <div style="overflow-x: auto;">
        <?php
          $sql = "SELECT * FROM makerspace WHERE eliminato = FALSE ORDER BY id ASC";

          $pagina = $mysqli -> real_escape_string(isset($_GET['p']) ? trim($_GET['p']) : '');

          if(!preg_match("/^[0-9]+$/", $pagina))
            $pagina = 1;

          $query = new Paginator($mysqli, $sql, $pagina, 10);

          if($query -> result) {
        ?>
        <div style="overflow-x: auto;">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Azioni</th>
              </tr>
            </thead>
            <tbody>
              <?php
                // Stampo gli utenti
                while($row = $query -> result -> fetch_assoc()) {

                  echo "<tr>";
                  echo "<td><a href=\"/gestione/makerspace/makerspace.php?id={$row['id']}\" class=\"button\" style=\"padding: 3px 5px;\">{$row['id']}</a></td>";
                  echo "<td>{$row['nome']}</td>";
                  echo "<td><a onclick=\"elimina(this, {$row['id']})\">Elimina</a></td>";
                  echo "</tr>";
                }
              ?>
            </tbody>
          </table>
        </div>
        <div style="margin: 20px 0; text-align: center;"><?php echo $query -> getButtons('p'); ?></div>
        <?php
          } else {
            echo "<p>Impossibile completare la richiesta!</p>";
            $console -> alert('Impossibile eseguire la query! '.$query -> mysqli -> error, $autenticazione -> id);
          }
        ?>
      </div>
    </div>
    <?php
      include_once('../../inc/footer.inc.php');
    ?>
  </body>
</html>
