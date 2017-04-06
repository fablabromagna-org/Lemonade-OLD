<div id="headerInt">
  <div id="topBar">
    <div id="logoArea">
      <img src="/images/logo.png" alt="Logo" />
      <h3><?php echo NOME_SITO; ?></h3>
    </div>
    <div id="areaUtente">
      <div>
        <p><?php echo $autenticazione -> nome.' '.$autenticazione -> cognome; ?></p>
      </div>
      <div>
        <img src="<?php echo $autenticazione -> getPicUrl(); ?>" alt="Immagine del profilo" />
      </div>
    </div>
  </div>
  <div id="nav">
    <a href="/dashboard.php" class="button">Dashboard</a>
    <a href="/account/impostazioni.php" class="button">Impostazioni</a>
    <?php if($autenticazione -> gestionePortale== 1) { ?>
      <a href="/gestione/" class="button">Gestione</a>
    <?php } ?>
    <a href="/logout.php" class="button">Esci</a>
  </div>
</div>