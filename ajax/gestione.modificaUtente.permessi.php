<?php
  require_once('../inc/carica.inc.php');
  require_once('../vendor/autoload.php');

  // Configuro AWS SES
  use Aws\Ses\SesClient;
  $client = SesClient::factory(array(
    'key' => $dizionario -> getValue('AWS_KEY'),
    'secret' => $dizionario -> getValue('AWS_SECRET'),
    'region'  => $dizionario -> getValue('AWS_REGION')
  ));

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $categoria = $mysqli -> real_escape_string(isset($_POST['categoria']) ? trim($_POST['categoria']) : '');
  $gestionePortale = $mysqli -> real_escape_string(isset($_POST['gestionePortale']) ? trim($_POST['gestionePortale']) : '');
  $gestioneRete = $mysqli -> real_escape_string(isset($_POST['gestioneRete']) ? trim($_POST['gestioneRete']) : '');
  $sospensione = $mysqli -> real_escape_string(isset($_POST['sospensione']) ? trim($_POST['sospensione']) : '');
  $confermaMail = $mysqli -> real_escape_string(isset($_POST['confermaMail']) ? trim($_POST['confermaMail']) : '');
  $id = $mysqli -> real_escape_string(isset($_POST['id']) ? trim($_POST['id']) : '');

  function stampaErrore($errore = 'Errore sconosciuto!') {
    echo '{"errore":true,"msg":"'.$errore.'"}';
    exit();
  }

  $confermaMail = ($confermaMail == '1') ? '0' : '1';

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

    // Controllo l'id sia diverso da vuoto
    if($id == '')
      stampaErrore('ID mancante!');

    // Controllo che i valori siano validi
    else if($categoria == '')
      stampaErrore('Devi inserire una categoria!');

    else if($gestionePortale != '0' && $gestionePortale != '1' && $gestionePortale != '2')
      stampaErrore('Devi inserire il permesso di gestione del portale!');

    else if($gestioneRete != '0' && $gestioneRete != '1' && $gestioneRete != '2')
      stampaErrore('Devi inserire il permesso di gestione della rete!');

    else if($sospensione != '1' && $sospensione != '0')
      stampaErrore('Devi indicare lo stato di sospensione dell\'account!');

    else if($confermaMail != '1' && $confermaMail != '0')
      stampaErrore('Devi indicare lo stato di verifica dell\'indirizzo email!');

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

          if($row['categoria'] != $categoria)
            $modificati = true;

          if($row['gestionePortale'] != $gestionePortale)
            $modificati = true;

          if($row['gestioneRete'] != $gestioneRete)
            $modificati = true;

          if($row['sospeso'] != $sospensione)
            $modificati = true;

          if($row['codiceAttivazione'] != $confermaMail)
            $modificati = true;

          // Avviso l'utente che nessun dato è stato modificato
          if(!$modificati)
            stampaErrore('Nessun dato è stato modificato.');

          // I dati sono stati modificati
          // Controllo che la categoria esista
          $vecchiaRow = $row;

          $sql = "SELECT * FROM categorieUtenti WHERE id = '{$categoria}'";

          if(!$query = $mysqli -> query($sql))
            stampaErrore('Impossibile inviare la richiesta al database!');

          else {

            // Se il database restituisce più di una riga
            // E' sicuramente presente un errore
            if($query -> num_rows > 1)
              stampaErrore('Database corrotto!');

            // Se restituisce una riga la categoria esiste
            else if($query -> num_rows == 1) {

              $codiceAttivazione = '0';
              if($row['codiceAttivazione'] == '0' && $confermaMail == '0')
                $codiceAttivazione = uniqid();

              $sql = "UPDATE utenti SET categoria = '{$categoria}', gestionePortale = '{$gestionePortale}', gestioneRete = '{$gestioneRete}', sospeso = '{$sospensione}', codiceAttivazione = '{$codiceAttivazione}' WHERE id = '{$id}'";

              if($mysqli -> query($sql)) {

                // Se la conferma dell'indirizzo email è cambiata
                // Rinvio la mail
                if($row['codiceAttivazione'] != $confermaMail) {

                  $linkVerifica = $dizioanario -> getValue('urlSito').'/confermaMail.php?token='.$codiceAttivazione;

                  try {
                    $replyTo = ($dizionario -> getValue('EMAIL_REPLY_TO') == false || $dizionario -> getValue('EMAIL_REPLY_TO') == null) ? $dizionario -> getValue('EMAIL_SOURCE') : $dizionario -> getValue('EMAIL_REPLY_TO');
                    $html = $templateManager -> getTemplate('confermaMailGestione', array(
                      'nome' => $nome,
                      'cognome' => $cognome,
                      'linkConferma' => $linkVerifica,
                      'nomeSito' => $dizionario -> getValue('nomeSito')
                    ));

                    $client -> sendEmail(array(
                      'Source' => $dizionario -> getValue('EMAIL_SOURCE'),
                      'ReplyToAddresses' => array($replyTo),
                      'Destination' => array(
                        'ToAddresses' => array($email)
                      ),
                      'Message' => array(
                        'Subject' => array(
                          'Data' => 'Verifica indirizzo email',
                          'Charset' => 'UTF-8'
                        ),
                        'Body' => array(
                          'Html' => array(
                            'Data' => $html,
                            'Charset' => 'UTF-8'
                          )
                        )
                      )
                    ));

                    echo '{}';

                  } catch(Exception $e) {
                    stampaErrore('Impossibile inviare l\'email!');
                  }

                } else
                  echo '{}';

              } else
                stampaErrore('Impossibile aggiornare i dati dell\'utente!');

            // Se non restituisce nulla la categoria non esiste
            } else
              stampaErrore('Categoria inesistente!');
          }
        }
      } else
        stampaErrore('Impossibile inviare la richiesta al database!');
    }
  }
?>
