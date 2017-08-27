<?php
  require_once('../../inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $chiave = $mysqli -> real_escape_string(isset($_POST['chiave']) ? trim($_POST['chiave']) : '');
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
  else if(!$permessi -> whatCanHeDo($autenticazione -> id)['dizionario']['stato'])

    // L'utente non ha i permessi
    stampaErrore('Non sei autorizzato ad effettuare la modifica!');

  // L'utente ha effettuato l'accesso ed è autorizzato
  else {

    // Controllo che la chiave e il valore siano corretti
    if(!preg_match("/^[a-z\._0-9]{3,50}+$/iu", $chiave) || strlen($valore) > 50)
      stampaErrore('La chiave ed il valore possono contenere solo lettere, numeri, punti, underscore e spazi (min. 3  caratteri, max. 50 caratteri)!');

    // Controllo che la chiave non esista già
    $sql = "SELECT * FROM dizionario WHERE chiave COLLATE utf8mb4_general_ci = '{$chiave}'";
    $query = $mysqli -> query($sql);

    if(!$query) {
      $console -> alert("Impossibile eseguire la query di verifica esistenza della chiave! ".$mysqli -> error, $autenticazione -> id);
      stampaErrore('Impossibile completare la richiesta!');
    }

    if($query -> num_rows != 0)
      stampaErrore('La chiave esiste già!');

    // Creo l'elemento
    $sql = "INSERT INTO dizionario (chiave, valore) VALUES ('{$chiave}', '{$valore}')";
    $query = $mysqli -> query($sql);

    if(!$query) {
      $console -> alert("Impossibile eseguire la query di inserimento dell'elemento! ".$mysqli -> error, $autenticazione -> id);
      stampaErrore('Impossibile completare la richiesta!');
    }

    echo '{}';
    $console -> log("Creato elemento nel dizionario \"{$chiave}\", \"{$valore}\" (ID: {$mysqli -> insert_id }).", $autenticazione -> id);
  }
?>
