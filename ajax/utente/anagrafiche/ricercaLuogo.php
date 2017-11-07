<?php
  require_once('../../../inc/carica.inc.php');

  $autenticazione = new Autenticazione($mysqli);

  header('Content-Type: application/json');

  $luogo = $mysqli -> real_escape_string(isset($_POST['luogo']) ? trim($_POST['luogo']) : '');

  function stampaErrore() {
    echo '[]';
    exit();
  }

  // Controllo che l'utente abbia effettuato l'accesso
  if(!$autenticazione -> isLogged())

    // L'utente non ha effettuato l'accesso
    stampaErrore('Non hai effettuato l\'accesso!');

  // Controllo che l'utente abbia i permessi per effettuare la modifica
  else if(!$permessi -> whatCanHeDo($autenticazione -> id)['modificaAnagrafiche']['stato'])

    // L'utente non ha i permessi
    stampaErrore('Non sei autorizzato ad accedere ai dati!');

  // L'utente ha effettuato l'accesso ed è autorizzato
  else {

    if($luogo === "")
      stampaErrore();

    $luogo = mb_strtoupper($luogo);

    $sql = "SELECT * FROM comuni WHERE comune LIKE '{$luogo}%' OR stato LIKE '{$luogo}%' OR codiceCatastale LIKE '{$luogo}%' ORDER BY comune ASC, stato ASC";
    $query = $mysqli -> query($sql);

    if($query) {
      $s = '[';

      while($row = $query -> fetch_assoc()) {

        if(strlen($s) != 1)
          $s .= ',';

        if($row['stato'] == null)
          $s .= '{ "comune": "'.$row['comune'].'", "belfiore": "'.$row['codiceCatastale'].'" }';

        else
          $s .= '{ "comune": "'.$row['stato'].'", "belfiore": "'.$row['codiceCatastale'].'" }';
      }

      $s .= ']';

      echo $s;

    } else {
      $console -> alert("Impossibile richiedere i comuni! ".$mysqli -> error, $autenticazione -> id);
      stampaErrore();
    }
  }
?>
