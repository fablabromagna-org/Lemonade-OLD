<?php
  require_once('../../inc/carica.inc.php');

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
  else if(!$permessi -> whatCanHeDo($autenticazione -> id)['modificareAttivita']['stato'])

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

      // Ricavo i dati originali
      $sql = "SELECT * FROM attivita WHERE id = '{$id}'";
      $query = $mysqli -> query($sql);

      if(!$query) {
        $console -> alert('Impossibile estrarre l\'attività originale dal database! ', $autenticazione -> id);
        stampaErrore('Impossibile estrarre l\'attività originale dal database!');
      }

      $datiOriginali = $query -> fetch_array();

      $idAttivita = $datiOriginali['id'];

      $descrizione = strip_tags($descrizione, '<strong><em><p><br><u><span><h1><h2><h3><ol><ul><li><a>');

      // Se non è presente una transazione la creo
      if($datiOriginali['fabcoin'] == null && $fabcoin != 0) {

        $sql = "INSERT INTO transazioniFabCoin (idUtente, descrizione, dataInserimento, annullabile, modificabile, valore) VALUES ('{$datiOriginali['idUtente']}', 'Attività riconosciuta da FabLab Romagna (ID: {$datiOriginali['id']})', '".time()."', 'FALSE', 'FALSE', '{$fabcoin}')";

        if($mysqli -> query($sql))  {

          $datiOriginali['idTransazioneFabCoin'] = $mysqli -> insert_id;
          $datiOriginali['fabcoin'] = $fabcoin;
          $console -> log('Creata transazione ('.$mysqli -> insert_id.') di F'.$fabcoin.' all\'utente '.$datiOriginali['idUtente'].' per un\'attività.', $autenticazione -> id);

        } else {
          $console -> alert('Impossibile creare la transazione per l\'attività! ', $autenticazione -> id);
          stampaErrore('Impossibile creare la transazione per l\'attività!');
        }

      // La transazione esiste già, la annullo e la ricreo
      } else if($datiOriginali['fabcoin'] != null && $fabcoin != $datiOriginali['fabcoin'] && $fabcoin != 0) {

        $idVecchio = $datiOriginali['idTransazioneFabCoin'];

        $sql = "UPDATE transazioniFabCoin SET annullata = TRUE WHERE id = '{$datiOriginali['idTransazioneFabCoin']}';";
        $sql .= "INSERT INTO transazioniFabCoin (idUtente, descrizione, dataInserimento, annullabile, modificabile, valore) VALUES ('{$datiOriginali['idUtente']}', 'Attività riconosciuta da FabLab Romagna (ID: {$datiOriginali['id']})', '".time()."', 'FALSE', 'FALSE', '{$fabcoin}')";

        if($mysqli -> multi_query($sql))  {

          $mysqli -> next_result();

          $datiOriginali['idTransazioneFabCoin'] = $mysqli -> insert_id;
          $datiOriginali['fabcoin'] = $fabcoin;
          $console -> warn('Eliminata transazione FabCoin ('.$idVecchio.') a seguito della modifica di un\'attività.', $autenticazione -> id);
          $console -> log('Creata transazione ('.$mysqli -> insert_id.') di F'.$fabcoin.' all\'utente '.$datiOriginali['idUtente'].' per un\'attività.', $autenticazione -> id);

        } else {
          $console -> alert('Impossibile creare la transazione per l\'attività! ', $autenticazione -> id);
          stampaErrore('Impossibile creare la transazione per l\'attività!');
        }

      // La transazione esiste ma deve essere annullata
      } else if($datiOriginali['fabcoin'] != null && $fabcoin == 0) {

        $idVecchio = $datiOriginali['idTransazioneFabCoin'];

        $sql = "UPDATE transazioniFabCoin SET annullata = TRUE WHERE id = '{$datiOriginali['idTransazioneFabCoin']}';";

        if($mysqli -> query($sql))  {

          $datiOriginali['idTransazioneFabCoin'] = 'NULL';
          $datiOriginali['fabcoin'] = 'NULL';
          $console -> warn('Eliminata transazione FabCoin ('.$idVecchio.') a seguito della modifica di un\'attività.', $autenticazione -> id);

        } else {
          $console -> alert('Impossibile eliminare la transazione per l\'attività! ', $autenticazione -> id);
          stampaErrore('Impossibile eliminare la transazione per l\'attività!');
        }
      }

      $inizio = strtotime("{$annoInizio}-{$meseInizio}-{$giornoInizio} {$oraInizio}:{$minutoInizio}");
      $fine = strtotime("{$annoEnd}-{$meseEnd}-{$giornoEnd} {$oraEnd}:{$minutoEnd}");

      if($datiOriginali['idTransazioneFabCoin'] == null)
        $datiOriginali['idTransazioneFabCoin'] = 'NULL';

      if($datiOriginali['fabcoin'] == null)
        $datiOriginali['fabcoin'] = 'NULL';

      while($mysqli -> more_results()){
        $mysqli -> next_result();
        $mysqli -> use_result();
      }

      // Aggiorno l'attività
      $sql = "UPDATE attivita SET descrizione = '{$descrizione}', inizio = '{$inizio}', fine = '{$fine}', aggiuntoIl = '".time()."', aggiuntoDa = '".$autenticazione -> id."', idTransazioneFabCoin = {$datiOriginali['idTransazioneFabCoin']}, fabcoin = {$datiOriginali['fabcoin']} WHERE id = '{$datiOriginali['id']}'";

      $query = $mysqli -> query($sql);

      if($query) {
        $console -> log('Aggiornata attività (ID: '.$datiOriginali['id'].').', $autenticazione -> id);
        echo '{}';

        $notificheUtente = new Notifiche($mysqli, $datiOriginali['idUtente']);
        $notificheUtente -> link('Ti è stata aggiornata un\'attività (ID: '.$datiOriginali['id'].').', '/account/visualizzaAttivita.php?id='.$datiOriginali['id']);

      } else {
        $console -> alert('Impossibile aggiornare l\'attività! '.$mysqli -> error, $autenticazione -> id);
        stampaErrore('Impossibile aggiornare l\'attività!'.$mysqli -> error);
      }
    }
  }
?>
