<?php
  require_once('../../../inc/carica.inc.php');

  header('Content-Type: application/json');

  $token = $mysqli -> real_escape_string(isset($_GET['token']) ? trim($_GET['token']) : '');
  $password = $mysqli -> real_escape_string(isset($_POST['password']) ? trim($_POST['password']) : '');
  $email = $mysqli -> real_escape_string(isset($_POST['email']) ? trim($_POST['email']) : '');

  function stampaErrore($errore = 'Login invalid') {
    echo "{\"Reply-Message\": \"{$errore}\"}";
    exit();
  }

  // Controllo i valori
  if(!preg_match("/^[0-9a-f\-]{36}+$/", $token)) {
    http_response_code(401);
    stampaErrore();
  }

  if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(401);
    stampaErrore();
  }

  if(strlen($password) < 6) {
    http_response_code(401);
    stampaErrore();
  }

  // Controllo che il token del server radius sia valido
  $sql = "SELECT totemPresenze.id FROM totemPresenze INNER JOIN makerspace ON makerspace.id = totemPresenze.idMakerSpace WHERE totemPresenze.token = '{$token}' AND makerspace.eliminato = FALSE LIMIT 0, 1";
  $query = $mysqli -> query($sql);

  // Impossibile completare la richiesta
  if(!$query) {
    $console -> alert('Impossibile eseguire la query! '.$mysqli -> error, 0);
    http_response_code(500);
    exit();
  }

  // Il token non esiste
  if($query -> num_rows == 0) {
    http_response_code(401);
    stampaErrore();
  }

  // Controllo se l'utente esiste
  $sql = "SELECT * FROM utenti WHERE email = '{$email}' AND password = '".md5($password)."' AND sospeso IS FALSE LIMIT 0, 1";
  $query = $mysqli -> query($sql);

  if(!$query) {
    $console -> alert('Impossibile eseguire la query! '.$mysqli -> error, 0);
    http_response_code(500);
    exit();
  }

  // Il token non esiste
  if($query -> num_rows == 0) {
    http_response_code(401);
    stampaErrore();
  }

  http_response_code(204);
?>
