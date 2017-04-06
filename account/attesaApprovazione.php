<?php
  require_once('../inc/mysql.inc.php');
?>
<!DOCTYPE html>
<html>
  <head>
    <?php
      require_once('../inc/header.inc.php');
    ?>
    <link type="text/css" rel="stylesheet" media="screen" href="/css/index.css" />
  </head>
  <body>
    <div id="header">
      <div>
        <p><?php echo NOME_SITO; ?><p>
      </div>
      <img src="/images/logo.png" alt />
    </div>
    <div id="accesso">
      <h2>Sei in attesa dell'approvazione</h2>
      <p style="margin-top: 10px;">Al momento della registrazione hai scelto un account da esterno.</p>
      <p style="margin-top: 10px;">Un membro dello staff dovrà approvare il tuo account.</p>
    </div>
    <?php
      include_once('../inc/footer.inc.html');
    ?>
  </body>
</html>
