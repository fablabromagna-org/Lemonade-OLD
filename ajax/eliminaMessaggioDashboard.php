<?php
  require_once('../inc/carica.inc.php');
  require_once('../vendor/autoload.php');

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

  // Controllo che l'utente abbia i permessi per effettuare la modifica
  else if($autenticazione -> gestionePortale!= 1)

    // L'utente non ha i permessi
    stampaErrore('Non sei autorizzato ad effettuare la modifica!');

  // L'utente ha effettuato l'accesso ed è autorizzato
  else {

    // Controllo l'ID sia diverso da vuoto
    if($id == '')
      stampaErrore('Devi inserire un ID valido!');

    // Aggiorno i dati nel database
    else {

      // Estraggo i dati relativi all'utente
      $sql = "DELETE FROM dashboard WHERE id = '{$id}'";

      if($query = $mysqli -> query($sql)) {
        $console -> warn("Rimosso messaggio sulla dashboard.", $autenticazione -> id);
        echo '{}';

      } else {
        $console -> warn("Impossibile inviare la richiesta al database! ".$mysqli -> error, $autenticazione -> id);
        stampaErrore('Impossibile completare la richiesta!');
      }
    }
  }
?>
