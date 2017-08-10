<?php
  namespace Longman\TelegramBot\Commands\SystemCommands;

  require_once(__DIR__.'/../../../../inc/carica.inc.php');
  require_once(__DIR__.'/../../../../vendor/autoload.php');

  use Longman\TelegramBot\Commands\SystemCommand;
  use Longman\TelegramBot\Request;

  class StartCommand extends SystemCommand {
    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';
    protected $version = '1.0.0';

    public function execute() {
        $message = $this -> getMessage();
        $chat_id = $GLOBALS['mysqli'] -> real_escape_string($message -> getChat() -> getId());
        $text = $GLOBALS['mysqli'] -> real_escape_string(trim($message -> getText(true)));

        // L'utente non ha inviato il suo codice di autenticazione
        if(!preg_match("/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}+$/i", $text)) {
          $text = 'Ciao!' . PHP_EOL . 'Per poter utilizzare questo bot, segui le indicazioni sul sito.';
          $data = [
              'chat_id' => $chat_id,
              'text' => $text,
          ];
          return Request::sendMessage($data);
        }

        // Faccio un controllo con il db
        $sql = "SELECT utenti.nome FROM socialNetworks INNER JOIN utenti ON utenti.id = socialNetworks.idUtente WHERE socialNetworks.authCode = '{$text}' LIMIT 0, 1";
        $query = $GLOBALS['mysqli'] -> query($sql);

        if(!$query) {
          $GLOBALS['console'] -> alert('Impossibile completare il controllo! '.$GLOBALS['mysqli'] -> error, 0);

          $text = 'Impossibile completare la richiesta.';
          $data = [
              'chat_id' => $chat_id,
              'text' => $text,
          ];

          return Request::sendMessage($data);
        }

        // Controllo che il token esista
        if($query -> num_rows === 0) {
          $text = 'Token di autenticazione non valido, riprova seguendo le indicazioni sul sito.';
          $data = [
              'chat_id' => $chat_id,
              'text' => $text,
          ];

          return Request::sendMessage($data);
        }

        $row = $query -> fetch_assoc();

        // Il token è valido, aggiorno i dati nel db
        $sql = "UPDATE socialNetworks SET authCode = NULL, idSocial = '{$chat_id}' WHERE authCode = '{$text}'";
        $query = $GLOBALS['mysqli'] -> query($sql);

        if(!$query) {
          $GLOBALS['console'] -> alert('Impossibile completare l\'aggiornamento della tabella! '.$GLOBALS['mysqli'] -> error, 0);

          $text = 'Impossibile completare la richiesta.';
          $data = [
              'chat_id' => $chat_id,
              'text' => $text,
          ];

          return Request::sendMessage($data);
        }

        $text = "Ciao {$row['nome']}, l'associazione con il tuo profilo è riuscita!";
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
        ];

        return Request::sendMessage($data);
    }
  }
?>
