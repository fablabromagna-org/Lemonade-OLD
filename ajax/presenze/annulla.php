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

    // Controllo l'ID della notifica
    if(!preg_match("/^[0-9]+$/", $id))
      stampaErrore('Devi inserire un ID valido!');

    else {

      // Controllo che la notifica esista
      $sql = "SELECT * FROM presenze WHERE id = '{$id}' LIMIT 0, 1";
      $query = $mysqli -> query($sql);

      if(!$query) {
        $console -> alert('Impossibile eseguire la query di ricerca della presenza '.$mysqli -> error, $autenticazione -> id);
        stampaErrore("Impossibile completare la richiesta.");
      }

      if($query -> num_rows != 1)
        stampaErrore('Presenza inesistente!');

      $row = $query -> fetch_assoc();

      // Leggo la notifica
      $sql = "UPDATE presenze SET annullata = NOT annullata WHERE id = '{$id}'";
      $query = $mysqli -> query($sql);

      if(!$query) {
        $console -> alert('Impossibile eseguire la query di annullamento. '.$mysqli -> error, $autenticazione -> id);
        stampaErrore("Impossibile completare la richiesta.");
      }

      echo '{}';

      if($row['fine'] === null) {
        $termine = ' e non terminata';
        $durata = '';

      } else {
        $termine = ' e terminata il '.date("d/m/Y H:i:s", $row['fine']);
        $durata = sec2str((int)$row['fine'] - (int)$row['inizio']).', ';
      }

      if($row['annullata'] == false) {
        $console -> warn("Annullata la presenza all\'utente {$row['idUtente']} (ID presenza: {$row['id']})", $autenticazione -> id);

        $notificheUtente = new Notifiche($mysqli, $row['idUtente']);
        $notificheUtente -> link('Annullata la presenza iniziata il '.date("d/m/Y H:i:s", $row['inizio']).$termine.' ('.$durata.'ID: '.$row['id'].').', '/account/presenze.php');

      } else {
        $console -> log("Revocato l'annullamento della presenza all\'utente {$row['idUtente']} (ID presenza: {$row['id']})", $autenticazione -> id);

        $notificheUtente = new Notifiche($mysqli, $row['idUtente']);
        $notificheUtente -> link('Revocato l\'annullamento della presenza iniziata il '.date("d/m/Y H:i:s", $row['inizio']).$termine.' ('.$durata.'ID: '.$row['id'].').', '/account/presenze.php');
      }
    }
  }
?>
