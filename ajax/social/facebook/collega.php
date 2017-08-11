<?php
  require_once('../../../inc/carica.inc.php');
  require_once('../../../vendor/autoload.php');

  $autenticazione = new Autenticazione($mysqli);

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

  if(!$autenticazione -> isLogged())
    stampaErrore('Non hai effettuato l\'accesso!');

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
  $sql = "SELECT * FROM socialNetworks WHERE tipo = 'facebook' AND idUtente = {$autenticazione -> id} LIMIT 0, 1";
  $query = $mysqli -> query($sql);

  if(!$query) {
    $console -> alert('Impossibile completare la richiesta al database! '.$mysqli -> error, 0);
    stampaErrore('Impossibile completare la richiesta!');
  }

  if($query -> num_rows === 1)
    stampaErrore("Account Facebook già collegato!");

  // Inserisco la sessione nel db ed elimino tutte quelle scadute
  $sql = "INSERT INTO socialNetworks (idSocial, tipo, idUtente) VALUES ('{$id}', 'facebook', '{$autenticazione -> id}'); ";

  if($query = $mysqli -> query($sql)) {
    echo '{}';
    $notificheUtente = new Notifiche($mysqli, $autenticazione -> id);
    $notificheUtente -> link('Collegamento a Facebook riuscito!', '/account/social.php');

  } else {
    $console -> alert('Impossibile completare la richiesta al database! '.$mysqli -> error, 0);
    stampaErrore('Impossibile completare la richiesta!');
  }
?>
