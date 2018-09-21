<?php

error_reporting(E_ALL & ~E_NOTICE);

require_once(__DIR__ . '/../inc/config.inc.php');
require_once(__DIR__ . '/../inc/utilities.inc.php');
require_once(__DIR__ . '/../inc/permessi.inc.php');

$mysqli = new mysqli(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PWD, MYSQL_DB);

require_once(__DIR__ . '/SQLOperator/SQLOperator.class.php');
require_once(__DIR__ . '/SQLOperator/SQLLike.class.php');
require_once(__DIR__ . '/SQLOperator/SQLEquals.class.php');
require_once(__DIR__ . '/SQLOperator/SQLNotEquals.class.php');
require_once(__DIR__ . '/SQLOperator/SQLGreaterThan.class.php');
require_once(__DIR__ . '/SQLOperator/SQLGreaterThanEquals.class.php');
require_once(__DIR__ . '/SQLOperator/SQLLowerThan.class.php');
require_once(__DIR__ . '/SQLOperator/SQLLowerThanEquals.class.php');

require_once(__DIR__ . '/RisultatoRicerca.class.php');
require_once(__DIR__ . '/Comune.class.php');
require_once(__DIR__ . '/File.class.php');
require_once(__DIR__ . '/Firewall.class.php');
require_once(__DIR__ . '/Utente.class.php');
require_once(__DIR__ . '/Autenticazione.class.php');
require_once(__DIR__ . '/Fallimento.class.php');
require_once(__DIR__ . '/Log.class.php');
require_once(__DIR__ . '/Gruppo.class.php');
require_once(__DIR__ . '/Permesso.class.php');

session_start();