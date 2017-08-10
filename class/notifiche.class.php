<?php
  class Notifiche {
    private $mysqli;
    public $numNotificheNonLette = false;
    private $idUtente = null;

    public function __construct($mysqli, $idUtente) {
      $this -> mysqli = $mysqli;

      $idUtente = $this -> mysqli -> real_escape_string($idUtente);
      $sql = "SELECT * FROM notifiche WHERE idUtente = '{$idUtente}' AND letto = '0'";

      $query = $this -> mysqli -> query($sql);
      if($query)
        $this -> numNotificheNonLette = $query -> num_rows;

      $this -> idUtente = $idUtente;
    }

    public function noLink($msg) {
      $this -> social($msg);

      $msg = $this -> mysqli -> real_escape_string($msg);

      if($this -> mysqli -> query("INSERT INTO notifiche (idUtente, descrizione, data) VALUES ('{$this -> idUtente}', '{$msg}', '".time()."')"))
        return true;

      else
        return $this -> mysqli -> error;
    }

    public function link($msg, $link) {
      $this -> social($msg, $link);

      $msg = $this -> mysqli -> real_escape_string($msg);
      $link = $this -> mysqli -> real_escape_string($link);

      if($this -> mysqli -> query("INSERT INTO notifiche (idUtente, descrizione, data, link) VALUES ('{$this -> idUtente}', '{$msg}', '".time()."', '{$link}')"))
        return true;

      else
        return $this -> mysqli -> error;
    }

    private function social($msg, $link = null) {
      if($dizionario -> getValue('telegramBotName') !== false && $dizionario -> getValue('telegramBotName') !== null) {

        $sql = "SELECT * FROM socialNetworks WHERE idUtente = {$this -> idUtente} AND tipo = 'telegram' AND authCode IS NULL LIMIT 0, 1";
        $query = $this -> mysqli -> query($sql);

        $link = $GLOBALS['dizionario'] -> getValue('urlSito').$link;

        if($query) {
          if($query -> num_rows == 1) {

            $row = $query -> fetch_array();

            require_once(__DIR__.'/../vendor/autoload.php');

            $bot_api_key  = $GLOBALS['dizionario'] -> getValue('telegramKey');
            $bot_username = $GLOBALS['dizionario'] -> getValue('telegramBotName');

            $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

            $data = [
                'chat_id' => $row['idSocial'],
                'text' => $msg
            ];

            // Se il link esiste aggiungo un bottone per aprirlo
            if($link !== null) {
              $inline_keyboard = array("inline_keyboard" => array(array(array(
                'text' => 'Apri',
                'url' => $link
              ))));
              $data['reply_markup'] = $inline_keyboard;
            }

            return Longman\TelegramBot\Request::sendMessage($data);
          } // Controllo numero righe
        } // Controllo esecuzione query
      } // Controllo Telegram
    } // Metodo social
  } // Classe Notifiche
?>
