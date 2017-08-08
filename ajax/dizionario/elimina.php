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

    // Controllo che l'ID sia valdio
   if(!preg_match("/^[0-9]{1,11}+$/ iu", $id))
      stampaErrore('ID non valido!');

    // Controllo che la chiave non esista già
    $sql = "SELECT * FROM dizionario WHERE id = {$id}";
    $query = $mysqli -> query($sql);

    if(!$query) {
      $console -> alert("Impossibile eseguire la query di verifica esistenza della chiave! ".$mysqli -> error, $autenticazione -> id);
      stampaErrore('Impossibile completare la richiesta!');
    }

    if($query -> num_rows == 0)
      stampaErrore('L\'elemento non esiste!');

    // Elimino l'elemento
    $sql = "DELETE FROM dizionario WHERE id = {$id}";
    $query = $mysqli -> query($sql);

    if(!$query) {
      $console -> alert("Impossibile eseguire la query eliminazione dell'elemento! ".$mysqli -> error, $autenticazione -> id);
      stampaErrore('Impossibile completare la richiesta!');
    }

    echo '{}';
    $console -> log("Eliminato elemento nel dizionario (ID: {$id}).", $autenticazione -> id);
  }
?>
