<?php
  require_once('../../inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $id = $mysqli -> real_escape_string(isset($_POST['id']) ? trim($_POST['id']) : '');
  $rfid = $mysqli -> real_escape_string(isset($_POST['rfid']) ? trim($_POST['rfid']) : '');

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

    // Controllo l'RFID, deve essere un intero di 10 cifre
    if(!preg_match("/^[0-9]{10}$/", $rfid))
      stampaErrore('Devi inserire un RFID valido!');

    // Controllo l'ID, deve essere un intero
    else if(!preg_match("/^[0-9]+$/", $id))
      stampaErrore('Devi inserire un ID utente valido!');

    else {

      // Controllo che l'utente esista
      $sql = "SELECT id FROM utenti WHERE id = '{$id}'";
      $query = $mysqli -> query($sql);

      if(!$query) {
        $console -> alert('Impossibile eseguire la query di ricerca dell\'utente '.$mysqli -> error, $autenticazione -> id);
        stampaErrore("Impossibile eseguire la query di ricerca dell'utente!");
      }

      if($query -> num_rows != 1)
        stampaErrore('Utente inesistente!');

      // Controllo che il badge non sia già in uso
      $sql = "SELECT * FROM badge WHERE rfid = '{$rfid}' AND revocato = FALSE";
      $query = $mysqli -> query($sql);

      if(!$query) {
        $console -> alert('Impossibile eseguire la query di ricerca del badge '.$mysqli -> error, $autenticazione -> id);
        stampaErrore("Impossibile eseguire la query di ricerca del badge!");
      }

      if($query -> num_rows != 0)
        stampaErrore('Badge in uso!');

      // Inserisco il badge
      $sql = "INSERT INTO badge (idUtenteRilascio, dataRilascio, idUtente, rfid) VALUES ('".$autenticazione -> id."', '".time()."', '{$id}', '{$rfid}')";
      $query = $mysqli -> query($sql);

      if(!$query) {
        $console -> alert('Impossibile eseguire la query di inserimento del badge '.$mysqli -> error, $autenticazione -> id);
        stampaErrore("Impossibile memorizzare il badge!");
      }

      echo '{}';
      $console -> log('Aggiunto badge all\'utente '.$id.' (RFID: '.$rfid.')', $autenticazione -> id);
    }
  }
?>