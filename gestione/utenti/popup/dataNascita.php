<?php
  require_once('../../../inc/autenticazione.inc.php');
  require_once('../../../vendor/autoload.php');

  use CodiceFiscale\Checker;
  use CodiceFiscale\Subject;

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
    <script type="text/javascript" src="data.js"></script>
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
      <h3 style="margin-bottom: 20px;">Modifica la data di nascita di <?php echo $profilo['nome'].' '.$profilo['cognome']; ?></h3>
      <form id="salva">
        <select id="giornoNascita">
          <option value="" disabled <?php if($profilo['dataNascita'] == null) echo 'selected'; ?>>Giorno</option>
          <?php
            for($i = 1; $i <= 31; $i++) {
          ?>
            <option value="<?php echo $i; ?>" <?php if(date("d", $profilo['dataNascita']) == $i && $profilo['dataNascita'] != null) echo 'selected'; ?>><?php echo $i; ?></option>
          <?php
            }
          ?>
        </select>
        <select id="meseNascita">
          <option value="" disabled <?php if($profilo['dataNascita'] == null) echo 'selected'; ?>>Mese</option>
          <?php
            for($i = 1; $i <= 12; $i++) {
          ?>
            <option value="<?php echo $i; ?>" <?php if(date("m", $profilo['dataNascita']) == $i && $profilo['dataNascita'] != null) echo 'selected'; ?>><?php echo $i; ?></option>
          <?php
            }
          ?>
        </select>
        <select id="annoNascita">
          <option value="" disabled <?php if($profilo['dataNascita'] == null) echo 'selected'; ?>>Anno</option>
          <?php
            for($i = date("Y"); $i >= 1915; $i--) {
          ?>
            <option value="<?php echo $i; ?>" <?php if(date("Y", $profilo['dataNascita']) == $i && $profilo['dataNascita'] != null) echo 'selected'; ?>><?php echo $i; ?></option>
          <?php
            }
          ?>
        </select>
        <br />
        <br />
        <input type="hidden" value="<?php echo $id; ?>" id="idUtente" />
        <input type="submit" value="Salva" id="bottoneSalva" />
      </form>
      <br />
      <div style="color: #ff0000;" id="errore"></div>
      <br />
      <a id="rimuovi">Rimuovi la data di nascita</a>
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
