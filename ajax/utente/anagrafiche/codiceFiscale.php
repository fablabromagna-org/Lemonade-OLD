<?php
  require_once('../../../inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $cf = $mysqli -> real_escape_string(isset($_POST['cf']) ? trim($_POST['cf']) : '');
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

  $cf = strtoupper($cf);

  if($cf != '') {

    // Controllo la il codice catastale
    if(!preg_match("/^[A-Z]{6}[0-9]{2}[ABCDEHLMPRST]{1}[0-9]{2}[A-Z]{1}[0-9]{3}[A-Z]{1}+$/", $cf))
      stampaErrore('Codice Fiscale non valido!');

    // Controllo che il comuno o lo stato esista
    $sql = "SELECT * FROM utenti WHERE cf = '{$cf}'";
    $query = $mysqli -> query($sql);

    if(!$query) {
      $console -> alert("Impossibile controllare esistenza CF ".$mysqli -> error, $autenticazione -> id);
      stampaErrore('Impossibile completare la richiesta!');
    }

    if($query -> num_rows != 0)
      stampaErrore('Codice Fiscale già in uso!');

    $cf = "'{$cf}'";
  } else
    $cf = "NULL";

  // Aggiorno i dati dell'utente
  $sql = "UPDATE utenti SET cf = {$cf} WHERE id = {$id}";
  $query = $mysqli -> query($sql);

  if(!$query) {
    $console -> alert("Impossibile impostare CF! ".$mysqli -> error, $autenticazione -> id);
    stampaErrore('Impossibile completare la richiesta!');
  }

  echo '{}';
  $console -> log("Aggiornato CF di <a href=\"/gestione/utenti/utente.php?id={$id}\">{$id}</a> a {$cf}.", $autenticazione -> id);
?>
