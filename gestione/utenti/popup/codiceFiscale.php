<?php
  require_once('../../../inc/autenticazione.inc.php');

  $permessiTmp = $permessi -> whatCanHeDo($autenticazione -> id);
  if(!$permessiTmp['modificaAnagrafiche']['stato'])
    header('Location: /');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../../../inc/header.inc.php');
    ?>
    <script type="text/javascript" src="cf.js"></script>
    <style type="text/css">
      #headerInt { display: none !important; }
      #footer { display: none !important; }
      html { height: auto !important; }
    </style>
  </head>
  <body>
    <?php
      include_once('../../../inc/nav.inc.php');

      $id = $mysqli -> real_escape_string(isset($_GET['id']) ? trim($_GET['id']) : '');

      // Estraggo il profilo dell'utente
      $sql = "SELECT * FROM utenti LEFT JOIN comuni ON utenti.luogoNascita = comuni.codiceCatastale WHERE id = '{$id}'";

      if(!$query = $mysqli -> query($sql)) {
        echo '<div id="contenuto"><h1>Errore!</h1></div>';
        var_dump($mysqli -> error);

      } else {

        if($query -> num_rows != 1)
          echo '<div id="contenuto"><h1>Utente inesistente!</h1></div>';

        else {

          $profilo = $query -> fetch_assoc();
    ?>
    <div id="contenuto">
      <h3 style="margin-bottom: 20px;">Modifica il codice fiscale di <?php echo $profilo['nome'].' '.$profilo['cognome']; ?></h3>
      <form id="salva">
        <input type="text" id="cf" placeholder="Codice Fiscale" value="<?php echo $profilo['cf']; ?>" />
        <input type="hidden" value="<?php echo $id; ?>" id="idUtente" />
        <input type="submit" value="Salva" id="bottoneSalva" />
      </form>
      <br />
      <div style="color: #ff0000;" id="errore"></div>
      <a id="rimuovi">Rimuovi il codice fiscale</a>
      <br />
      <?php
          }
        }
      ?>
    </div>
    <?php
      include_once('../../../inc/footer.inc.php');
    ?>
  </body>
</html>
