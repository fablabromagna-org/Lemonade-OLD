<?php
  class Permessi {

    public $mysqli;

    public function __construct($mysqli) {
      $this -> mysqli = $mysqli;
    }

    // Metodo per estrarre tutti i permessi di un utente
    public function getUser($id) {

      $sql = "SELECT * FROM elencoPermessi A LEFT JOIN permessi B ON A.nome = B.permesso AND B.idGruppoUtente = {$id} AND B.gruppo IS FALSE";
      $query = $this -> mysqli -> query($sql);

      if(!$query)
        return false;

      $res = array();

      while($row = $query -> fetch_assoc()) {
        if($row['stato'] != null)
          $tmp = array( 'stato' => (bool)$row['stato'] );

        else
          $tmp = array( 'stato' => null );

        $tmp['descrizione'] = $row['descrizione'];
        $tmp['default'] = (bool)$row['valoreDefault'];
        $tmp['bottoneGestione'] = (bool)$row['bottoneGestione'];

        $res[$row['nome']] = $tmp;
      }

      return $res;
    }

    // Metodo per estrarre tutti i permessi di un gruppo
    public function getGroup($id) {

      $sql = "SELECT * FROM elencoPermessi A LEFT JOIN permessi B ON A.nome = B.permesso AND B.idGruppoUtente = {$id} AND B.gruppo IS TRUE ORDER BY A.nome ASC";
      $query = $this -> mysqli -> query($sql);

      if(!$query)
        return false;

      $res = array();

      while($row = $query -> fetch_assoc()) {

        if($row['stato'] != null)
          $tmp = array( 'stato' => (bool)$row['stato'] );

        else
          $tmp = array( 'stato' => (bool)$row['valoreDefault'] );

        $tmp['descrizione'] = $row['descrizione'];
        $tmp['default'] = (bool)$row['valoreDefault'];
        $tmp['bottoneGestione'] = (bool)$row['bottoneGestione'];

        $res[$row['nome']] = $tmp;
      }

      return $res;
    }

    // Metodo per vedere tutti i permessi di un utente
    // Calcolati anche in base a quelli del gruppo a cui appartiene
    public function whatCanHeDo($id) {

      $sql = "SELECT * FROM utenti WHERE id = {$id} LIMIT 0, 1";
      $query = $this -> mysqli -> query($sql);

      if(!$query)
        return false;

      $gruppo = $query -> fetch_assoc()['categoria'];

      $permessiUtente = $this -> getUser($id);
      $permessiGruppo = $this -> getGroup($gruppo);

      $bottoneGestione = false;
      $tmp = array();

      foreach ($permessiUtente as $key => $value) {

        $tmp[$key] = $value;

        if($value['stato'] === null)
          $tmp[$key]['stato'] = $permessiGruppo[$key]['stato'];

        if($tmp[$key]['bottoneGestione'] && $tmp[$key]['stato'])
          $bottoneGestione = true;
      }

      $tmp['_bottoneGestione'] = $bottoneGestione;

      return $tmp;
    }
  }
?>
