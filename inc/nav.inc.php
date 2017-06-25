<div id="headerInt">
  <div id="topBar">
    <div id="logoArea">
      <img src="/images/logo.png" alt="Logo" />
      <h3><?php echo NOME_SITO; ?></h3>
    </div>
    <div id="areaUtente">
      <div>
        <p><?php echo $autenticazione -> nome.' '.$autenticazione -> cognome; ?></p>
        <p><a href="/account/impostazioni.php" style="color: #151515">Impostazioni</a> - <a href="/account/notifiche.php" style="color: #151515">Notifiche<?php if($notifiche -> numNotificheNonLette != false) echo " ({$notifiche -> numNotificheNonLette})"; ?></a></p>
      </div>
      <div>
        <i class="fa fa-user-circle-o" aria-hidden="true" style="font-size: 50px; color: #2b2b2b; margin-left: 15px;"></i>
      </div>
    </div>
  </div>
  <div id="nav">
    <a href="/dashboard.php" class="button">Dashboard</a>
    <?php if($autenticazione -> gestionePortale== 1) { ?>
      <a href="/gestione/" class="button">Gestione</a>
    <?php } ?>
    <a href="/logout.php" class="button">Esci</a>
  </div>
</div>