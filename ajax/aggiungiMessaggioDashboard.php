<?php
  require_once('../inc/carica.inc.php');
  require_once('../vendor/autoload.php');

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $titolo = $mysqli -> real_escape_string(isset($_POST['titolo']) ? trim($_POST['titolo']) : '');
  $descrizione = $mysqli -> real_escape_string(isset($_POST['descrizione']) ? trim($_POST['descrizione']) : '');
  $link = $mysqli -> real_escape_string(isset($_POST['link']) ? trim($_POST['link']) : '');
  $testo = $mysqli -> real_escape_string(isset($_POST['testo']) ? trim($_POST['testo']) : '');

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

    // Controllo il titolo sia diverso da vuoto
    if($titolo == '')
      stampaErrore('Devi inserire un titolo!');

    // Aggiorno i dati nel database
    else {

      if($link != '' && $testo == '')
        $testo = 'Apri il link';

      $tipo = 2;
      if($link != '')
        $tipo = 1;

      $titolo = strip_tags($titolo);
      $descrizione = strip_tags($descrizione, '<b><i>');
      $testo = strip_tags($testo);

      // Estraggo i dati relativi all'utente
      $sql = "INSERT INTO dashboard (titolo, descrizione, tipo, linkBottone, titoloLink) VALUES ('{$titolo}', '{$descrizione}', '{$tipo}', '{$link}', '{$testo}')";

      if($query = $mysqli -> query($sql)) {
        $console -> log("Aggiunto messaggio sulla dashboard ({$titolo}).", $autenticazione -> id);
        echo '{}';
      } else
        stampaErrore('Impossibile inviare la richiesta al database! '.$mysqli -> error);
    }
  }
?>