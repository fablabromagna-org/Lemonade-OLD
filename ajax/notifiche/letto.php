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

    // Controllo l'ID della notifica
    if(!preg_match("/^[0-9]+$/", $id))
      stampaErrore('Devi inserire un ID utente valido!');

    else {

      // Controllo che la notifica esista
      $sql = "SELECT * FROM notifiche WHERE id = '{$id}' AND idUtente = '{$autenticazione -> id}'";
      $query = $mysqli -> query($sql);

      if(!$query) {
        $console -> alert('Impossibile eseguire la query di ricerca della notifica '.$mysqli -> error, $autenticazione -> id);
        stampaErrore("Impossibile eseguire la query di ricerca della notifica!");
      }

      if($query -> num_rows != 1)
        stampaErrore('Notifica inesistente!');

      // Leggo la notifica
      $sql = "UPDATE notifiche SET letto = TRUE WHERE id = '{$id}'";
      $query = $mysqli -> query($sql);

      if(!$query) {
        $console -> alert('Impossibile eseguire la query di lettura della notifica '.$mysqli -> error, $autenticazione -> id);
        stampaErrore("Impossibile eseguire la query di lettura della notifica!");
      }

      echo '{}';
    }
  }
?>