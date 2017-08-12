<?php
  require_once('../../inc/carica.inc.php');

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
  else {

    // Leggo la notifica
    $sql = "DELETE FROM notifiche WHERE idUtente = '{$autenticazione -> id}'";
    $query = $mysqli -> query($sql);

    if(!$query) {
      $console -> alert('Impossibile eseguire la query di eliminazione delle notifiche '.$mysqli -> error, $autenticazione -> id);
      stampaErrore("Impossibile completare la richiesta.");
    }

    echo '{}';
  }
?>
