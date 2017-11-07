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

  // Controllo che l'utente abbia i permessi per effettuare la modifica
  else if(!$permessi -> whatCanHeDo($autenticazione -> id)['modificaAnagrafiche']['stato'])

    // L'utente non ha i permessi
    stampaErrore('Non sei autorizzato ad effettuare la modifica!');

  // L'utente ha effettuato l'accesso ed è autorizzato
  else {

    // Controllo l'ID dell'utente
    if(!preg_match("/^[0-9]{1,11}+$/iu", $id))
      stampaErrore('ID utente non valido!');

    // Imposto la data di nascita, se l'utente non esiste non succede nulla e risparmio CPU :-D
    $sql = "UPDATE utenti SET dataNascita = NULL WHERE id = {$id}";
    $query = $mysqli -> query($sql);

    if($query) {
      $console -> log("Rimossa data di nascita di <a href=\"/gestione/utenti/utente.php?id={$id}\">{$id}</a>.", $autenticazione -> id);
      echo '{}';

    } else {
      $console -> alert("Impossibile aggiornare la data di nascita! ".$mysqli -> error, $autenticazione -> id);
      stampaErrore('Impossibile completare la richiesta.');
    }
  }
?>
