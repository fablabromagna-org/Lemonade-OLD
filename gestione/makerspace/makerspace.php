<?php
  require_once('../../inc/autenticazione.inc.php');

  if($autenticazione -> gestionePortale != 1)
    header('Location: /');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../../inc/header.inc.php');
    ?>
    <style type="text/css">
      .box { display: table; border: 1px solid <?php echo TEMA_BG_PRINCIPALE ?>; width: 100%; border-radius: 3px; margin-bottom: 15px; }
      .box > div { display: table-cell; vertical-align: top; padding: 10px; }
      .box > div:first-child { background: <?php echo TEMA_BG_PRINCIPALE ?>; width: 120px; font-size: 20px;  }

      #contenuto > h1 { margin-bottom: 20px; }

      form input, form select { margin-bottom: 10px; }
      form input:last-child, select { margin-bottom: 0; }

      #elencoTotem { margin-top: 10px; padding-top: 10px; border-top: 1px solid #cecece; }
    </style>
    <script type="text/javascript" src="/js/makerspace/modifica.js"></script>
    <script type="text/javascript" src="/js/totem/presenze/aggiungi.js"></script>
    <script type="text/javascript" src="/js/totem/presenze/elimina.js"></script>
    <script type="text/javascript" src="/js/totem/presenze/revoca.js"></script>
  </head>
  <body>
    <?php
      include_once('../../inc/nav.inc.php');

      $id = $mysqli -> real_escape_string(isset($_GET['id']) ? trim($_GET['id']) : '');

      // Estraggo il profilo dell'utente
      $sql = "SELECT * FROM makerspace WHERE id = '{$id}' AND eliminato = FALSE";

      if(!$query = $mysqli -> query($sql))
        echo '<div id="contenuto"><h1>Errore!</h1></div>';

      else {

        if($query -> num_rows != 1)
          echo '<div id="contenuto"><h1>Maker Space inesistente!</h1></div>';

        else {

          $makerspace = $query -> fetch_assoc();
    ?>
    <div id="contenuto">
      <h1><?php echo $makerspace['nome']; ?></h1>
      <div class="box">
        <div>Generale</div>
        <div>
          <form id="modificaGenerale">
            <input type="text" value="<?php echo $makerspace['nome'] ?>" id="nome" placeholder="Nome" style="display: block;" />
            <input type="hidden" id="idMakerSpace" value="<?php echo $makerspace['id']; ?>" />
            <input type="submit" value="Salva" id="salvaMakerSpace" />
          </form>
        </div>
      </div>
      <div class="box">
        <div>Gestione presenze</div>
        <div>
          <form id="modificaLimiti">
            <div>
              <label for="limiteGiornaliero">Limite giornaliero beggiate (0 = senza limiti)</label>
              <input type="text" value="<?php echo $makerspace['limiteBeggiateGiornaliere'] ?>" id="limiteGiornaliero" placeholder="Limite giornaliero" style="display: block; margin-top: 5px;" />
            </div>
            <div style="margin: 10px 0;">
              <label for="distanzaBeggiate">Distanza beggiate (in ore, 0 = distanza non richiesta)</label>
              <input type="text" value="<?php echo $makerspace['distanzaBeggiate'] ?>" id="distanzaBeggiate" placeholder="Distanza beggiate" style="display: block; margin-top: 5px;" />
            </div>
            <input type="submit" value="Salva" id="salvaLimiti" />
          </form>
        </div>
      </div>
      <div class="box">
        <div>Gestione totem</div>
        <div>
          <form id="aggiungiTotemForm">
            <label for="nomeTotem">Nome del nuovo totem</label>
            <input type="text" id="nomeTotem" placeholder="Nome totem" style="display: block; margin-top: 5px;" />
            <input type="submit" value="Aggiungi" id="aggiungiTotem" />
          </form>
          <div id="elencoTotem">
            <?php
              $sql = "SELECT * FROM totemPresenze WHERE idMakerSpace = '{$id}' ORDER BY id ASC";

              if($query = $mysqli -> query($sql)) {

                if($query -> num_rows > 0) {
            ?>
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nome e Token</th>
                  <th>Azioni</th>
                </tr>
              </thead>
              <tbody>
                <?php
                  // Stampo gli utenti
                  while($row = $query -> fetch_assoc()) {

                    echo "<tr>";
                    echo "<td><a href=\"/gestione/makerspace/makerspace.php?id={$row['id']}\">{$row['id']}</a></td>";
                    echo "<td><p>{$row['nome']}</p><p style=\"margin-top: 4px;\">Token: {$row['token']}</p></td>";
                    echo "<td><a onclick=\"revoca(this, {$row['id']})\">Revoca token</a><br /><a onclick=\"elimina(this, {$row['id']})\">Elimina</a></td>";
                    echo "</tr>";
                  }
                ?>
              </tbody>
            </table>
            <?php
                } else
                  echo "<p>Nessun totem di rilevazione delle presenze è stato registrato.</p>";

              } else {
                echo "<p>Impossibile comunicare con il database!</p>";
                $console -> alert('Impossibile comunicare con il database! '.$mysqli -> error, $autenticazione -> id);
              }
            ?>
          </div>
        </div>
      </div>
      <?php
          }
        }
      ?>
    </div>
    <?php
      include_once('../../inc/footer.inc.html');
    ?>
  </body>
</html>
