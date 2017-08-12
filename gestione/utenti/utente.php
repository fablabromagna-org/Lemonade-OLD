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
    <script type="text/javascript" src="/js/gestione.utente.js"></script>
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

      .link { text-align: right; margin-bottom: 5px; }
    </style>
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
      $sql = "SELECT * FROM utenti WHERE id = '{$id}'";

      if(!$query = $mysqli -> query($sql))
        echo '<div id="contenuto"><h1>Errore!</h1></div>';

      else {

        if($query -> num_rows != 1)
          echo '<div id="contenuto"><h1>Utente inesistente!</h1></div>';

        else {

          $profilo = $query -> fetch_assoc();
    ?>
    <div id="contenuto">
      <h1>Profilo di <?php echo $profilo['nome'].' '.$profilo['cognome']; ?></h1>
      <p class="link"><a href="/gestione/attivita/?id=<?php echo $id; ?>">Visualizza le attività svolte</a></p>
      <p class="link"><a href="/gestione/badge/utente.php?id=<?php echo $profilo['id']; ?>">Gestione badge</a></p>
      <p class="link"><a href="/gestione/transazioni/utenteFabCoin.php?id=<?php echo $profilo['id']; ?>">Transazioni FabCoin</a></p>
      <p class="link"><a href="/gestione/utenti/social.php?id=<?php echo $profilo['id']; ?>">Social Networks</a></p>
      <p class="link"><a href="/gestione/presenze/presenze.php?id=<?php echo $profilo['id']; ?>">Presenze</a></p>
      <div class="box">
        <div>Profilo</div>
        <div>
          <img src="/images/utente.png" id="imgUtente" alt />
          <form id="modificaProfilo">
            <input type="text" value="<?php echo $profilo['nome'] ?>" id="profiloNome" placeholder="Nome" style="display: block;" />
            <input type="text" value="<?php echo $profilo['cognome'] ?>" id="profiloCognome" placeholder="Cognome" style="display: block;" />
            <input type="text" value="<?php echo $profilo['email'] ?>" id="profiloMail" placeholder="E-Mail" style="display: block;" />
            <input type="hidden" id="idUtente" value="<?php echo $profilo['id']; ?>" />
            <input type="submit" value="Salva" id="salvaProfilo" />
          </form>
          <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #aaa;">
            <p style="margin-bottom: 7px;">Se modifichi l'indirizzo email, l'utente riceverà un messaggio di avviso al vecchio indirizzo e dovrà confermare il nuovo cliccando sul link inviato alla nuova casella.</p>
          </div>
          <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #aaa;">
            <ul>
              <li>ID utente: <b><?php echo $profilo['id'] ?> </b></li>
              <li>Data di iscrizione: <b><?php echo date("d/m/Y H:i:s", $profilo['dataRegistrazione']); ?></b></li>
              <li>IP al momento della registrazione: <b><?php echo $profilo['ipRegistrazione']; ?></b></li>
            </ul>
          </div>
          <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #aaa;">
            <form id="modificaPermessi">
              <div>
                <label for="profiloGestione">Gestione portale</label>
                <select id="profiloGestione">
                  <option value="0" <?php if($profilo['gestionePortale'] == 0) echo 'selected' ?>>Non abilitata</option>
                  <option value="1" <?php if($profilo['gestionePortale'] == 1) echo 'selected' ?>>Abilitata</option>
                  <option value="2" <?php if($profilo['gestionePortale'] == 2) echo 'selected' ?>>Default dalla categoria</option>
                </select>
              </div>
              <div>
                <label for="profiloGestioneRete">Gestione rete</label>
                <select id="profiloGestioneRete">
                  <option value="0" <?php if($profilo['gestioneRete'] == 0) echo 'selected' ?>>Non abilitata</option>
                  <option value="1" <?php if($profilo['gestioneRete'] == 1) echo 'selected' ?>>Abilitata</option>
                  <option value="2" <?php if($profilo['gestioneRete'] == 2) echo 'selected' ?>>Default dalla categoria</option>
                </select>
              </div>
              <div>
                <label for="profiloSospeso">Sospensione</label>
                <select id="profiloSospeso">
                  <option value="0" <?php if($profilo['sospeso'] == 0) echo 'selected' ?>>Non attiva</option>
                  <option value="1" <?php if($profilo['sospeso'] == 1) echo 'selected' ?>>Attiva</option>
                </select>
              </div>
              <div>
                <label for="profiloConferma">Verifica E-Mail</label>
                <select id="profiloConferma">
                  <option value="0" <?php if($profilo['codiceAttivazione'] != 0) echo 'selected' ?>>Non verificata</option>
                  <option value="1" <?php if($profilo['codiceAttivazione'] == 0) echo 'selected' ?>>Verificata</option>
                </select>
              </div>
              <div>
                <label for="profiloCategoria">Categoria account</label>
                <select id="profiloCategoria">
                  <?php
                    foreach($categorieUtenti as $key => $value) {
                      $selected = ($profilo['categoria'] == $key) ? 'selected': '';
                      echo "<option value=\"{$key}\" {$selected}>{$value}</option>";
                    }
                  ?>
                </select>
              </div>
              <input type="submit" id="salvaPermessi" value="Salva" />
            </form>
          </div>
        </div>
      </div>
      <div class="box">
        <div>Password</div>
        <div>
          <a id="cambiaPwd" class="button" data-email="<?php echo $profilo['email']; ?>">Invia password</a>
          <p style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #aaa;">Puoi inviare una nuova password all'utente cliccando il bottone sopra. L'invio di una nuova password comporta la revoca della precedente.</p>
        </div>
      </div>
      <?php
          }
        }
      ?>
    </div>
    <?php
      include_once('../../inc/footer.inc.html');
    ?>
  </body>
</html>
