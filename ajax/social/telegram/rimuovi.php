<?php
  require_once('../../../inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $id = $mysqli -> real_escape_string(isset($_POST['id']) ? trim($_POST['id']) : '');

  function stampaErrore($errore = 'Errore sconosciuto!') {
    echo '{"errore":true,"msg":"'.$errore.'"}';
    exit();
  }

  // Controllo che l'utente abbia effettuato l'accesso
  if(!$autenticazione -> isLogged())

    // L'utente non ha effettuato l'accesso
    stampaErrore('Non hai effettuato l\'accesso!');

  // L'utente ha effettuato l'accesso ed è autorizzato
  $sql = "DELETE FROM socialNetworks WHERE idUtente = {$autenticazione -> id} AND tipo = 'telegram'";
  $query = $mysqli -> query($sql);

  if(!$query) {
    $console -> alert('Impossibile eliminare il collegamento con Telegram! '.$mysqli -> error, $autenticazione -> id);
    stampaErrore('Impossibile completare la richiesta!');
  }

  $notificheUtente = new Notifiche($mysqli, $autenticazione -> id);
  $notificheUtente -> link('Disconnessione da Telegram riuscita!', '/account/social.php');

  echo '{}';
?>
