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
    <link type="text/css" rel="stylesheet" media="screen" href="/css/dashboard.css" />
    <script type="text/javascript" src="/js/gestione.categorieUtenti.js"></script>
  </head>
  <body>
    <?php
      include_once('../../inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <h2>Categorie utenti</h1>
      <form id="aggiungiForm" style="margin-top: 20px;">
        <input type="text" id="nome" placeholder="Nome" />
        <p style="margin-top: 5px;"><input type="checkbox" id="portale" /> Gestione portale</p>
        <p style="margin-top: 5px; margin-bottom: 5px;"><input type="checkbox" id="rete" /> Gestione rete</p>
        <input type="submit" id="aggiungi" value="Aggiungi" />
      </form>
      <div style="overflow-x: auto;">
        <?php
          $sql = "SELECT * FROM categorieUtenti";

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
                <th>Gestione portale</th>
                <th>Gestione rete</th>
                <th>Azioni</th>
              </tr>
            </thead>
            <tbody>
              <?php
                // Stampo gli utenti
                while($row = $query -> result -> fetch_assoc()) {

                  if($row['gestioneRete'] == false)
                    $row['gestioneRete'] = 'Non abilitato';

                  else
                    $row['gestioneRete'] = 'Abilitato';

                  if($row['gestionePortale'] == false)
                    $row['gestionePortale'] = 'Non abilitato';

                  else
                    $row['gestionePortale'] = 'Abilitato';

                  echo "<tr>";
                  echo "<td>{$row['id']}</td>";
                  echo "<td>{$row['nome']}</td>";
                  echo "<td class=\"descrizione\">{$row['gestionePortale']}</td>";
                  echo "<td>{$row['gestioneRete']}</td>";

                  if($row['id'] != 1)
                    echo "<td><a onclick=\"elimina({$row['id']})\">Elimina</a><br /><a onclick=\"modifica({$row['id']})\">Modifica nome</a><br /><a onclick=\"spostaIn({$row['id']})\">Sposta utenti</a></td>";

                  else
                    echo "<td><a onclick=\"modifica({$row['id']})\">Modifica nome</a><br /><a onclick=\"spostaIn({$row['id']})\">Sposta utenti</a></td>";

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
            $console -> alert('Impossibile completare la richiesta! '.$query -> mysqli -> error, $autenticazione -> id);
          }
        ?>
      </div>
    </div>
    <?php
      include_once('../../inc/footer.inc.php');
    ?>
  </body>
</html>
