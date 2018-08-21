<?php

define('MYSQL_HOST', 'localhost');
define('MYSQL_DB', 'lemonade');
define('MYSQL_USERNAME', 'root');
define('MYSQL_PWD', 'root');

define('DEBUG', false);
define('AUTH_COOKIE_NAME', 'lemonade_auth_cookie');

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

// Fuso orario
date_default_timezone_set('Europe/Rome');