<?php
  require_once('../inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $sorgente = isset($_POST['sorgente']) ? trim($_POST['sorgente']) : '';
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

  // Controllo che l'ID sia valido
  if(!preg_match("/^[0-9]+$/i", $id))
    stampaErrore('ID non valido!');

  $sorgente = $mysqli -> real_escape_string(base64_encode(strip_tags($sorgente, '<strong><em><p><br><u><span><h1><h2><h3><ol><ul><li><a>')));

  $sql = "UPDATE templates SET sorgente = '{$sorgente}' WHERE id = {$id}";
  $query = $mysqli -> query($sql);

  if(!$query) {
    $console -> alert('Impossibile eseguire la query! '.$mysqli -> error, $autenticazione -> id);
    stampaErrore('Impossibile completare la richiesta!');
  }

  echo '{}';
  $console -> warn('Modificato template ID: '.$id, $autenticazione -> id);
?>
