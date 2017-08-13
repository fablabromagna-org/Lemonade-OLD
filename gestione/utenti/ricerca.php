<?php
  require_once('../../inc/autenticazione.inc.php');

  if($autenticazione -> gestionePortale!= 1)
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

      #contenitoreAvanzate { display: none; }

      #filtroColonneContainer { display: none; background: rgba(0, 0, 0, 0.6); position: absolute; height: 100vh; z-index: 998; left: 0; right: 0; top: 0; bottom: 0; }
      #filtroColonne { left: calc(calc(100% - 320px) / 2); width: 320px; height: 180px; text-align: center; background: #fff; position: absolute; z-index: 999; top: 12vh; border-radius: 3px; }
    </style>
    <script type="text/javascript">
      // Apro e chiudo il bottone delle avanzate
      window.addEventListener('DOMContentLoaded', function() {

        // Filtro colonne
        document.getElementById('filtroColonneBottone').addEventListener('click', function(e) {
          e.preventDefault();

          document.getElementById('filtroColonneContainer').style.display = 'block';
        });

        // Impostazioni avanzate
        var chiuso = true
        document.getElementById('avanzate').addEventListener('click', function(e) {
          e.preventDefault()

          if(chiuso) {
            document.getElementById('avanzate').innerHTML = 'Chiudi avanzate'
            chiuso = false
            document.getElementById('contenitoreAvanzate').style.display = 'block'

          } else {
            document.getElementById('avanzate').innerHTML = 'Visualizza avanzate'
            chiuso = true
            document.getElementById('contenitoreAvanzate').style.display = 'none'
          }
        })
      })
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

      // Pulisco i dati
      $nome = $mysqli -> real_escape_string(isset($_GET['nome']) ? trim($_GET['nome']) : '');
      $cognome = $mysqli -> real_escape_string(isset($_GET['cognome']) ? trim($_GET['cognome']) : '');
      $email = $mysqli -> real_escape_string(isset($_GET['email']) ? trim($_GET['email']) : '');
      $gestione = $mysqli -> real_escape_string(isset($_GET['gestione']) ? trim($_GET['gestione']) : '');
      $gestioneRete = $mysqli -> real_escape_string(isset($_GET['gestioneRete']) ? trim($_GET['gestioneRete']) : '');
      $sospeso = $mysqli -> real_escape_string(isset($_GET['sospeso']) ? trim($_GET['sospeso']) : '');
      $ci = $mysqli -> real_escape_string(isset($_GET['ci']) ? trim($_GET['ci']) : '');
      $id = $mysqli -> real_escape_string(isset($_GET['id']) ? trim($_GET['id']) : '');
      $conferma = $mysqli -> real_escape_string(isset($_GET['conferma']) ? trim($_GET['conferma']) : '');
      $categoria = $mysqli -> real_escape_string(isset($_GET['categoria']) ? trim($_GET['categoria']) : '');
      $filtroColonne = @$_GET['filtroColonne'];

      if(!isset($filtroColonne) || gettype($filtroColonne) != 'array')
        $filtroColonne = array();
    ?>
    <div id="contenuto">
      <h1>Ricerca utenti</h1>
      <form method="get">
        <div>
          <input type="text" name="nome" placeholder="Nome" value="<?php echo $nome; ?>" />
          <input type="text" name="cognome" placeholder="Cognome" value="<?php echo $cognome; ?>" />
          <input type="text" name="email" placeholder="E-Mail" value="<?php echo $email; ?>" />
          <input type="text" name="id" placeholder="ID" style="width: 46px; min-width: 46px;" value="<?php echo $id; ?>" />
          <a id="avanzate">Visualizza avanzate</a>
        </div>
        <div id="contenitoreAvanzate">
          <div>
            <label for="conferma">Conferma email</label>
            <select name="conferma" id="conferma">
              <option>Confermati</option>
              <option value="1" <?php if($conferma == 1) echo 'selected' ?>>Tutti</option>
              <option value="2" <?php if($conferma == 2) echo 'selected' ?>>Da confermare</option>
            </select>
          </div>
          <div>
            <label for="categoria">Categoria account</label>
            <select name="categoria" id="categoria">
              <option>Tutti</option>
              <?php
                foreach($categorieUtenti as $key => $value) {
                  $selected = ($categoria == $key) ? 'selected': '';
                  echo "<option value=\"{$key}\" {$selected}>{$value}</option>";
                }

              ?>
            </select>
          </div>
          <div>
            <label for="sospeso">Sospensione</label>
            <select name="sospeso" id="sospeso">
              <option>Tutti</option>
              <option value="1" <?php if($sospeso == 1) echo 'selected' ?>>Solo sospesi</option>
              <option value="2" <?php if($sospeso == 2) echo 'selected' ?>>Solo attivi</option>
            </select>
          </div>
          <div>
            <label for="gestione">Gestione portale</label>
            <select name="gestione" id="gestione">
              <option>Tutti</option>
              <option value="1" <?php if($gestione == 1) echo 'selected' ?>>Non abilitata</option>
              <option value="2" <?php if($gestione == 2) echo 'selected' ?>>Abilitata</option>
            </select>
          </div>
          <div>
            <label for="gestioneRete">Gestione rete</label>
            <select name="gestioneRete" id="gestioneRete">
              <option>Tutti</option>
              <option value="1" <?php if($gestioneRete == 1) echo 'selected' ?>>Non abilitata</option>
              <option value="2" <?php if($gestioneRete == 2) echo 'selected' ?>>Abilitata</option>
            </select>
          </div>
          <div>
            <input type="checkbox" name="ci" value="1" <?php if($ci == '1') echo 'checked'; ?> />
            Case sensitive
          </div>
        </div>
        <a href="/gestione/utenti/ricerca.php" class="button">Reset</a>
        <input type="submit" value="Cerca" />
        <a id="filtroColonneBottone">Filtro colonne</a>
        <div id="filtroColonneContainer">
          <div id="filtroColonne">
            <select name="filtroColonne[]" multiple style="height: 95px; margin-top: 10px;">
              <option value="id" <?php if(array_search('id', $filtroColonne) !== false || $filtroColonne == []) echo 'selected' ?>>ID</option>
              <option value="nome" <?php if(array_search('nome', $filtroColonne) !== false || $filtroColonne == []) echo 'selected' ?>>Nome</option>
              <option value="cognome" <?php if(array_search('cognome', $filtroColonne) !== false || $filtroColonne == []) echo 'selected' ?>>Cognome</option>
              <option value="email" <?php if(array_search('email', $filtroColonne) !== false || $filtroColonne == []) echo 'selected' ?>>E-Mail</option>
              <option value="dataIscrizione" <?php if(array_search('dataIscrizione', $filtroColonne) !== false) echo 'selected' ?>>Data Iscrizione</option>
              <option value="categoria" <?php if(array_search('categoria', $filtroColonne) !== false) echo 'selected' ?>>Categoria</option>
              <option value="confEmail" <?php if(array_search('confEmail', $filtroColonne) !== false) echo 'selected' ?>>Conferma E-Mail</option>
            </select>
            <input type="submit" value="Applica" style="display: block; margin: 20px auto;" />
          </div>
        </div>
      </form>
      <?php
        // Creo una query SQL generica
        $sql = "SELECT utenti.nome nome,
                categorieUtenti.nome nomeCategoria,
                utenti.id,
                utenti.email,
                 utenti.cognome,
                 utenti.categoria,
                 utenti.codiceAttivazione,
                 utenti.dataRegistrazione FROM utenti INNER JOIN categorieUtenti ON categorieUtenti.id = utenti.categoria WHERE ";

        // Case sensitive o insensitive
        $ci = ($ci == 1) ? '' : ' COLLATE utf8mb4_general_ci';

        // Aggiungo i campi di ricerca
        if($nome != "")
          $sql .= "utenti.nome LIKE _utf8mb4 '%".$nome."%'".$ci." AND ";

        if($cognome != "")
          $sql .= "cognome LIKE _utf8mb4 '%".$cognome."%'".$ci." AND ";

        if($email != "")
          $sql .= "email LIKE _utf8mb4 '%".$email."%'".$ci." AND ";

        if($id != "")
          $sql .= "utenti.id = '".$id."' AND ";

        switch($gestione) {
          case 1:
            $sql .= "utenti.gestionePortale = '0' OR utenti.gestionePortale = '2' AND categorieUtenti.gestionePortale = '0' AND ";
            break;

          case 2:
          $sql .= "utenti.gestionePortale = '1' OR utenti.gestionePortale = '2' AND categorieUtenti.gestionePortale = '1' AND ";
            break;
        }

        switch($gestioneRete) {
          case 1:
            $sql .= "utenti.gestioneRete = '0' OR utenti.gestioneRete = '2' AND categorieUtenti.gestioneRete = '0' AND ";
            break;

          case 2:
          $sql .= "utenti.gestioneRete = '1' OR utenti.gestioneRete = '2' AND categorieUtenti.gestioneRete = '1' AND ";
            break;
        }

        switch($conferma) {
          default:
            $sql .= "codiceAttivazione = '0' AND ";
            break;

          case 2:
            $sql .= "codiceAttivazione <> '0' AND ";
            break;

          case 1:
            $sql .= ""; // Non seleziono nulla
            break;
        }

        if($categoria != 'Tutti' && $categoria != '')
          $sql .= "categoria = '{$categoria}' AND ";

        switch($sospeso) {
          case 1:
            $sql .= "sospeso = TRUE AND ";
            break;
        }

        // Pulisco la query
        if(mb_substr($sql, -strlen(" AND ")) == " AND ")
          $sql = mb_substr($sql, 0, strlen($sql)-strlen(" AND "));

        if(mb_substr($sql, -strlen(" WHERE ")) == " WHERE ")
          $sql = mb_substr($sql, 0, strlen($sql)-strlen(" WHERE "));

        $pagina = $mysqli -> real_escape_string(isset($_GET['p']) ? trim($_GET['p']) : '');

        if(!preg_match("/^[0-9]+$/", $pagina))
          $pagina = 1;

        $query = new Paginator($mysqli, $sql, $pagina, 10);

        // Eseguo la query
        if($query -> result) {

          // Sono presenti degli utenti con i criteri selezionati
          if($query -> result -> num_rows > 0) {

          ?>
          <style type="text/css">
            .id {  display: <?php if(array_search('id', $filtroColonne) !== false || $filtroColonne == []) echo 'auto'; else echo 'none'; ?>; }
            .nome {  display: <?php if(array_search('nome', $filtroColonne) !== false || $filtroColonne == []) echo 'auto'; else echo 'none'; ?>; }
            .cognome {  display: <?php if(array_search('cognome', $filtroColonne) !== false || $filtroColonne == []) echo 'auto'; else echo 'none'; ?>; }
            .email {  display: <?php if(array_search('email', $filtroColonne) !== false || $filtroColonne == []) echo 'auto'; else echo 'none'; ?>; }
            .dataIscrizione {  display: <?php if(array_search('dataIscrizione', $filtroColonne) !== false) echo 'auto'; else echo 'none'; ?>; }
            .categoria {  display: <?php if(array_search('categoria', $filtroColonne) !== false) echo 'auto'; else echo 'none'; ?>; }
            .confEmail {  display: <?php if(array_search('confEmail', $filtroColonne) !== false) echo 'auto'; else echo 'none'; ?>; }
          </style>
          <p style="margin-top: 20px;">Trovato/i <?php echo $query -> result -> num_rows ?> utente/i.</p>
          <div style="overflow-x: auto;">
            <table>
              <thead>
                <tr>
                  <th class="id">ID</th>
                  <th class="nome">Nome</th>
                  <th class="cognome">Cognome</th>
                  <th class="email">E-Mail</th>
                  <th class="dataIscrizione">Data iscrizione</th>
                  <th class="categoria">Categoria</th>
                  <th class="confEmail">Conf. email</th>
                </tr>
              </thead>
              <tbody>
            <?php
              // Stampo gli utenti
              while($row = $query -> result -> fetch_assoc()) {

                if($row['codiceAttivazione'] != 0)
                  $row['codiceAttivazione'] = 'NO';

                else
                  $row['codiceAttivazione'] = 'SI';


                echo "<tr>";
                echo "<td class=\"id\"><a href=\"utente.php?id={$row['id']}\" class=\"button\">{$row['id']}</a></td>";
                echo "<td class=\"nome\">{$row['nome']}</td>";
                echo "<td class=\"cognome\">{$row['cognome']}</td>";
                echo "<td class=\"email\">{$row['email']}</td>";
                echo "<td class=\"dataIscrizione\">".date("d/m/Y", $row['dataRegistrazione'])."</td>";
                echo "<td class=\"categoria\">{$row['nomeCategoria']}</td>";
                echo "<td class=\"confEmail\">{$row['codiceAttivazione']}</td>";
                echo "</tr>";
              }
            ?>
              </tbody>
            </table>
          </div>
          <div style="margin: 20px 0; text-align: center;"><?php echo $query -> getButtons('p'); ?></div>
          <?php
          } else
            echo "<p style=\"margin-top: 20px;\">Nessun utente è presente nel database con i criteri impostati.</p>";

        } else {
          echo "Impossibile completare la richiesta.";
          $console -> alert('Impossibile contattare il database! '.$mysqli -> error, $autenticazione -> id);
        }


      ?>
    </div>
    <?php
      include_once('../../inc/footer.inc.php');
    ?>
  </body>
</html>
