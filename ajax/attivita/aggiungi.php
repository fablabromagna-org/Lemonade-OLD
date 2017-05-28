<?php
  require_once('../../inc/carica.inc.php');
  require_once('../../vendor/autoload.php');

  // Configuro Mailgun
  use Mailgun\Mailgun;
  $dominio = DOMINIO_EMAIL_MAILGUN;
  $mailgun = new Mailgun(MAILGUN_API_KEY);

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $descrizione = $mysqli -> real_escape_string(isset($_POST['descrizione']) ? trim($_POST['descrizione']) : '');
  $annoInizio = $mysqli -> real_escape_string(isset($_POST['annoInizio']) ? trim($_POST['annoInizio']) : '');
  $meseInizio = $mysqli -> real_escape_string(isset($_POST['meseInizio']) ? trim($_POST['meseInizio']) : '');
  $giornoInizio = $mysqli -> real_escape_string(isset($_POST['giornoInizio']) ? trim($_POST['giornoInizio']) : '');
  $oraInizio = $mysqli -> real_escape_string(isset($_POST['oraInizio']) ? trim($_POST['oraInizio']) : '');
  $minutoInizio = $mysqli -> real_escape_string(isset($_POST['minutoInizio']) ? trim($_POST['minutoInizio']) : '');
  $annoEnd = $mysqli -> real_escape_string(isset($_POST['annoFine']) ? trim($_POST['annoFine']) : '');
  $meseEnd = $mysqli -> real_escape_string(isset($_POST['meseFine']) ? trim($_POST['meseFine']) : '');
  $giornoEnd = $mysqli -> real_escape_string(isset($_POST['giornoFine']) ? trim($_POST['giornoFine']) : '');
  $oraEnd = $mysqli -> real_escape_string(isset($_POST['oraFine']) ? trim($_POST['oraFine']) : '');
  $minutoEnd = $mysqli -> real_escape_string(isset($_POST['minutoFine']) ? trim($_POST['minutoFine']) : '');
  $id = $mysqli -> real_escape_string(isset($_POST['id']) ? trim($_POST['id']) : '');
  $fabcoin = $mysqli -> real_escape_string(isset($_POST['fabcoin']) ? trim($_POST['fabcoin']) : '');

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

    if($fabcoin == '')
      $fabcoin = '0';

    if(strip_tags($descrizione) == '')
      stampaErrore('Devi inserire una descrizione!');

    else if(!preg_match("/^[0-9]+$/i", $giornoInizio) || !preg_match("/^[0-9]+$/i", $meseInizio) || !preg_match("/^[0-9]+$/i", $annoInizio) || !preg_match("/^[0-9]+$/i", $oraInizio) || !preg_match("/^[0-9]+$/i", $minutoInizio))
      stampaErrore('Devi inserire una data di inizio valida!');

    else if(!preg_match("/^[0-9]+$/i", $giornoEnd) || !preg_match("/^[0-9]+$/i", $meseEnd) || !preg_match("/^[0-9]+$/i", $annoEnd) || !preg_match("/^[0-9]+$/i", $oraEnd) || !preg_match("/^[0-9]+$/i", $minutoEnd))
      stampaErrore('Devi inserire una data di fine valida!');

    else if(!checkdate((int)$meseInizio, (int)$giornoInizio, (int)$annoInizio))
      stampaErrore('Devi inserire una data di inizio valida!');

    else if(!checkdate((int)$meseEnd, (int)$giornoEnd, (int)$annoEnd))
      stampaErrore('Devi inserire una data di fine valida!');

    else if(!preg_match("/^[0-9]+$/i", $id))
      stampaErrore('L\'ID deve essere un numero intero!');

    else if(!preg_match("/^[0-9]+$/i", $fabcoin))
      stampaErrore('Devi inserire un numero di fabcoin valido!');

    else if((int)$oraInizio < 0 || (int)$oraInizio > 23)
      stampaErrore('Devi inserire un\'ora di inizio valida!');

    else if((int)$minutoInizio < 0 || (int)$minutoInizio > 59)
      stampaErrore('Devi inserire un minuto di inizio valido!');

    else if((int)$oraEnd < 0 || (int)$oraEnd > 23)
      stampaErrore('Devi inserire un\'ora di fine valida!');

    else if((int)$minutoEnd < 0 || (int)$minutoEnd > 59)
      stampaErrore('Devi inserire un minuto di fine valido!');

    else {
      $idTransazione = 'NULL';
      $idAttivita = null;

      $descrizione = strip_tags($descrizione, '<strong><em><p><br><u><span><h1><h2><h3><ol><ul><li><a>');

      // Se i FabCoin sono più di 0 creo una transazione
      if($fabcoin != 0) {
        $sql = "INSERT INTO transazioniFabCoin (idUtente, dataInserimento, annullabile, modificabile) VALUES ('{$id}', '".time()."', 'FALSE', 'FALSE')";

        if($mysqli -> query($sql)) {
          $idTransazione = $mysqli -> insert_id;
          $console -> log('Creata transazione ('.$idTransazione.') di F'.$fabcoin.' all\'utente '.$id.' per un\'attività.', $autenticazione -> id);

        } else {
          $console -> alert('Impossibile eseguire la query di aggiunta della transazione! '.$mysqli -> error, $autenticazione -> id);
          stampaErrore('Impossibile eseguire la query di aggiunta della transazione!');
        }
      } else
        $fabcoin = 'NULL';

      $inizio = strtotime("{$annoInizio}-{$meseInizio}-{$giornoInizio} {$oraInizio}:{$minutoInizio}");
      $fine = strtotime("{$annoEnd}-{$meseEnd}-{$giornoEnd} {$oraEnd}:{$minutoEnd}");

      // Aggiungo l'attività
      $sql = "INSERT INTO attivita (idUtente, descrizione, aggiuntoDa, aggiuntoIl, inizio, fine, idTransazioneFabCoin, fabcoin) VALUES ('{$id}', '{$descrizione}', '".$autenticazione -> id."', '".time()."', '{$inizio}', '{$fine}', '{$idTransazione}', '{$fabcoin}')";

      // Tutto ok, aggiorno la transazione se necessario
      if($mysqli -> query($sql)) {

        if($idTransazione == 'NULL') {
          echo '{}';
          exit();
        }

        $sql = "UPDATE transazioniFabCoin SET descrizione = 'Attività riconosciuta da FabLab Romagna (ID: ".$mysqli -> insert_id.").' WHERE id = '{$idTransazione}'";

        if($mysqli -> query($sql)) {
          echo '{}';
          $console -> log('Aggiornata transazione ('.$idTransazione.'), aggiunta descrizione.', $autenticazione -> id);

        } else {
          $console -> alert('Impossibile eseguire la query di aggiornamento della descrizione della transazione! '.$mysqli -> error, $autenticazione -> id);
          stampaErrore('Impossibile eseguire la query di aggiornamento della descrizione della transazione!');
        }

      // Errore
      } else {
        $console -> alert('Impossibile aggiungere l\'attività! '.$mysqli -> error, $autenticazione -> id);
        stampaErrore('Impossibile aggiungere l\'attività!');
      }
    }
  }
?>