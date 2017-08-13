<?php
  require_once('inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);
  if($autenticazione -> isLogged())
    header('Location: /dashboard.php');
?>
<!DOCTYPE html>
<html>
  <head>
    <?php
      require_once('./inc/header.inc.php');
    ?>
    <link type="text/css" rel="stylesheet" media="screen" href="css/index.css" />
    <script type="text/javascript" src="js/accesso.js"></script>
  </head>
  <body>
    <?php
      if($dizionario -> getValue('facebookAppId') !== false && $dizionario -> getValue('facebookAppId') !== null) {
    ?>
    <!-- Facebook -->
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/it_IT/sdk.js#xfbml=1&version=v2.10&appId=<?php echo $dizionario -> getValue('facebookAppId') ?>";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
    <?php
      }
    ?>
    <div id="header">
      <a href="/" class="button" id="bottoneFlottante">Registrazione</a>
      <div>
        <p><?php echo NOME_SITO; ?><p>
      </div>
      <img src="images/logo.png" alt="Logo" />
    </div>
    <div id="accesso">
      <h2>Accesso</h2>
      <?php
        if($dizionario -> getValue('bloccoAccessi') !== 'true') {
      ?>
      <form id="formAccesso">
        <input type="text" id="email" autocomplete="off" placeholder="E-Mail" />
        <input type="password" id="pwd" autocomplete="off" placeholder="Password" />
        <a style="display: block;" href="/account/recupero.php">Ho dimenticato la password</a>
        <input type="submit" value="Accedi" id="invioForm" />
      </form>
      <?php
          if($dizionario -> getValue('facebookAppId') !== false && $dizionario -> getValue('facebookAppId') !== null) {
      ?>
        <!-- Facebook -->
        <a id="fbLogin" class="noselect"><i class="fa fa-facebook-official" aria-hidden="true"></i>Accedi con Facebook</a>
      <?php
          }

        } else {
      ?>
      <h3 style="margin-top: 20px;">Gli accessi sono temporaneamente bloccati.</h3>
      <?php
        }
      ?>
    </div>
    <?php
      include_once('inc/footer.inc.php');
    ?>
  </body>
</html>
