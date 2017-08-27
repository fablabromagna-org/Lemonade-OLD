<?php
  class Autenticazione {

    private $mysqli;

    // Memorizzo i dati dell'utente
    public $nome;
    public $cognome;
    public $email;
    public $password;
    public $sospeso;
    public $gestione;
    public $ipRegistrazione;
    public $categoria;
    public $id;
    public $hashSessione;

    // Carico il database in memoria
    public function __construct($db) {
      $this -> mysqli = $db;
    }

    // Controllo che l'accesso sia stato effettuato
    public function isLogged() {

      if(!isset($_COOKIE[COOKIE_NAME]))
        return false;

      else if(strlen($_COOKIE[COOKIE_NAME]) < 34 || strpos($_COOKIE[COOKIE_NAME], '-') === false)
        return false;

      else {

        // Divido ID e Hash
        $stringa = explode('-', $_COOKIE[COOKIE_NAME]);
        $id = $this -> mysqli -> real_escape_string($stringa[0]);
        $hash = $this -> mysqli -> real_escape_string($stringa[1]);

        // Creo una query SQL
        $sql = "SELECT * FROM utenti, sessioni WHERE sessioni.idUtente = '{$id}' AND utenti.id = '{$id}' AND utenti.codiceAttivazione = '0' AND sessioni.hashSessione = '$hash' AND sessioni.scadenza >= '".time()."'";

        // Eseguo la query
        if($query = $this -> mysqli -> query($sql)) {

          // La sessione esiste
          if($query -> num_rows === 1) {

            $dati = $query -> fetch_array();

            $this -> nome = $dati['nome'];
            $this -> cognome = $dati['cognome'];
            $this -> email = $dati['email'];
            $this -> password = $dati['password'];
            $this -> sospeso = $dati['sospeso'];
            $this -> ipRegistrazione = $dati['ipRegistrazione'];
            $this -> id = $dati['id'];
            $this -> hashSessione = $hash;

            // Estraggo tutte le categorie degli utenti
            $sql = "SELECT * FROM categorieUtenti";

            $categorieUtenti = array();

            if($query = $this -> mysqli -> query($sql)) {

              while($key = $query -> fetch_array(MYSQLI_ASSOC)) {
                $categorieUtenti[$key['id']] = $key['nome'];
              }

            } else {
              echo 'Impossibile estrarre le categorie degli utenti.';
              exit();
            }

            // Memorizzo la categoria dell'utente
            $this -> categoria = array((int)$dati['categoria'], $categorieUtenti[(int)$dati['categoria']]);

            return true;

          // La sessione non esiste
          } else {

            // Elimino il cookie
            setcookie(COOKIE_NAME, '', 0, '/');

            return false;
          }

        // Errore nell'esecuzione della query
        } else {

          // Elimino il cookie
          setcookie(COOKIE_NAME, '', 0, '/');

          return false;
        }
      }
    }
  }
?>
