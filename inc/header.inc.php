<?php
  require_once('carica.inc.php');
  $autenticazione = new Autenticazione($mysqli);
?>
<title><?php echo NOME_SITO; ?></title>
<link type="text/css" rel="stylesheet" media="screen" href="/css/generale.css" />
<meta charset="utf-8">
<meta name="theme-color" content="<?php echo TEMA_BG_PRINCIPALE ?>">
<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.5">

<script type="text/javascript" src="https://use.fontawesome.com/cf689c1a7e.js"></script>

<script type="text/javascript" src="/js/cookie.js"></script>

<style type="text/css">
  body { color: <?php echo TEMA_COL_TESTO_PRINCIPALE; ?> !important; }
  #header div { background: <?php echo TEMA_BG_PRINCIPALE ?> !important; }
  #footer { background: <?php echo TEMA_BG_PRINCIPALE ?> !important; }

  <?php
    // CSS per utenti autenticati
    if($autenticazione -> isLogged()) {
  ?>
    #topBar { background: <?php echo TEMA_BG_PRINCIPALE ?> !important; }
    #nav { background: <?php echo TEMA_BG_PRINCIPALE_LOGGED ?> !important; }
    #footer { background: <?php echo TEMA_BG_PRINCIPALE_LOGGED ?> !important; color: <?php echo TEMA_COL_TESTO_LOGGED ?> !important; }

  <?php
    }
  ?>

  .button, input[type='submit'] { border: 1px solid <?php echo TEMA_BG_BTN_NOHOVER; ?> !important; transition: .2s background, color !important; background: <?php echo TEMA_BG_BTN_NOHOVER; ?> !important; color: <?php echo TEMA_COL_BTN_NOHOVER; ?> !important; }
  .button:active, input[type='submit']:active { transition: .2s background, color !important; background: <?php echo TEMA_BG_BTN_HOVER; ?> !important; color: <?php echo TEMA_COL_BTN_HOVER; ?> !important; }
  .button.disabled, input[type='submit'][disabled] { border: 2px dashed <?php echo TEMA_COL_BTN_BORDER_DISABLED; ?> !important; background: #fff; background: <?php echo TEMA_BG_BTN_HOVER; ?> !important; color: <?php echo TEMA_COL_BTN_HOVER; ?> !important; }
  input[type='text']:focus, input[type='text']:hover, input[type='email']:focus, input[type='email']:hover, input[type='password']:focus, input[type='password']:hover, select:hover, select:focus { border-color: <?php echo TEMA_BG_BTN_NOHOVER; ?> !important; }
</style>