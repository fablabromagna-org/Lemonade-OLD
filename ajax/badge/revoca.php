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

  // Controllo che l'utente abbia i permessi per effettuare la modifica
  else if($autenticazione -> gestionePortale != 1)

    // L'utente non ha i permessi
    stampaErrore('Non sei autorizzato ad effettuare la modifica!');

  // L'utente ha effettuato l'accesso ed è autorizzato
  else {

    // Controllo l'ID, deve essere un intero
    if(!preg_match("/^[0-9]+$/", $id))
      stampaErrore('Devi inserire un ID utente valido!');

    else {

      // Controllo che l'utente esista
      $sql = "SELECT rfid FROM badge WHERE id = '{$id}' AND revocato = FALSE";
      $query = $mysqli -> query($sql);

      if(!$query) {
        $console -> alert('Impossibile eseguire la query di ricerca del badge '.$mysqli -> error, $autenticazione -> id);
        stampaErrore("Impossibile eseguire la query di ricerca del badge!");
      }

      $rfid = $query -> fetch_assoc();
      $rfid = $rfid['rfid'];

      if($query -> num_rows != 1)
        stampaErrore('Badge inesistente!');

      // Revoco il badge
      $sql = "UPDATE badge SET revocato = TRUE WHERE id = '{$id}'";
      $query = $mysqli -> query($sql);

      if(!$query) {
        $console -> alert('Impossibile eseguire la query di inserimento del badge '.$mysqli -> error, $autenticazione -> id);
        stampaErrore("Impossibile memorizzare il badge!");
      }

      echo '{}';
      $console -> warn('Revocato badge all\'utente '.$id.' (RFID: '.$rfid.')', $autenticazione -> id);
    }
  }
?>