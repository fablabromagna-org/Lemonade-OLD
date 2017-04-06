<?php
  require_once('../class/gestioneAuth.class.php');
  require_once('../inc/mysqli.inc.php');
  require_once('../vendor/autoload.php');

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $id = $mysqli -> real_escape_string(isset($_POST['id']) ? trim($_POST['id']) : '');
  $richiesta = $mysqli -> real_escape_string(isset($_POST['richiesta']) ? trim($_POST['richiesta']) : '');
  $nome = $mysqli -> real_escape_string(isset($_POST['nome']) ? trim($_POST['nome']) : '');
  $portale = $mysqli -> real_escape_string(isset($_POST['portale']) ? trim($_POST['portale']) : '');
  $rete = $mysqli -> real_escape_string(isset($_POST['rete']) ? trim($_POST['rete']) : '');
  $destinazione = $mysqli -> real_escape_string(isset($_POST['destinazione']) ? trim($_POST['destinazione']) : '');


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

    if($richiesta == 'elimina') {

      // Controllo l'ID sia diverso da vuoto
      if($id == '')
        stampaErrore('Devi inserire un ID valido!');

      else if($id == '1')
        stampaErrore('Non puoi eliminare la categoria predefinita!');

      // Aggiorno i dati nel database
      else {

        // Estraggo i dati relativi all'utente
        $sql = "UPDATE utenti SET categoria = '1' WHERE categoria = '{$id}'; DELETE FROM categorieUtenti WHERE id = '{$id}';";

        if($query = $mysqli -> multi_query($sql))
          echo '{}';

        else
          stampaErrore('Impossibile inviare la richiesta al database!');
      }
      
    } else if($richiesta == 'rinomina') {

      // Controllo l'ID sia diverso da vuoto
      if($id == '')
        stampaErrore('Devi inserire un ID valido!');

      else if($nome == '')
        stampaErrore('Devi inserire un nome!');

      else if(!preg_match("/^[a-z ]+$/i", $nome))
        stampaErrore('Il nome può contenere solo lettere!');

      // Aggiorno i dati nel database
      else {

        // Estraggo i dati relativi all'utente
        $sql = "UPDATE categorieUtenti SET nome = '{$nome}' WHERE id = '{$id}';";

        if($query = $mysqli -> query($sql))
          echo '{}';

        else
          stampaErrore('Impossibile inviare la richiesta al database!');
      }


    } else if($richiesta == 'aggiungi') {

      // Controllo i valori
      if($nome == '')
        stampaErrore('Devi inserire un nome!');

      else if(!preg_match("/^[a-z ]+$/i", $nome))
        stampaErrore('Il nome può contenere solo lettere!');

      // I valori vanno bene
      else {

        // Potere di gestione della rete interna ai maker space
        if($rete == 'true')
          $rete = true;

        else
          $rete = false;

        // Potere di gestione del portale
        if($portale == 'true')
          $portale = true;

        else
          $portale = false;

        $sql = "INSERT INTO categorieUtenti (nome, gestionePortale, gestioneRete) VALUES ('{$nome}', '{$portale}', '{$rete}')";

        if($query = $mysqli -> query($sql))
          echo '{}';

        else
          stampaErrore('Impossibile creare la categoria!');

      }
    } else if($richiesta == 'sposta') {

      // Controllo l'ID sia diverso da vuoto
      if($id == '' || $destinazione == '' || $id == $destinazione)
        stampaErrore('Devi inserire un ID valido!');

      // Aggiorno i dati nel database
      else {

        // Estraggo i dati relativi all'utente
        $sql = "SELECT * FROM categorieUtenti WHERE id = '{$destinazione}'";

        if($query = $mysqli -> query($sql)) {

          if($query -> num_rows > 0) {

            $sql = "UPDATE utenti SET categoria = '{$destinazione}' WHERE categoria = '{$id}'";

              if($query = $mysqli -> query($sql))
                stampaErrore('Spostamento completato!');

              else
                stampaErrore('Impossibile spostare gli utenti!');

          } else
            stampaErrore('Categoria inesistente!');

        } else
          stampaErrore('Impossibile inviare la richiesta al database!');
      }
    } else
      stampaErrore();
  }
?>