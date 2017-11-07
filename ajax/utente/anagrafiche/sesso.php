<?php
  require_once('../../../inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $sesso = $mysqli -> real_escape_string(isset($_POST['sesso']) ? trim($_POST['sesso']) : '');
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

  if($sesso != '') {

    // Controllo la il codice catastale
    if(!preg_match("/^[0-1]{1}+$/", $sesso))
      stampaErrore('Sesso non valido!');

  } else
    $sesso = "NULL";

  // Aggiorno i dati dell'utente
  $sql = "UPDATE utenti SET sesso = {$sesso} WHERE id = {$id}";
  $query = $mysqli -> query($sql);

  if(!$query) {
    $console -> alert("Impossibile impostare il sesso! ".$mysqli -> error, $autenticazione -> id);
    stampaErrore('Impossibile completare la richiesta!');
  }

  echo '{}';

  switch($sesso) {
    case 'NULL':
      $sesso = 'N/D';
      break;

    case '0':
      $sesso = 'uomo';
      break;

    case '1':
      $sesso = 'donna';
      break;
  }

  $console -> log("Aggiornato sesso di <a href=\"/gestione/utenti/utente.php?id={$id}\">{$id}</a> a {$sesso}.", $autenticazione -> id);
?>
