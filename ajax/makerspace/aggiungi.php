<?php
  require_once('../../inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $nome = $mysqli -> real_escape_string(isset($_POST['nome']) ? trim($_POST['nome']) : '');

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

    // Controllo che il nome del Maker Space sia valido
    if(!preg_match("/^[\p{L}\. 0-9]{3,50}+$/iu", $nome))
      stampaErrore('Il Maker Space può contenere solo lettere, numeri, punti e spazi (min. 3  caratteri, max. 50 caratteri)!');

    // Controllo che il Maker Space non esista già
    $sql = "SELECT * FROM makerspace WHERE nome COLLATE utf8_general_ci = '{$nome}'";
    $query = $mysqli -> query($sql);

    if(!$query) {
      $console -> alert("Impossibile eseguire la query di verifica esistenza Maker Space! ".$mysqli -> error, $autenticazione -> id);
      stampaErrore('Impossibile completare la richiesta!');
    }

    if($query -> num_rows != 0)
      stampaErrore('Il Maker Space esiste già!');

    // Creo il Maker Space
    $sql = "INSERT INTO makerspace (nome) VALUES ('{$nome}')";
    $query = $mysqli -> query($sql);

    if(!$query) {
      $console -> alert("Impossibile eseguire la query di inserimento del Maker Space! ".$mysqli -> error, $autenticazione -> id);
      stampaErrore('Impossibile completare la richiesta!');
    }

    echo '{}';
    $console -> log("Creato Maker Space \"{$nome}\" (ID: {$mysqli -> insert_id }).", $autenticazione -> id);
  }
?>
