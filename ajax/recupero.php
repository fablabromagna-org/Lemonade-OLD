<?php
  require_once('../inc/mysqli.inc.php');
  require_once('../vendor/autoload.php');

  // Configuro Mailgun
  use Mailgun\Mailgun;
  $dominio = DOMINIO_EMAIL_MAILGUN;
  $mailgun = new Mailgun(MAILGUN_API_KEY);

  header('Content-Type: application/json');

  $email = $mysqli -> real_escape_string(isset($_POST['email']) ? trim($_POST['email']) : '');

  function stampaErrore($errore = 'Errore sconosciuto!') {
    echo '{"errore":true,"msg":"'.$errore.'"}';
    exit();
  }

  function randomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.@!#[]{}+-_,;.*?';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  // Controllo i dati
  if($email == '')
    stampaErrore('Devi inserire il tuo indirizzo email!');

  else if(!filter_var($email, FILTER_VALIDATE_EMAIL))
    stampaErrore('Devi inserire un indirizzo un indirizzo email valido!');

  // I dati sono validi
  else {

    // Li confronto col database
    $sql = "SELECT * FROM utenti WHERE email = '$email'";

    if($query = $mysqli -> query($sql)) {

      // L'account esiste
      if($query -> num_rows == 1) {

        $dati = $query -> fetch_assoc();

        $password = randomString();
        $hashPassword = md5($password);
        $nome = $dati['nome'];

        $sql = "UPDATE utenti SET password = '$hashPassword' WHERE id = '".$dati['id']."'";

        if($query = $mysqli -> query($sql)) {

          // Invio la mail
          try {
            $mailgun -> sendMessage($dominio, array(
              'to' => $email,
              'from' => MITTENTE_EMAIL." <".INDIRIZZO_MITTENTE.">",
              'h:Reply-To' => MITTENTE_EMAIL." <".INDIRIZZO_MITTENTE.">",
              'html' => file_get_contents('../mail/password/mail.html'),
              'subject' => 'Recupero password',
              'recipient-variables' => "{ \"{$email}\": { \"nomeUtente\": \"{$nome}\", \"nomeSito\": \"".NOME_SITO."\", \"password\": \"{$password}\", \"urlSito\": \"".URL_SITO."\", \"indirizzoMittente\": \"".INDIRIZZO_MITTENTE."\" } }"
            ));


            echo '{}';

          } catch(Exception $e) {
            stampaErrore('Impossibile inviare l\'email!');
          }

        } else
          stampaErrore('Impossibile aggiornare la password!');

      } else
        stampaErrore('Account inesistente!');

    } else
      stampaErrore('Impossibile controllare l\'esistenza dell\'account!');
  }
?>