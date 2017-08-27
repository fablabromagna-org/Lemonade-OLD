<?php
  require_once(__DIR__.'/../class/gestioneAuth.class.php');
  require_once(__DIR__.'/../class/logs.class.php');
  require_once(__DIR__.'/../class/notifiche.class.php');
  require_once(__DIR__.'/../class/paginator.class.php');
  require_once(__DIR__.'/../class/dizionario.class.php');
  require_once(__DIR__.'/../class/templateManager.class.php');
  require_once(__DIR__.'/../class/permessi.class.php');
  require_once('mysqli.inc.php');

  $console = new Console($mysqli);
  $dizionario = new Dizionario($mysqli, 'dizionario');
  $templateManager = new TemplateManager($mysqli, 'templates');
  $permessi = new Permessi($mysqli);
?>
