<?php
  require_once('../inc/carica.inc.php');
  require_once('../vendor/autoload.php');

  // Configuro Mailgun
  use Mailgun\Mailgun;
  $dominio = DOMINIO_EMAIL_MAILGUN;
  $mailgun = new Mailgun(MAILGUN_API_KEY);

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $nome = $mysqli -> real_escape_string(isset($_POST['nome']) ? trim($_POST['nome']) : '');
  $cognome = $mysqli -> real_escape_string(isset($_POST['cognome']) ? trim($_POST['cognome']) : '');
  $email = $mysqli -> real_escape_string(isset($_POST['email']) ? trim($_POST['email']) : '');
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
  else if($autenticazione -> gestionePortale!= 1)

    // L'utente non ha i permessi
    stampaErrore('Non sei autorizzato ad effettuare la modifica!');

  // L'utente ha effettuato l'accesso ed è autorizzato
  else {

    // Controllo l'id sia diverso da vuoto
    if($id == '')
      stampaErrore('ID mancante!');

    // Controllo che il nome sia valido
    else if($nome == '' || !preg_match("/^[a-z ,.'-]+$/i", $nome))
      stampaErrore('Devi inserire un nome valido!');

    // Controllo che il cognome sia valido
    else if($cognome == '' || !preg_match("/^[a-z ,.'-]+$/i", $cognome))
      stampaErrore('Devi inserire un cognome valido!');

    // Controllo che l'indirizzo email sia valido
    else if($email == '' || !filter_var($email, FILTER_VALIDATE_EMAIL))
      stampaErrore('Devi inserire un indirizzo email valido!');

    // Aggiorno i dati nel database
    else {

      // Estraggo i dati relativi all'utente
      $sql = "SELECT * FROM utenti WHERE id = '{$id}'";

      if($query = $mysqli -> query($sql)) {

        // Nessun utente è stato trovato
        if($query -> num_rows != 1)
          stampaErrore('L\'utente richiesto non è presente nel database.');

        // Controllo se i dati sono stati modificati
        else {

          $modificati = false;
          $row = $query -> fetch_assoc();

          if($row['nome'] != $nome)
            $modificati = true;

          if($row['cognome'] != $cognome)
            $modificati = true;

          if($row['email'] != $email)
            $modificati = true;

          // Avviso l'utente che nessun dato è stato modificato
          if(!$modificati)
            stampaErrore('Nessun dato è stato modificato.');

          // I dati sono stati modificati
          // Controllo che l'indirizzo email non sia utilizzato da un altro utente
          $vecchiaRow = $row;

          $sql = "SELECT id, email FROM utenti WHERE email = '{$email}'";

          if(!$query = $mysqli -> query($sql))
            stampaErrore('Impossibile inviare la richiesta al database!');

          else {

            // Creo una funzione che verrà richiamata per modificare i dati dell'utente
            function modifica($nome, $cognome, $email, $id, $emailOriginale, $mysqli, $mailgun, $codiceConfermaMail) {

              // Genero un codice di attivazione se l'indirizzo è diverso da quello nel db
              if($emailOriginale != $email) {
                $codiceAttivazione = uniqid();
                $link = URL_SITO.'/confermaMail.php?token='.$codiceAttivazione;

              } else
                $codiceAttivazione = $codiceConfermaMail;

              // Creo la query
              $sql = "UPDATE utenti SET nome = '{$nome}', cognome = '{$cognome}', email = '{$email}', codiceAttivazione = '{$codiceAttivazione}' WHERE id = '{$id}'";

              // Eseguo la query
              if(!$query = $mysqli -> query($sql))
                stampaErrore('Impossibile contattare il database!');

              else {

                // Se l'indirizzo non è stato modificato
                // Blocco l'esecuzione per evitare l'invio di una nuova email
                if($emailOriginale == $email) {
                  echo '{}';
                  return;
                }

                // La modifica è avvenuta con successo
                // Invio una mail all'indirizzo originale e a quello nuovo
                try {
                  $mailgun -> sendMessage(DOMINIO_EMAIL_MAILGUN, array(
                    'to' => $emailOriginale,
                    'from' => MITTENTE_EMAIL." <".INDIRIZZO_MITTENTE.">",
                    'h:Reply-To' => MITTENTE_EMAIL." <".INDIRIZZO_MITTENTE.">",
                    'html' => file_get_contents('../mail/modificaMailVecchioIndirizzo/mail.html'),
                    'subject' => 'Modifica dell\'indirizzo email',
                    'recipient-variables' => "{ \"{$emailOriginale}\": { \"nomeUtente\": \"{$nome}\", \"nomeSito\": \"".NOME_SITO."\", \"urlSito\": \"".URL_SITO."\", \"indirizzoMittente\": \"".INDIRIZZO_MITTENTE."\" } }"
                  ));

                  $mailgun -> sendMessage(DOMINIO_EMAIL_MAILGUN, array(
                    'to' => $email,
                    'from' => MITTENTE_EMAIL." <".INDIRIZZO_MITTENTE.">",
                    'h:Reply-To' => MITTENTE_EMAIL." <".INDIRIZZO_MITTENTE.">",
                    'html' => file_get_contents('../mail/modificaMailNuovoIndirizzo/mail.html'),
                    'subject' => 'Conferma E-Mail',
                    'recipient-variables' => "{ \"{$email}\": { \"nomeUtente\": \"{$nome}\", \"nomeSito\": \"".NOME_SITO."\", \"urlSito\": \"".URL_SITO."\", \"indirizzoMittente\": \"".INDIRIZZO_MITTENTE."\" } }"
                  ));

                  echo '{}';

                } catch(Exception $e) {
                  stampaErrore('Impossibile inviare l\'email di conferma!');
                }
              }
            }

            // Se il database restituisce più di una riga
            // E' sicuramente presente un errore
            if($query -> num_rows > 1)
              stampaErrore('Sono presenti più persone con questo indirizzo email nel database!');

            // Se restituisce una riga controllo che non sia già utilizzato
            else if($query -> num_rows == 1) {

              $row = $query -> fetch_assoc();

              if($row['id'] == $id)
                modifica($nome, $cognome, $email, $id, $vecchiaRow['email'], $mysqli, $mailgun, $vecchiaRow['codiceAttivazione']);

              else
                stampaErrore('E-Mail già utilizzata!');

            // Se non restituisce nulla modifico direttamente
            } else
              modifica($nome, $cognome, $email, $id, $vecchiaRow['email'], $mysqli, $mailgun, $vecchiaRow['codiceAttivazione']);

          }
        }
      } else
        stampaErrore('Impossibile inviare la richiesta al database!');
    }
  }
?>