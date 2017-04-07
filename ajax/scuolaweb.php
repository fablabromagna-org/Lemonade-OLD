<?php
  require_once('../class/gestioneAuth.class.php');
  require_once('../inc/mysqli.inc.php');
  require_once('../lib/simpletest/browser.php');

  $autenticazione = new Autenticazione($mysqli);
  if(!$autenticazione -> isLogged())
    exit('{ "errore": true, "msg": "Autenticazione fallita."}');

  else if($autenticazione -> scuolaweb > 0 || $autenticazione -> categoria > 2)
    exit('{ "errore": true, "msg": "Verifica non autorizzata."}');

  header('Content-Type: application/json');

  $utente = $mysqli -> real_escape_string(isset($_POST['utente']) ? trim($_POST['utente']) : '');
  $password = $mysqli -> real_escape_string(isset($_POST['pwd']) ? trim($_POST['pwd']) : '');

  function stampaErrore($errore = 'Errore sconosciuto!') {
    echo '{"errore":true,"msg":"'.$errore.'"}';
    exit();
  }

  // Controllo i dati
  if($utente === '')
    stampaErrore('Devi inserire il tuo codice utente!');

  else if(!preg_match('/^[0-9]+$/', $utente))
    stampaErrore('Devi inserire il tuo codice utente valido!');

  else if($password === '')
    stampaErrore('Devi inserire la tua password!');

  // Invio le credenziali a scuolaweb
  else {

    // Studenti/Genitori
    if($autenticazione -> categoria == 0 || $autenticazione -> categoria == 2)
      $url = 'https://registro.itis-cesena.it/scuolawebfamiglie/src/login.aspx?Scuola=FOTF010008';

    // Docenti
    else if($autenticazione -> categoria == 1)
      $url = 'https://registro.itis-cesena.it/scuolaweb/src/login.aspx?Scuola=FOTF010008';

    // Creo un browser ed abilito i cookie
    $browser = new SimpleBrowser();
    $browser -> useCookies();

    // Imposto IP e User Agent dell'utente
    $browser -> addHeader('X-Forwarded-For: '.$_SERVER['HTTP_X_FORWARDED_FOR']);
    $browser -> addHeader('User-Agent: '.$_SERVER['HTTP_USER_AGENT']);

    // Apro la pagina di accesso
    $browser -> get($url);

    // Controllo che il server abbia risposto correttamente
    if($browser -> getResponseCode() != 200)
      exit('{ "errore": true, "msg": "Impossibile contattare il registro elettronico! '.$browser -> getTransportError().'"}');

    // Inserisco le credenziali
    $browser -> setFieldById('LoginControl1_txtCodUser', $utente);
    $browser -> setFieldById('LoginControl1_txtPassword', $password);

    // Invio il form
    $browser -> clickSubmitById('LoginControl1_btnOk');

    // Parso l'html di risposta
    $doc = new DOMDocument();
    $doc -> loadHTML($browser -> getContent());

    // Memorizzo il nodo di errore
    $errore = $doc -> getElementById('LoginControl1_lblOutput');

    // Accesso effettuato
    if($errore == null) {

      // Memorizzo il nodo con nome e cognome
      $nomeCognome = $doc -> getElementById('ctl00_pnlHeader_lblUtente');

      // Controllo che esista
      if($nomeCognome == null)
        stampaErrore();

      // Memorizzo i dati
      else {

        // Rimuovo la dicitura iniziale "Utente: "
        $nomeCognome = str_replace('Utente: ', '', $nomeCognome -> nodeValue);

        // Preparo le query
        $sql = "INSERT INTO confermeScuolaWeb (codiceUtente, idUtente, ip, nomeAccount, data) VALUES ('$utente', '".$autenticazione -> id."', '".$_SERVER['X-Forwarded-For']."', '$nomeCognome', '".time()."'); ";
        $sql .= "UPDATE utenti SET scuolaweb = '1' WHERE id = '".$autenticazione -> id."'";

        // Eseguo la query
        if($query = $mysqli -> multi_query($sql)) {
          echo '{}';

        } else
          stampaErrore('Impossibile completare la verifica!');
      }

    // Errore durante l'accesso
    } else
      stampaErrore('Il registro elettronico ha risposto con un errore: '.$errore -> nodeValue.'');
  }
?>