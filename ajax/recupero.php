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
        $cognome = $dati['cognome'];

        $sql = "UPDATE utenti SET password = '$hashPassword' WHERE id = '".$dati['id']."'";

        if($query = $mysqli -> query($sql)) {

          // Invio la mail
          try {
            $replyTo = ($dizionario -> getValue('EMAIL_REPLY_TO') == false || $dizionario -> getValue('EMAIL_REPLY_TO') == null) ? $dizionario -> getValue('EMAIL_SOURCE') : $dizionario -> getValue('EMAIL_REPLY_TO');
            $html = $templateManager -> getTemplate('recuperoPassword', array(
              'nome' => $nome,
              'cognome' => $cognome,
              'password' => $password,
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
                  'Data' => 'Nuova password',
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
          stampaErrore('Impossibile aggiornare la password!');

      } else
        stampaErrore('Account inesistente!');

    } else
      stampaErrore('Impossibile controllare l\'esistenza dell\'account!');
  }
?>
