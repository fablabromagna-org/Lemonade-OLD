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
require_once(__DIR__ . '/SQLOperator/SQLOr.php');
require_once(__DIR__ . '/SQLOperator/SQLAnd.php');

require_once(__DIR__ . '/Data/DataGridFields.php');
require_once(__DIR__ . '/Data/TableHeader.php');
require_once(__DIR__ . '/Data/DataGrid.php');
require_once(__DIR__ . '/Data/HTMLDataGrid.php');
require_once(__DIR__ . '/Data/CSVDataGrid.php');

require_once(__DIR__ . '/Ricercabile.php');
require_once(__DIR__ . '/Ricerca.php');
require_once(__DIR__ . '/RisultatoRicerca.class.php');

require_once(__DIR__ . '/EntiLocali/EnteAreaVasta.php');
require_once(__DIR__ . '/EntiLocali/Regione.php');
require_once(__DIR__ . '/EntiLocali/Provincia.php');
require_once(__DIR__ . '/EntiLocali/Comune.class.php');

require_once(__DIR__ . '/File.class.php');
require_once(__DIR__ . '/Firewall.class.php');
require_once(__DIR__ . '/Utente.class.php');
require_once(__DIR__ . '/Autenticazione.class.php');
require_once(__DIR__ . '/OggettoRegistro.class.php');
require_once(__DIR__ . '/Log.class.php');
require_once(__DIR__ . '/Gruppo.class.php');
require_once(__DIR__ . '/Permesso.class.php');
require_once(__DIR__ . '/Email/Configuration.php');
require_once(__DIR__ . '/Email/Email.php');
require_once(__DIR__ . '/Email/TemplateEmail.php');
require_once(__DIR__ . '/Email/Sender.php');
require_once(__DIR__ . '/Scuola.php');
require_once(__DIR__ . '/RelazioneScolastica.php');
require_once(__DIR__ . '/Tag.php');

session_start();
