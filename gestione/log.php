<?php
  require_once('../inc/autenticazione.inc.php');

  if(!$permessi -> whatCanHeDo($autenticazione -> id)['visualizzareLog']['stato'])
    header('Location: /');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../inc/header.inc.php');
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
    </style>
  </head>
  <body>
    <?php
      include_once('../inc/nav.inc.php');

      $id = $mysqli -> real_escape_string(isset($_GET['id']) ? trim($_GET['id']) : '');

      // Estraggo il profilo dell'utente
      $sql = "SELECT * FROM log WHERE id = '{$id}'";

      if(!$query = $mysqli -> query($sql))
        echo '<div id="contenuto"><h1>Errore!</h1></div>';

      else {

        if($query -> num_rows != 1)
          echo '<div id="contenuto"><h1>Log inesistente!</h1></div>';

        else {

          $log = $query -> fetch_assoc();
      ?>
      <div id="contenuto">
        <h1>Visualizzazione log</h1>
        <p><b>ID log:</b> <?php echo $id; ?></p>
        <p style="margin-top: 10px;"><b>Data:</b> <?php echo date("d/m/Y H:i:s", $log['data']); ?></p>
        <?php
          if($log['livello'] == 'WARN')
            $log['livello'] = '<span style="padding: 3px 5px; border-radius: 3px; color: #fff; margin-top: 3px; display: inline-block; background: #ff9800; font-weight: 700;">WARN</span>';

          else if($log['livello'] == 'ALERT')
            $log['livello'] = '<span style="padding: 3px 5px; border-radius: 3px; color: #fff; margin-top: 3px; display: inline-block; background: #f44336; font-weight: 700;">ALERT</span>';

          $log['messaggio'] = nl2br($log['messaggio']);
        ?>
        <p style="margin-top: 10px;"><b>ID utente:</b> <a href="/gestione/utenti/utente.php?id=<?php echo $log['idUtente'] ?>" style="padding: 3px 5px;" class="button"><?php echo $log['idUtente'] ?></a></p>
        <pstyle="margin-top: 10px;"><b>Livello:</b> <?php echo $log['livello'] ?></p>
        <div style="margin-top: 4px; border: 1px solid #888; border-radius: 3px;">
          <div style="width: calc(100% - 10px); background: #888; padding: 5px;"><p style="color: #fff"><b>Messaggio</b></p></div>
          <div style="width: calc(100% - 10px); padding: 5px;"><?php echo $log['messaggio'] ?></div>
        </div>
        <div style="margin-top: 4px; border: 1px solid #888; border-radius: 3px;">
          <div style="width: calc(100% - 10px); background: #888; padding: 5px;"><p style="color: #fff"><b>Debug</b></p></div>
          <div style="width: calc(100% - 10px); padding: 5px; overflow: auto"><pre><?php echo $log['debug'] ?></pre></div>
        </div>
      <?php
          }
        }
      ?>
    </div>
    <?php
      include_once('../inc/footer.inc.php');
    ?>
  </body>
</html>
