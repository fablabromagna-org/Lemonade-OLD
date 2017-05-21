<?php
  require_once('../inc/autenticazione.inc.php');

  if($autenticazione -> gestionePortale != 1)
    header('Location: /');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../inc/header.inc.php');
    ?>
    <link type="text/css" rel="stylesheet" media="screen" href="/css/dashboard.css" />
  </head>
  <body>
    <?php
      include_once('../inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <h2>Log del portale</h1>
      <div style="overflow-x: auto;">
        <?php
          $sql = "SELECT id, idUtente, messaggio, data, livello FROM log ORDER BY id DESC";

          if($query = $mysqli -> query($sql)) {
        ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>ID Utente</th>
              <th>Livello</th>
              <th>Messaggio</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            <?php
              // Stampo gli utenti
              while($row = $query -> fetch_assoc()) {

                if($row['livello'] == 'WARN')
                  $row['livello'] = '<span style="padding: 3px 5px; border-radius: 3px; color: #fff; margin-top: 3px; display: inline-block; background: #ff9800; font-weight: 700;">WARN</span>';

                else if($row['livello'] == 'ALERT')
                  $row['livello'] = '<span style="padding: 3px 5px; border-radius: 3px; color: #fff; margin-top: 3px; display: inline-block; background: #f44336; font-weight: 700;">ALERT</span>';

                $row['messaggio'] = nl2br($row['messaggio']);

                echo "<tr>";
                echo "<td><a href=\"log.php?id={$row['id']}\" style=\"padding: 3px 5px;\" class=\"button\">{$row['id']}</a></td>";
                echo "<td><a href=\"utente.php?id={$row['idUtente']}\" style=\"padding: 3px 5px;\" class=\"button\">{$row['idUtente']}</a></td>";
                echo "<td>{$row['livello']}</td>";
                echo "<td>{$row['messaggio']}</td>";
                echo "<td>{$row['data']}</td>";
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
      include_once('../inc/footer.inc.html');
    ?>
  </body>
</html>
