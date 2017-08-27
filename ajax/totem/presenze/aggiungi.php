<?php
  require_once('../../../inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $nome = $mysqli -> real_escape_string(isset($_POST['nome']) ? trim($_POST['nome']) : '');
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
  else if(!$permessi -> whatCanHeDo($autenticazione -> id)['gestireTotem']['stato'])

    // L'utente non ha i permessi
    stampaErrore('Non sei autorizzato ad effettuare la modifica!');

  // L'utente ha effettuato l'accesso ed è autorizzato
  else {

    // Controllo che il nome del totem sia valido
    if(!preg_match("/^[\p{L}\. 0-9]{3,20}+$/iu", $nome))
      stampaErrore('Il nome del totem può contenere solo lettere, numeri, punti e spazi (min. 3  caratteri, max. 20 caratteri)!');

    // Controllo l'ID del Maker Space
    if(!preg_match("/^[0-9]+$/", $id))
      stampaErrore('ID del Maker Space non valido!');

    // Controllo che il Maker Space non esista già
    $sql = "SELECT * FROM totemPresenze WHERE nome COLLATE utf8mb4_general_ci = '{$nome}' AND idMakerSpace = '{$id}'";
    $query = $mysqli -> query($sql);

    if(!$query) {
      $console -> alert("Impossibile eseguire la query di verifica esistenza totem! ".$mysqli -> error, $autenticazione -> id);
      stampaErrore('Impossibile completare la richiesta!');
    }

    if($query -> num_rows != 0)
      stampaErrore('Il totem esiste già!');

    // Creo il Maker Space
    $sql = "INSERT INTO totemPresenze (nome, token, idMakerSpace) VALUES ('{$nome}', UUID(), '{$id}')";
    $query = $mysqli -> query($sql);

    if(!$query) {
      $console -> alert("Impossibile eseguire la query di inserimento del totem! ".$mysqli -> error, $autenticazione -> id);
      stampaErrore('Impossibile completare la richiesta!');
    }

    echo '{}';
    $console -> log("Creato totem \"{$nome}\" (ID: {$mysqli -> insert_id }).", $autenticazione -> id);
  }
?>
