<?php
  require_once('../../../inc/carica.inc.php');
  require_once('../../../vendor/autoload.php');

  header('Content-Type: application/json');

  // Raccolgo tutti i dati e li "pulisco"
  $token = $mysqli -> real_escape_string(isset($_POST['token']) ? trim($_POST['token']) : '');

  function stampaErrore($errore = 'Errore sconosciuto!') {
    echo '{"errore":true,"msg":"'.$errore.'"}';
    exit();
  }

  // Controllo che gli accessi non siano stati bloccati
  if($dizionario -> getValue('bloccoAccessi') === 'true')
    stampaErrore('Gli accessi sono stati bloccati!');

  if($dizionario -> getValue('facebookAppId') === false || $dizionario -> getValue('facebookAppId') === null)
    stampaErrore('L\'accesso con Facebook è disabilitato!');

  $fb = new Facebook\Facebook([
    'app_id' => $dizionario -> getValue('facebookAppId'),
    'app_secret' => $dizionario -> getValue('facebookAppSecret'),
    'default_graph_version' => 'v2.10',
  ]);

  try {
    $response = $fb -> get('/me?fields=id', $token);

  } catch(Facebook\Exceptions\FacebookResponseException $e) {
    $console -> alert('Errore durante l\'accesso con Facebook! '.$e, 0);
    stampaErrore('Impossibile completare la richiesta!');

  } catch(Facebook\Exceptions\FacebookSDKException $e) {
    $console -> alert('Errore durante l\'accesso con Facebook! '.$e, 0);
    stampaErrore('Impossibile completare la richiesta!');
  }

  $id = $response -> getGraphUser()['id'];

  // Controllo con il database
  $sql = "SELECT * FROM socialNetworks WHERE tipo = 'facebook' AND idSocial = '{$id}' AND authCode IS NULL LIMIT 0, 1";
  $query = $mysqli -> query($sql);

  if(!$query) {
    $console -> alert('Impossibile completare la richiesta al database! '.$mysqli -> error, 0);
    stampaErrore('Impossibile completare la richiesta!');
  }

  if($query -> num_rows === 0)
    stampaErrore("Account Facebook non collegato a nessun profilo!");

  $idUtente = $query -> fetch_assoc()['idUtente'];
  $hashSessione = md5(time());
  $scadenza = time() + 30 * 24 * 60 * 60;
  $ip = getIpAddress();
  $userAgent = $mysqli -> real_escape_string(isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '');

  // Inserisco la sessione nel db ed elimino tutte quelle scadute
  $sql = "INSERT INTO sessioni (ipInizio, hashSessione, idUtente, userAgent, scadenza, social) VALUES ('$ip', '$hashSessione', '".$idUtente."', '$userAgent', '$scadenza', 'facebook'); ";
  $sql .= "DELETE FROM sessioni WHERE scadenza < '".time()."'";

  if($query = $mysqli -> multi_query($sql)) {
    setcookie(COOKIE_NAME, $idUtente."-".$hashSessione, $scadenza, "/");
    echo '{}';

  } else {
    $console -> alert('Impossibile completare la richiesta al database! '.$mysqli -> error, 0);
    stampaErrore('Impossibile completare la richiesta!');
  }
?>
