<?php
  require_once('../../../inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $belfiore = $mysqli -> real_escape_string(isset($_POST['luogo']) ? trim($_POST['luogo']) : '');
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

  // Controllo l'ID dell'utente
  if(!preg_match("/^[0-9]{1,11}+$/iu", $id))
    stampaErrore('ID utente non valido!');

  if($belfiore != '') {

    // Controllo la il codice catastale
    if(!preg_match("/^[A-Z]{1}[0-9]{3}+$/", $belfiore))
      stampaErrore('Codice catastale non valido!');

    // Controllo che il comuno o lo stato esista
    $sql = "SELECT * FROM comuni WHERE codiceCatastale = '{$belfiore}'";
    $query = $mysqli -> query($sql);

    if(!$query) {
      $console -> alert("Impossibile controllare esistenza comune nascita! ".$mysqli -> error, $autenticazione -> id);
      stampaErrore('Impossibile completare la richiesta!');
    }

    if($query -> num_rows != 1)
      stampaErrore('Comune o stato inesistente!');

    $belfiore = "'{$belfiore}'";
  } else
    $belfiore = "NULL";

  // Aggiorno i dati dell'utente
  $sql = "UPDATE utenti SET luogoNascita = {$belfiore} WHERE id = {$id}";
  $query = $mysqli -> query($sql);

  if(!$query) {
    $console -> alert("Impossibile impostare comune nascita! ".$mysqli -> error, $autenticazione -> id);
    stampaErrore('Impossibile completare la richiesta!');
  }

  echo '{}';
    $console -> log("Aggiornato luogo di nascita di <a href=\"/gestione/utenti/utente.php?id={$id}\">{$id}</a> a {$belfiore}.", $autenticazione -> id);
?>
