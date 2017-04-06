<?php
  require_once('../class/gestioneAuth.class.php');
  require_once('../inc/mysqli.inc.php');

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $pwd = $mysqli -> real_escape_string(isset($_POST['pwd']) ? trim($_POST['pwd']) : '');
  $pwdAttuale = $mysqli -> real_escape_string(isset($_POST['pwdAttuale']) ? trim($_POST['pwdAttuale']) : '');

  function stampaErrore($errore = 'Errore sconosciuto!') {
    echo '{"errore":true,"msg":"'.$errore.'"}';
    exit();
  }

  // Controllo che l'utente abbia effettuato l'accesso
  if(!$autenticazione -> isLogged())

    // L'utente non ha effettuato l'accesso
    stampaErrore('Non hai effettuato l\'accesso!');

  // L'utente ha effettuato l'accesso
  else {

    // Controllo che la password non sia vuota
    if($pwd == '')
      stampaErrore('Devi inserire una password!');

    // Controllo che la password abbia almeno sei caratteri
    else if(strlen($pwd) < 6)
      stampaErrore('La password deve avere almeno sei caratteri!');

    // La password precedente è diversa da quella attualmente memorizzata
    else if(md5($pwdAttuale) != $autenticazione -> password)
      stampaErrore('La password attuale non è valida!');

    // Aggiorno i dati nel database
    else {

      // Creo la query SQL
      $sql = "UPDATE utenti SET password = '".md5($pwd)."' WHERE id = '".$autenticazione -> id."'";

      // Eseguo la query
      if($query = $mysqli -> query($sql)) {
        echo '{}';

      // Errore nell'esecuzione della query
      } else
        stampaErrore('Errore nell\'esecuzione della query!');
    }
  }
?>