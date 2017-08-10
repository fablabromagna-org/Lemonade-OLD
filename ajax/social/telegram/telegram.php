<?php
  require_once('../../../inc/carica.inc.php');
  require_once('../../../vendor/autoload.php');

  $bot_api_key  = $dizionario -> getValue('telegramKey');
  $bot_username = $dizionario -> getValue('telegramBotName');

  use Longman\TelegramBot\Commands\SystemCommand;
  use Longman\TelegramBot\Request;

  try {
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    
    $telegram -> addCommandsPaths([__DIR__."/comandi/"]);
    $telegram -> handle();

  } catch (Longman\TelegramBot\Exception\TelegramException $e) {
    echo $e -> getMessage();
  }
?>
