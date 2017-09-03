<?php
  require_once('../../../inc/carica.inc.php');

  header('Content-Type: application/json');

  $token = $mysqli -> real_escape_string(isset($_POST['token']) ? trim($_POST['token']) : '');
  $rfid = $mysqli -> real_escape_string(isset($_POST['rfid']) ? trim($_POST['rfid']) : '');
  $timestamp = $mysqli -> real_escape_string(isset($_POST['timestamp']) ? trim($_POST['timestamp']) : (string)time());

  function stampaErrore($errore = 'Errore sconosciuto!') {
    echo '{"errore":true,"msg":"'.$errore.'"}';
    exit();
  }

  // Controllo i valori
  if(!preg_match("/^[0-9a-f\-]{36}+$/", $token))
    stampaErrore('Token non valido!');

  else if(!preg_match("/^[0-9]{10}+$/", $rfid))
    stampaErrore('RFID non valido!');

  else if(!preg_match("/^[0-9]{10,11}+$/", $timestamp) || (int)$timestamp < time() - 12 * 60 * 60 || (int)$timestamp > time() + 5 * 60)
    stampaErrore('Timestamp non valido!');

  // Controllo la validità del token del totem
  $sql = "SELECT totemPresenze.id FROM totemPresenze INNER JOIN makerspace ON makerspace.id = totemPresenze.idMakerSpace WHERE totemPresenze.token = '{$token}' AND makerspace.eliminato = FALSE LIMIT 0, 1";
  $query = $mysqli -> query($sql);

  // Impossibile completare la richiesta
  if(!$query) {
    $console -> alert('Impossibile eseguire la query! '.$mysqli -> error, 0);
    stampaErrore('Impossibile completare la richiesta!');
  }

  // Il token non esiste
  if($query -> num_rows == 0)
    stampaErrore('Token non valido!');

  $query = $query -> fetch_assoc();
  $totemId = $query['id'];

  // Controllo la validità dell'RFID
  $sql = "SELECT utenti.id AS idUtente, badge.id AS idBadge FROM utenti INNER JOIN badge ON badge.idUtente = utenti.id WHERE badge.rfid = '{$rfid}' AND badge.revocato = FALSE AND utenti.sospeso = FALSE LIMIT 0, 1";
  $query = $mysqli -> query($sql);

  // Impossibile completare la richiesta
  if(!$query) {
    $console -> alert('Impossibile eseguire la query! '.$mysqli -> error, 0);
    stampaErrore('Impossibile completare la richiesta!');
  }

  // L'RFID non esiste o l'utente è sospeso
  if($query -> num_rows == 0)
    stampaErrore('RFID non valido!');

  $query = $query -> fetch_assoc();
  $userId = $query['idUtente'];
  $badgeId = $query['idBadge'];

  // Controllo se l'utente ha delle presenze da chiudere
  $sql = "SELECT id FROM presenze WHERE idUtente = {$userId} AND fine IS NULL AND annullata = FALSE ORDER BY id DESC LIMIT 0, 1";
  $query = $mysqli -> query($sql);

  // Impossibile completare la richiesta
  if(!$query) {
    $console -> alert('Impossibile eseguire la query! '.$mysqli -> error, 0);
    stampaErrore('Impossibile completare la richiesta!');
  }

  // L'utente non ha presenze pendenti
  if($query -> num_rows == 0) {

    $sql = "INSERT INTO presenze (idUtente, idTotemInizio, inizio, rfidInizio) VALUES ({$userId}, {$totemId}, {$timestamp}, {$badgeId})";
    $query = $mysqli -> query($sql);

    if(!$query) {
      $console -> alert('Impossibile eseguire la query! '.$mysqli -> error, 0);
      stampaErrore('Impossibile completare la richiesta!');
    }

    echo '{}';

  // L'utente ha una presenza da chiudere
  } else {

    $query = $query -> fetch_assoc();
    $idPresenza = $query['id'];

    $sql = "UPDATE presenze SET idTotemFine = {$totemId}, fine = {$timestamp}, rfidFine = {$badgeId} WHERE id = {$idPresenza}";
    $query = $mysqli -> query($sql);

    if(!$query) {
      $console -> alert('Impossibile eseguire la query! '.$mysqli -> error, 0);
      stampaErrore('Impossibile completare la richiesta!');
    }

    echo '{}';
  }
?>
