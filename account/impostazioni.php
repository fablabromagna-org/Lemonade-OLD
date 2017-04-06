<?php
  require_once('../inc/autenticazione.inc.php');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../inc/header.inc.php');
    ?>
    <script type="text/javascript" src="/js/cambioPwd.js"></script>
    <style type="text/css">
      .box { display: table; border: 1px solid <?php echo TEMA_BG_PRINCIPALE ?>; width: 100%; border-radius: 3px; margin-bottom: 15px; }
      .box > div { display: table-cell; vertical-align: top; padding: 10px; }
      .box > div:first-child { background: <?php echo TEMA_BG_PRINCIPALE ?>; width: 120px; font-size: 20px;  }

      #imgUtente { width: 100px; border-radius: 50%; }

      #cambioPwd { border-bottom: 1px solid #aaa; width: 100%; margin-bottom: 15px; padding-bottom: 15px; }
      #cambioPwd input { display: block; margin-bottom: 5px; }

      #verifica div div { border-bottom: 1px solid #aaa; margin-bottom: 15px; padding-bottom: 15px; }
      #verifica div div:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

      #contenuto > h1 { margin-bottom: 20px; }
    </style>
  </head>
  <body>
    <?php
      include_once('../inc/nav.inc.php');
    ?>
    <div id="contenuto">
      <h1>Impostazioni</h1>
      <div class="box">
        <div>Profilo</div>
        <div>
          <img src="<?php echo $autenticazione -> getPicUrl(); ?>" id="imgUtente" alt />
          <ul>
            <li><b><?php echo $autenticazione -> nome; ?> <?php echo $autenticazione -> cognome; ?></b></li>
            <li><?php echo $autenticazione -> email; ?></li>
          </ul>
          <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #aaa;">
            <p style="margin-bottom: 7px;">Puoi modificare la tua immagine del profilo su <a href="https://it.gravatar.com/">Gravatar</a>.</p>
            <p>Se hai necessità di modificare i tuoi dati personali o l'indirizzo email devi contattare il supporto.</p>
          </div>
          <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #aaa;">
            <p>Categoria utente: <b><?php echo $autenticazione -> categoria[1] ?></b></p>
          </div>
        </div>
      </div>
      <div class="box">
        <div>Password</div>
        <div>
          <form id="cambioPwd">
            <input type="password" id="pwdAttuale" placeholder="Password attuale" />
            <input type="password" id="pwdNuova" placeholder="Nuova password" />
            <input type="password" id="ripeti" placeholder="Ripeti nuova password" />
            <input type="submit" value="Cambia" id="invioForm" />
          </form>
          <p>Se hai dimenticato la tua password, puoi richiederne una nuova effettuando il logout e cliccando su "Ho dimenticato la password".</p>
        </div>
      </div>
    </div>
    <?php
      include_once('../inc/footer.inc.html');
    ?>
  </body>
</html>