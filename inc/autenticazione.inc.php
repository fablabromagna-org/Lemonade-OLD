<?php
  require_once('carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);

  if(!$autenticazione -> isLogged() || $autenticazione -> sospeso == 1) {
    setCookie(COOKIE_NAME, '', -1);
    header('Location: /');
    exit();
  }

  $notifiche = new Notifiche($mysqli, $autenticazione -> id)
?>