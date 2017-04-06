<?php
  require_once('../inc/mysqli.inc.php');
  require_once('../vendor/autoload.php');

  // Configuro Mailgun
  use Mailgun\Mailgun;
  $dominio = DOMINIO_EMAIL_MAILGUN;
  $mailgun = new Mailgun(MAILGUN_API_KEY);

  header('Content-Type: application/json');

  // Raccolgo tutti i dati e li "pulisco"
  $nome = $mysqli -> real_escape_string(isset($_POST['nome']) ? trim($_POST['nome']) : '');
  $cognome = $mysqli -> real_escape_string(isset($_POST['cognome']) ? trim($_POST['cognome']) : '');
  $pwd = $mysqli -> real_escape_string(isset($_POST['pwd']) ? trim($_POST['pwd']) : '');
  $email = $mysqli -> real_escape_string(isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '');
  $ip = $mysqli -> real_escape_string($_SERVER['REMOTE_ADDR']);

  function stampaErrore($errore = 'Errore sconosciuto!') {
    echo '{"errore":true,"msg":"'.$errore.'"}';
    exit();
  }

  // Controllo i dati
  if($nome === "")
    stampaErrore('Il nome è obbligatorio!');

  else if(!preg_match("/^[a-z ,.'-]+$/i", $nome))
    stampaErrore('Devi inserire un nome valido!');

  else if($cognome === "")
    stampaErrore('Il cognome è obbligatorio!');

  else if(!preg_match("/^[a-z ,.'-]+$/i", $cognome))
    stampaErrore('Devi inserire un cognome valido!');

  else if($email === "")
    stampaErrore('L\'indirizzo email è obbligatorio!');

  else if(!filter_var($email, FILTER_VALIDATE_EMAIL))
    stampaErrore('L\'indirizzo inserito non è valido!');

  else if($pwd === "")
    stampaErrore('Devi inserire una password!');

  else if(strlen($pwd) < 6)
    stampaErrore('La password deve contenere almeno sei caratteri!');

  // Confronto i dati col database
  else {

    $sql = "SELECT email FROM utenti WHERE email = '".$email."' AND codiceAttivazione = '0'";

    // Eseguo la query
    if($query = $mysqli -> query($sql)) {

      // Conto le righe
      if($query -> num_rows === 0) {

        // Controllo che l'account non sia da confermare
        $sql = "SELECT email FROM utenti WHERE email = '".$email."'";

        // Eseguo la query
        if($query = $mysqli -> query($sql)) {

          // Genero l'hash di conferma
          $codiceConferma = uniqid();

          if($query -> num_rows == 0)
            $sql = "INSERT INTO utenti (nome, cognome, email, password, codiceAttivazione, ipRegistrazione) VALUES ('".$nome."', '".$cognome."', '".$email."', '".md5($pwd)."', '".$codiceConferma."',  '".$ip."')";

          else
            $sql = "UPDATE utenti SET nome = '".$nome."', cognome = '".$cognome."', password = '".md5($pwd)."', codiceAttivazione = '".$codiceConferma."',  ipRegistrazione = '".$ip."' WHERE email = '".$email."'";

          if($query = $mysqli -> query($sql)) {

            $link = URL_SITO.'/confermaMail.php?token='.$codiceConferma;

            try {
              $mailgun -> sendMessage($dominio, array(
                'to' => $email,
                'from' => MITTENTE_EMAIL." <".INDIRIZZO_MITTENTE.">",
                'h:Reply-To' => MITTENTE_EMAIL." <".INDIRIZZO_MITTENTE.">",
                'html' => file_get_contents('../mail/registrazione/mail.html'),
                'subject' => 'Conferma indirizzo email',
                'recipient-variables' => "{ \"{$email}\": { \"nomeUtente\": \"{$nome}\", \"nomeSito\": \"".NOME_SITO."\", \"link\": \"{$link}\", \"urlSito\": \"".URL_SITO."\", \"indirizzoMittente\": \"".INDIRIZZO_MITTENTE."\" } }"
              ));

              echo '{}';

            } catch(Exception $e) {
              stampaErrore('Impossibile inviare l\'email!');
            }

          } else
            stampaErrore('Impossibile eseguire la richiesta al database! #3');

        } else
          stampaErrore('Impossibile eseguire la richiesta al database! #2');

      } else
        stampaErrore('E-Mail già utilizzata!');

    } else
      stampaErrore('Impossibile eseguire la richiesta al database! #1');
  }
?>