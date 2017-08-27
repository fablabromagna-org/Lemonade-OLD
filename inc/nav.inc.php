<?php
  $permessiTmp = $permessi -> whatCanHeDo($autenticazione -> id);
?>
<div id="headerInt">
  <div id="topBar">
    <div id="logoArea">
      <img src="/images/logo.png" alt="Logo" />
      <h3><?php echo NOME_SITO; ?></h3>
    </div>
    <div id="areaUtente">
      <div>
        <p><?php echo $autenticazione -> nome.' '.$autenticazione -> cognome; ?></p>
        <p><?php if($permessiTmp['visualizzareImpostazioniProprie']['stato']) { ?><a href="/account/impostazioni.php" style="color: #151515">Impostazioni</a><?php } ?>
          <?php if($permessiTmp['visualizzareNotificheProprie']['stato']) { ?> - <a href="/account/notifiche.php" style="color: #151515">Notifiche<?php if($notifiche -> numNotificheNonLette != false) echo " ({$notifiche -> numNotificheNonLette})"; ?></a></p><?php } ?>
      </div>
      <div>
        <i class="fa fa-user-circle-o" aria-hidden="true" style="font-size: 50px; color: #2b2b2b; margin-left: 15px;"></i>
      </div>
    </div>
  </div>
  <div id="nav">
    <a href="/dashboard.php" class="button">Dashboard</a>
    <?php if($permessiTmp['_bottoneGestione']) { ?>
      <a href="/gestione/" class="button">Gestione</a>
    <?php } ?>
    <a href="/logout.php" class="button">Esci</a>
  </div>
</div>
