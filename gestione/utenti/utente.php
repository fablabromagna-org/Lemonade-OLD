<?php
  require_once('../../inc/autenticazione.inc.php');
  require_once('../../vendor/autoload.php');

  use CodiceFiscale\Checker;
  use CodiceFiscale\Subject;

  $permessiTmp = $permessi -> whatCanHeDo($autenticazione -> id);
  if(!$permessiTmp['visualizzareUtenti']['stato'])
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

    <script type="text/javascript">
      var finestra

      function openPopup(popurl){
        finestra = window.open(popurl, "", "width=560,height=380,titlebar=no,resizable=no,status=no,location=no")
        finestra.onunload = function() {
          window.parent.location.reload()
          finestra = undefined
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
      <?php if($permessiTmp['visualizzareAttivita']['stato']) { ?><p class="link"><a href="/gestione/attivita/?id=<?php echo $id; ?>">Visualizza le attività svolte</a></p><?php } ?>
      <?php if($permessiTmp['visualizzareBadge']['stato']) { ?><p class="link"><a href="/gestione/badge/utente.php?id=<?php echo $profilo['id']; ?>">Gestione badge</a></p><?php } ?>
      <?php if($permessiTmp['visualizzareTransazioniFabCoin']['stato']) { ?><p class="link"><a href="/gestione/transazioni/utenteFabCoin.php?id=<?php echo $profilo['id']; ?>">Transazioni FabCoin</a></p><?php } ?>
      <p class="link"><a href="/gestione/utenti/social.php?id=<?php echo $profilo['id']; ?>">Social Networks</a></p>
      <?php if($permessiTmp['visualizzarePresenze']['stato']) { ?><p class="link"><a href="/gestione/presenze/presenze.php?id=<?php echo $profilo['id']; ?>">Presenze</a></p><?php } ?>
      <?php if($permessiTmp['visualizzarePermessi']['stato']) { ?><p class="link"><a href="/gestione/permessi/utente/?id=<?php echo $profilo['id']; ?>">Permessi dell'utente</a></p><?php } ?>
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
        <div>Anagrafiche</div>
        <div>
          <?php
            // Alert incosistenza dati anagrafici
            if($profilo['cf'] != null
              && $profilo['luogoNascita'] != null
              && $profilo['dataNascita'] != null
              && $profilo['sesso'] != null) {

                $subject = new Subject(
                  array(
                    'name' => $profilo['nome']
                    ,'surname' => $profilo['cognome']
                    ,'birthDate' => date('Y-m-d', $profilo['dataNascita'])
                    ,'gender' => ($profilo['sesso'] ? 'F' : 'M')
                    ,'belfioreCode' => $profilo['luogoNascita']
                  )
                );

                $checker = new Checker($subject, array(
                  "codiceFiscaleToCheck" => $profilo['cf'],
                  "omocodiaLevel" => Checker::ALL_OMOCODIA_LEVELS
                ));
                if(!$checker -> check()) {
          ?>
          <div style="border: 1.5px solid #F9A825; padding: 10px; border-radius: 3px; margin-bottom: 20px;">
            <h3 style="margin-bottom: 5px;">Attenzione</h4>
            <p>Il controllo sul codice fiscale ha dato esito negativo!<br />Ricontrolla i dati anagrafici.</p>
          </div>
          <?php
                }
              }
          ?>
          <p>Data di nascita: <?php if($profilo['dataNascita'] == null) echo 'N/D'; else echo '<b>'.date("d/m/Y", $profilo['dataNascita']).'</b>'; ?> <a href="javascript:openPopup('/gestione/utenti/popup/dataNascita.php?id=<?php echo $id ?>')" style="float: right">Modifica</a></p>
          <?php
            if($profilo['luogoNascita'] != null) {
              $sql = "SELECT * FROM comuni WHERE codiceCatastale = '{$profilo['luogoNascita']}' LIMIT 0, 1";
              $query = $mysqli -> query($sql);

              if(!$query) {
                $luogo = $profilo['luogoNascita'];
                $console -> alert('Impossibile estrarre il luogo di nascita. '.$mysqli -> error, $autenticazione -> id);

              } else {
                $row = $query -> fetch_assoc();
                $luogo = ($row['stato'] == null) ? $row['comune'] : $row['stato'];
              }
            }
          ?>
          <p>Luogo di nascita: <?php if($profilo['luogoNascita'] == null) echo 'N/D'; else echo '<b>'.$luogo.'</b>'; ?> <a href="javascript:openPopup('/gestione/utenti/popup/luogoNascita.php?id=<?php echo $id ?>')" style="float: right">Modifica</a></p>
          <p>Sesso: <?php if($profilo['sesso'] == null) echo 'N/D'; else echo '<b>'.($profilo['sesso'] ? 'Donna' : 'Uomo').'</b>'; ?> <a href="javascript:openPopup('/gestione/utenti/popup/sesso.php?id=<?php echo $id ?>')" style="float: right">Modifica</a></p>
          <p>Codice Fiscale: <?php if($profilo['cf'] == null) echo 'N/D'; else echo '<b>'.$profilo['cf'].'</b>'; ?> <a href="javascript:openPopup('/gestione/utenti/popup/codiceFiscale.php?id=<?php echo $id ?>')" style="float: right">Modifica</a></p>
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
      include_once('../../inc/footer.inc.php');
    ?>
  </body>
</html>
