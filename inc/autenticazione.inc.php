<?php
  require_once(__DIR__.'/../class/caricaClassi.inc.php');
  require_once(__DIR__.'/mysqli.inc.php');

  $autenticazione = new Autenticazione($mysqli);

  if(!$autenticazione -> isLogged() || $autenticazione -> sospeso == 1) {
    setCookie(COOKIE_NAME, '', -1);
    header('Location: /');
    exit();

  }
?>