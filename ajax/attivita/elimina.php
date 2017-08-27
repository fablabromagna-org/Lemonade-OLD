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
  else if(!$permessi -> whatCanHeDo($autenticazione -> id)['eliminaAttivita']['stato'])

    // L'utente non ha i permessi
    stampaErrore('Non sei autorizzato ad effettuare la modifica!');

  // L'utente ha effettuato l'accesso ed è autorizzato
  else {

    // Controllo l'ID, deve essere un intero
    if(!preg_match("/^[0-9]+$/", $id))
      stampaErrore('Devi inserire un ID utente valido!');

    else {

      // Controllo che l'utente esista
      $sql = "SELECT * FROM attivita WHERE id = '{$id}'";
      $query = $mysqli -> query($sql);

      if(!$query) {
        $console -> alert('Impossibile eseguire la query di ricerca dell\'attività '.$mysqli -> error, $autenticazione -> id);
        stampaErrore("Impossibile eseguire la query di ricerca dell'attività!");
      }

      if($query -> num_rows != 1)
        stampaErrore('Attività inesistente!');

      $attivita = $query -> fetch_assoc();

      if($query -> num_rows != 1)
        stampaErrore('Badge inesistente!');

      // Se è presente una transazione
      // La revoco
      if($attivita['idTransazioneFabCoin'] != null) {
        $sql = "UPDATE transazioniFabCoin SET annullata = TRUE WHERE id = '{$attivita['idTransazioneFabCoin']}'";
        $query = $mysqli -> query($sql);

        if(!$query) {
          $console -> alert('Impossibile annullare la transazione '.$mysqli -> error, $autenticazione -> id);
          stampaErrore("Impossibile annullare la transazione!");
        }
        $console -> warn('Annullata la transazione ID: '.$attivita['idTransazioneFabCoin'].' associata all\'attività ID: '.$attivita['id'], $autenticazione -> id);
      }

      // Elimino l'attività
      $sql = "DELETE FROM attivita WHERE id = '{$attivita['id']}'";
      $query = $mysqli -> query($sql);

      if(!$query) {
        $console -> alert('Impossibile eseguire la query di eliminazione dell\'attività '.$mysqli -> error, $autenticazione -> id);
        stampaErrore("Impossibile eseguire la query di eliminazione dell\'attività!");
      }

      echo '{}';
      $console -> warn('Eliminata attività ID: '.$attivita['id'].' all\'utente '.$attivita['idUtente'], $autenticazione -> id);

      $notificheUtente = new Notifiche($mysqli, $attivita['idUtente']);
      $notificheUtente -> noLink('Ti è stata eliminata un\'attività (ID: '.$attivita['id'].').');
    }
  }
?>
