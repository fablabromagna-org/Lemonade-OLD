<?php

  @include_once('devConfig.inc.php');

  // E-Mail
  define('MAILGUN_API_KEY', getenv('MAILGUN_API_KEY'));
  define('MITTENTE_EMAIL', getenv('MAILGUN_FROM_NAME'));
  define('INDIRIZZO_MITTENTE', getenv('MAILGUN_FROM'));
  define('DOMINIO_EMAIL_MAILGUN', getenv('MAILGUN_DOMAIN'));

  // Login
  define('COOKIE_NAME', 'flrAuth');

  // MySQL
  define('MYSQL_USERNAME', getenv('MYSQL_USERNAME'));
  define('MYSQL_PASSWORD', getenv('MYSQL_PWD'));
  define('MYSQL_DATABASE', getenv('MYSQL_DB'));
  define('MYSQL_HOST', getenv('MYSQL_HOST'));

  // Il programma deve mostrare gli errori di PHP?
  define('PHP_MOSTRA_ERRORI', (bool)getenv('PHP_ERRORS'));

  // Nome del sito e url
  define('NOME_SITO', getenv('SITE_NAME'));
  define('URL_SITO', getenv('SITE_URL'));

  // Tema
  define('TEMA_BG_PRINCIPALE', '#cecece'); // Colore sfondo home e footer e topbar
  define('TEMA_COL_TESTO_PRINCIPALE', '#151515'); // Colore del testo
  define('TEMA_BG_PRINCIPALE_LOGGED', '#2b2b2b'); // Sfondo footer e navigazione loggato
  define('TEMA_COL_TESTO_LOGGED', '#fff'); // Testo loggato footer e navigazione no link
  define('TEMA_COL_BTN_NOHOVER', '#fff'); // Testo no hover bottoni
  define('TEMA_COL_BTN_HOVER', 'rgb(0, 98, 217)'); // Testo hover bottoni
  define('TEMA_BG_BTN_NOHOVER', 'rgb(0, 98, 217)'); // Sfondo no hover bottoni
  define('TEMA_BG_BTN_HOVER', '#fff'); // Sfondo hover bottoni
  define('TEMA_COL_BTN_BORDER_DISABLED', '#cecece'); // Bordo bottoni disabilitati
?>