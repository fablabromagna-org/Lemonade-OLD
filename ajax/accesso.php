<?php
  require_once('../inc/carica.inc.php');

  header('Content-Type: application/json');

  // Raccolgo tutti i dati e li "pulisco"
  $pwd = $mysqli -> real_escape_string(isset($_POST['pwd']) ? trim($_POST['pwd']) : '');
  $email = $mysqli -> real_escape_string(isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '');
  $userAgent = $mysqli -> real_escape_string(isset($_SERVER['HTTP_USER_AGENT']) ? trim($_SERVER['HTTP_USER_AGENT']) : '');

  function stampaErrore($errore = 'Errore sconosciuto!') {
    echo '{"errore":true,"msg":"'.$errore.'"}';
    exit();
  }

  // Controllo che gli accessi non siano stati bloccati
  if($dizionario -> getValue('bloccoAccessi') === 'true')
    stampaErrore('Gli accessi sono stati bloccati!');

  // Controllo i dati
  if($email === "")
    stampaErrore('L\'indirizzo email è obbligatorio!');

  else if(!filter_var($email, FILTER_VALIDATE_EMAIL))
    stampaErrore('L\'indirizzo inserito non è valido!');

  else if($pwd === "")
    stampaErrore('Devi inserire una password!');

  else if(strlen($pwd) < 6)
    stampaErrore('La password deve contenere almeno sei caratteri!');

  // Confronto i dati col database
  else {

    // Creo l'hash della password
    $pwd = md5($pwd);

    // Creo la query
    $sql = "SELECT id, sospeso, nome, cognome FROM utenti WHERE codiceAttivazione = '0' AND email = '".$email."' AND password = '".$pwd."'";

    // Eseguo la query
    if($query = $mysqli -> query($sql)) {

      // Account esistente
      if($query -> num_rows === 1) {

        $dati = $query -> fetch_array();

        // Controllo se l'account è stato sospeso
        if($dati['sospeso'] == true)
          stampaErrore('Il tuo account è stato sospeso!');

        // L'account è attivo, genero la sessione
        else {

          $hashSessione = md5(time());
          $scadenza = time() + 30 * 24 * 60 * 60;
          $ip = getIpAddress();

          // Inserisco la sessione nel db ed elimino tutte quelle scadute
          $sql = "INSERT INTO sessioni (ipInizio, hashSessione, idUtente, userAgent, scadenza) VALUES ('$ip', '$hashSessione', '".$dati['id']."', '$userAgent', '$scadenza'); ";
          $sql .= "DELETE FROM sessioni WHERE scadenza < '".time()."'";

          if($query = $mysqli -> multi_query($sql)) {

            setcookie(COOKIE_NAME, $dati['id']."-".$hashSessione, $scadenza, "/");
            echo '{}';

          } else
            stampaErrore('Impossibile contattare il database! '.$mysqli -> error);
        }

      // Account inesistente o dati di accesso errati
      } else
        stampaErrore('L\'indirizzo email e/o la password inseriti sono errati!');

    } else
      stampaErrore('Impossibile contattare il database!');
  }
?>
