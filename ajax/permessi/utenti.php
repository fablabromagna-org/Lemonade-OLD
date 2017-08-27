<?php
  require_once('../../inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $id = $mysqli -> real_escape_string(isset($_POST['gruppo']) ? trim($_POST['gruppo']) : '');
  $permesso = $mysqli -> real_escape_string(isset($_POST['permesso']) ? trim($_POST['permesso']) : '');
  $valore = $mysqli -> real_escape_string(isset($_POST['valore']) ? trim($_POST['valore']) : '');

  function stampaErrore($errore = 'Errore sconosciuto!') {
    echo '{"errore":true,"msg":"'.$errore.'"}';
    exit();
  }

  // Controllo che l'utente abbia effettuato l'accesso
  if(!$autenticazione -> isLogged())

    // L'utente non ha effettuato l'accesso
    stampaErrore('Non hai effettuato l\'accesso!');

  // Controllo che l'utente abbia i permessi per effettuare la modifica
  if(!$permessi -> whatCanHeDo($autenticazione -> id)['modificarePermessi']['stato'])

    // L'utente non ha i permessi
    stampaErrore('Non sei autorizzato ad effettuare la modifica!');

  // L'utente ha effettuato l'accesso ed è autorizzato
  // Controllo i valori
  if(!preg_match("/^[0-9]{1,11}+$/", $id))
    stampaErrore('Devi inserire un ID valido!');

  // Controllo il valore, deve essere un intero
  if(!preg_match("/^[0-2]{1}+$/", $valore))
    stampaErrore('Devi inserire un valore valido!');

  if(!preg_match("/^[a-z]+$/i", $permesso))
    stampaErrore('Devi inserire un permesso valido!');

  $sql = "SELECT * FROM elencoPermessi WHERE nome = '{$permesso}'";
  $query = $mysqli -> query($sql);

  if(!$query) {
    $console -> alert('Impossibile controllare l\'esistenza del permesso. '.$mysqli -> error, $autenticazione -> id);
    stampaErrore('Impossibile completare la richiesta.');
  }

  if($query -> num_rows !== 1)
    stampaErrore('Permesso non esistente!');

  $sql = "SELECT * FROM utenti WHERE id = {$id}";
  $query = $mysqli -> query($sql);

  if(!$query) {
    $console -> alert('Impossibile controllare l\'esistenza dell\'utente. '.$mysqli -> error, $autenticazione -> id);
    stampaErrore('Impossibile completare la richiesta.');
  }

  if($query -> num_rows !== 1)
    stampaErrore('Utente non esistente!');

  if($valore == 2)
    $valore = 'NULL';

  // Elimino un eventuale risultato (nel caso fosse già presente il permesso)
  $sql = "DELETE FROM permessi WHERE permesso = '{$permesso}' AND gruppo IS FALSE AND idGruppoUtente = {$id};";
  $sql .= "INSERT INTO permessi (gruppo, permesso, stato, idGruppoUtente) VALUES (FALSE, '{$permesso}', {$valore}, {$id})";
  $query = $mysqli -> multi_query($sql);

  if(!$query) {
    $console -> alert('Impossibile controllare inserire il permesso nel database. '.$mysqli -> error, $autenticazione -> id);
    stampaErrore('Impossibile completare la richiesta.');
  }

  while($mysqli -> more_results()) {
    $mysqli -> next_result();
    $mysqli -> use_result();
  }

  $console -> warn('Aggiornato permesso all\'utente '.$id.': '.$permesso.'='.$valore, $autenticazione -> id);

  echo '{}';
?>
