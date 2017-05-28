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
    <script type="text/javascript" src="/js/gestione.attivita.aggiungi.js"></script>
  </head>
  <body>
    <?php
      include_once('../../inc/nav.inc.php');

    ?>
    <div id="contenuto">
      <h1>Aggiungi un'attività</h1>
      <form id="formAggiungi" style="margin-top: 20px;">

        <link href="https://cdn.quilljs.com/1.2.3/quill.snow.css" rel="stylesheet">
        <script src="https://cdn.quilljs.com/1.2.3/quill.js"></script>
        <div id="editor"></div>
        <script>
          editor = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Inserisci la descrizione dell\'attività...'
          });
        </script>
        <p style="margin-top: 20px;">Data e ora di inzio dell'attività.</p>
        <select id="giornoStart">
          <?php
            for($i = 1; $i <= 31; $i++) {
              $j = ((int)date('d') == $i) ? 'selected' : '';
              echo "<option value=\"{$i}\" {$j}>{$i}</option>";
            }
          ?>
        </select>
        <select id="meseStart">
          <?php
            $mesi = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];

            for($i = 0; $i < 12; $i++) {
              $j = $i + 1;
              $t = ((int)date('m') == $j) ? 'selected' : '';
              echo "<option value=\"{$j}\" {$t}>{$mesi[$i]}</option>";
            }
          ?>
        </select>
        <select id="annoStart">
          <?php
            for($i = (int)date('Y'); $i > 2010; $i--) {
              $j = ((int)date('Y') == $i) ? 'selected' : '';
              echo "<option value=\"{$i}\" {$j}>{$i}</option>";
            }
          ?>
        </select>
        <select id="oraStart" style="margin-left: 15px;">
          <?php
            for($i = 0; $i <= 23; $i++) {
              $j = ((int)date('H') == $i) ? 'selected' : '';
              echo "<option value=\"{$i}\" {$j}>{$i}</option>";
            }
          ?>
        </select>
        <select id="minutoStart">
          <?php
            for($i = 0; $i <= 59; $i++) {
              $j = ((int)date('i') == $i) ? 'selected' : '';
              echo "<option value=\"{$i}\" {$j}>{$i}</option>";
            }
          ?>
        </select>
        <p style="margin-top: 20px;">Data e ora di fine dell'attività.</p>
        <select id="giornoEnd">
          <?php
            for($i = 1; $i <= 31; $i++) {
              $j = ((int)date('d') == $i) ? 'selected' : '';
              echo "<option value=\"{$i}\" {$j}>{$i}</option>";
            }
          ?>
        </select>
        <select id="meseEnd">
          <?php
            $mesi = ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];

            for($i = 0; $i < 12; $i++) {
              $j = $i + 1;
              $t = ((int)date('m') == $j) ? 'selected' : '';
              echo "<option value=\"{$j}\" {$t}>{$mesi[$i]}</option>";
            }
          ?>
        </select>
        <select id="annoEnd">
          <?php
            for($i = (int)date('Y'); $i > 2010; $i--) {
              $j = ((int)date('Y') == $i) ? 'selected' : '';
              echo "<option value=\"{$i}\" {$j}>{$i}</option>";
            }
          ?>
        </select>
        <select id="oraEnd" style="margin-left: 15px;">
          <?php
            for($i = 0; $i <= 23; $i++) {
              $j = ((int)date('H') == $i) ? 'selected' : '';
              echo "<option value=\"{$i}\" {$j}>{$i}</option>";
            }
          ?>
        </select>
        <select id="minutoEnd">
          <?php
            for($i = 0; $i <= 59; $i++) {
              $j = ((int)date('i') == $i) ? 'selected' : '';
              echo "<option value=\"{$i}\" {$j}>{$i}</option>";
            }
          ?>
        </select>
        <p style="margin-top: 20px;">Valore in FabCoin dell'attività (è un numero intero, lascia il campo vuoto o inserisci 0 per non darle un valore).</p>
        <input type="text" id="fabcoin" placeholder="FabCoin" />
        <div style="margin-top: 20px;">
          <input type="submit" id="salva" value="Salva l'attività" />
        </div>
        <input type="hidden" id="id" value="<?php echo $_GET['id']; ?>" />
      </form>
    </div>
    <?php
      include_once('../../inc/footer.inc.html');
    ?>
  </body>
</html>