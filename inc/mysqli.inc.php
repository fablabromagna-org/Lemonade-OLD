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

  // Metodo per la conversione dei giorni
  function sec2str($secs) {
    $r = '';
    if ($secs >= 86400) {
      $days = floor($secs/86400);
      $secs = $secs%86400;
      $r .= $days . 'd';
      if ($secs > 0) $r .= ' ';
    }
    if ($secs >= 3600) {
      $hours = floor($secs/3600);
      $secs = $secs%3600;
      $r .= $hours . 'h';
      if ($secs > 0) $r .= ' ';
    }
    if ($secs>=60) {
      $minutes = floor($secs/60);
      $secs = $secs%60;
      $r .= $minutes . 'm';
      if ($secs > 0) $r .= ' ';
    }
    return $r . $secs . 's';
  }
?>
