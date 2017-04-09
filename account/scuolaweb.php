<?php
  require_once('../inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);
  if(!$autenticazione -> isLogged() || $autenticazione -> sospeso == 1) {
    header('Location: /');
    exit();

  } else if($autenticazione -> scuolaweb == 1) {
    header('Location: /dashboard.php');
    exit();

  } else if($autenticazione -> scuolaweb == 2) {
    header('Location: /account/attesaApprovazione.php');
    exit();

  }
?>
<!DOCTYPE html>
<html>
  <head>
    <?php
      require_once('../inc/header.inc.php');
    ?>
    <link type="text/css" rel="stylesheet" media="screen" href="/css/index.css" />
    <script type="text/javascript" src="/js/scuolaweb.js"></script>
  </head>
  <body>
    <div id="header">
      <a href="/logout.php" class="button" id="bottoneFlottante">Esci</a>
      <div>
        <p><?php echo NOME_SITO; ?><p>
      </div>
      <img src="/images/logo.png" alt="Logo" />
    </div>
    <div id="accesso" style="max-width: 520px;">
      <h2>Verifica ScuolaWeb</h2>
      <p style="margin-top: 10px;">Dobbiamo verificare la tua appartenza all'Istituto.<br />Inserisci le tue credenziali ScuolaWeb (il registro elettronico) per procedere con la verifica.</p>
      <p style="margin-top: 10px;"><b>La password non viene conservata!</b><br />Vengono invece conservati (per fini di tracciabilità e controllo) indirizzo IP dell'utente al momento della verifica (viene anche inoltrato al registro elettronico) e il nome associato all'account.</p>
      <p style="margin-top: 10px;">Se ti sei iscritto come studente/genitore devi possedere credenziali da studente/genitore, se ti sei iscritto come docente devi possedere credenziali da docente.</p>
      <p style="margin-top: 10px; font-weight: 700; color: #b71c1c">Ricorda che se sbagli molte volte le credenziali, il tuo account del registro elettronico potrebbe essere sospeso.</p>
      <form id="formAccesso">
        <input type="text" id="utente" autocomplete="off" placeholder="Utente" style="display: block; margin: 30px auto 10px auto;" />
        <input type="password" id="pwd" autocomplete="off" placeholder="Password" style="display: block; margin: 0 auto;" />
        <input type="submit" value="Verifica" id="invioForm" />
      </form>
    </div>
    <?php
      include_once('../inc/footer.inc.html');
    ?>
  </body>
</html>