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
  $sospensione = $mysqli -> real_escape_string(isset($_POST['sospensione']) ? trim($_POST['sospensione']) : '');
  $confermaMail = $mysqli -> real_escape_string(isset($_POST['confermaMail']) ? trim($_POST['confermaMail']) : '');
  $id = $mysqli -> real_escape_string(isset($_POST['id']) ? trim($_POST['id']) : '');

  function stampaErrore($errore = 'Impossibile completare la richiesta.') {
    echo '{"errore":true,"msg":"'.$errore.'"}';
    exit();
  }

  // Controllo che l'utente abbia effettuato l'accesso
  if(!$autenticazione -> isLogged())

    // L'utente non ha effettuato l'accesso
    stampaErrore('Non hai effettuato l\'accesso!');

  // Controllo che l'utente abbia i permessi per effettuare la modifica
  if(!$permessi -> whatCanHeDo($autenticazione -> id)['gestioneUtentiAvanzata']['stato'])

    // L'utente non ha i permessi
    stampaErrore('Non sei autorizzato ad effettuare la modifica!');

  // L'utente ha effettuato l'accesso ed è autorizzato
  // Controllo i valori
  if(!preg_match("/^[0-9]{1,11}+$/i", $id))
    stampaErrore('ID non valido!');

  if(!preg_match("/^[0-9]{1}+$/i", $confermaMail))
    stampaErrore('Valore non valido!');

  if(!preg_match("/^[0-9]{1}+$/i", $sospensione))
    stampaErrore('Valore non valido!');

  if(!preg_match("/^[0-9]{1,11}+$/i", $categoria))
    stampaErrore('ID categoria non valido!');

  // Estraggo i dati dell'utente
  $sql = "SELECT * FROM utenti WHERE id = {$id} LIMIT 0, 1";
  $query = $mysqli -> query($sql);

  if(!$query) {
    $console -> alert('Impossibile controllare l\'esistenza dell\'utente. '.$mysqli -> error, $autenticazione -> id);
    stampaErrore();
  }

  // Controllo che l'utente esista
  if($query -> num_rows === 0)
    stampaErrore('Utente inesistente!');

  $utente = $query -> fetch_assoc();

  // Controllo che la nuova categoria esista
  $sql = "SELECT * FROM categorieUtenti WHERE id = {$categoria} LIMIT 0, 1";
  $query = $mysqli -> query($sql);

  if(!$query) {
    $console -> alert('Impossibile controllare l\'esistenza della categoria. '.$mysqli -> error, $autenticazione -> id);
    stampaErrore();
  }

  // Controllo che la categoria esista
  if($query -> num_rows === 0)
    stampaErrore('Categoria inesistente!');

  /*
  Il campo codice attivazione è mappato diversamente sulla pagina web
  Valore || Pagina web     || DB
  0      || non verificato || verificato
  != 0   || verificato     || non verificato

  Quindi inverto il campo
  */
  $confermaMail = $confermaMail == 0 ? 1 : 0;

  // Aggiorno i campi modificati
  $sql = "UPDATE utenti SET ";

  // Gruppo di appartenenza
  if($utente['categoria'] != $categoria)
    $sql .= "categoria = {$categoria}, ";

  // Sospensione del profilo
  if($utente['sospeso'] != $sospensione)
    $sql .= "sospeso = {$sospensione}, ";

  // Verifica dell'indirizzo email
  if($utente['codiceAttivazione'] == 0 && $confermaMail === 1) {

    $codiceAttivazione = uniqid();
    $linkVerifica = $dizionario -> getValue('urlSito').'/confermaMail.php?token='.$codiceAttivazione;
    $replyTo = ($dizionario -> getValue('EMAIL_REPLY_TO') == false || $dizionario -> getValue('EMAIL_REPLY_TO') == null) ? $dizionario -> getValue('EMAIL_SOURCE') : $dizionario -> getValue('EMAIL_REPLY_TO');

    try {

      // Compilo il template
      $html = $templateManager -> getTemplate('confermaMailGestione', array(
        'nome' => $utente['nome'],
        'cognome' => $utente['cognome'],
        'linkConferma' => $linkVerifica,
        'nomeSito' => $dizionario -> getValue('nomeSito')
      ));

      // Invio l'email
      $client -> sendEmail(array(
        'Source' => $dizionario -> getValue('EMAIL_SOURCE'),
        'ReplyToAddresses' => array($replyTo),
        'Destination' => array(
          'ToAddresses' => array($utente['email'])
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

    // Errore durante l'invio dell'email
    } catch(Exception $e) {
      $console -> alert('Impossibile inviare l\'email di verifica. '.$e, $autenticazione -> id);
      stampaErrore();
    }

    $sql .= "codiceAttivazione = '{$codiceAttivazione}'";
  }

  if($utente['codiceAttivazione'] != 0 && $confermaMail === 0)
    $sql .= "codiceAttivazione = '0'";

  // Se la query è diversa da quella di base
  // La eseguo, altrimenti avviso l'utente che nulla è stato modificato
  if($sql === "UPDATE utenti SET ")
    stampaErrore('Nessun dato è stato modificato.');

  if(mb_substr($sql, -strlen(", ")) == ", ")
    $sql = mb_substr($sql, 0, strlen($sql)-strlen(", "));

  $query = $mysqli -> query("{$sql} WHERE id = {$id}");

  if(!$query) {
    $console -> alert($sql.' Impossibile aggiornare i dati. '.$mysqli -> error, $autenticazione -> id);
    stampaErrore();
  }

  echo '{}';
?>
