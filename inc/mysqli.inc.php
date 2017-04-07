<?php
  // Importo il file di configurazione
  require_once('config.inc.php');

  // Mi connetto al database
  $mysqli = new mysqli(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABASE);
  if($mysqli -> connect_errno)
    exit();

  // Configuro la codifica
  mb_internal_encoding('utf-8');

  // Se l'utente ha richiesto la visualizzazione degli errori PHP
  // Imposto il tutto
  if(PHP_MOSTRA_ERRORI) {
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(-1);
  }

  // Metodo per ricavare l'indirizzo IP
  function getIpAddress() {
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ipAddresses = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
      return trim(end($ipAddresses));

    } else
      return $_SERVER['REMOTE_ADDR'];
  }
?>