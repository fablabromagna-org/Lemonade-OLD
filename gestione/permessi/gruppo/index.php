<?php
  require_once('../../../inc/autenticazione.inc.php');

  if(!$permessi -> whatCanHeDo($autenticazione -> id)['visualizzarePermessi']['stato'])
    header('Location: /');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../../../inc/header.inc.php');
    ?>
    <script type="text/javascript" src="/js/permessi/gruppi.js"></script>
    <style type="text/css">
      .permesso { border-bottom: 1px dashed #aaa; padding-bottom: 5px; margin-bottom: 5px; display: table; width: 100%; }
      .permesso > div { display: inline-block; vertical-align: middle; display: table-cell; }
      .permesso > div:last-child { float: right }
      .selectPermesso { margin-left: 15px; }
    </style>
  </head>
  <body>
    <?php
      include_once('../../../inc/nav.inc.php');

    ?>
    <div id="contenuto">
      <h2 style="margin-bottom: 10px;">Permessi del gruppo</h1>
      <?php
        $id = $mysqli -> real_escape_string(isset($_GET['id']) ? trim($_GET['id']) : '');

        if(!preg_match("/^[0-9]+$/", $id))
          echo '<p>Impossibile completare la richiesta.</p>';

        else {

          $permessiGruppo = $permessi -> getGroup((int)$id);

          if($permessiGruppo === false) {
            $console -> alert('Impossibile richiedere i permessi del gruppo. '.$permessi -> mysqli -> error, $autenticazione -> id);
            echo '<p>Impossibile completare la richiesta.</p>';

          } else if(count($permessiGruppo) === 0)
            echo '<p>Non sono presenti i permessi nel sistema.</p>';

          else {
            foreach($permessiGruppo as $key => $value) {
              $default = ($value['default']) ? 'Abilitato' : 'Non abilitato';

              echo '<div class="permesso">';
              echo '<div>';
              echo '<p><b>'.$key.'</b></p>';
              echo '<p>'.$value['descrizione'].'<br /><em>Default: '.$default.'</em></p>';
              echo '</div><div>';
            ?>
              <select class="selectPermesso" data-value="<?php echo $key; ?>" data-id="<?php echo $id; ?>">
                <option value="0" <?php if(!$value['stato']) echo 'selected'; ?>>Non abilitato</option>
                <option value="1" <?php if($value['stato']) echo 'selected'; ?>>Abilitato</option>
              </select>
            <?php
              echo '</div>';
              echo '</div>';
            }
          }
        }
      ?>
    </div>
    <?php
      include_once('../../../inc/footer.inc.php');
    ?>
  </body>
</html>
