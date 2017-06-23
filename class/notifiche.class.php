<?php
  class Notifiche {
    private $mysqli;
    public $numNotificheNonLette = false;
    private $idUtente = null;

    public function __construct($mysqli, $idUtente) {
      $this -> mysqli = $mysqli;

      $idUtente = $this -> mysqli -> real_escape_string($idUtente);
      $sql = "SELECT * FROM notifiche WHERE idUtente = '{$idUtente}' AND letto = '0'";

      $query = $this -> mysqli -> query($sql);
      if($query)
        $this -> numNotificheNonLette = $query -> num_rows;

      $this -> idUtente = $idUtente;
    }

    public function noLink($msg) {
      $msg = $this -> mysqli -> real_escape_string($msg);

      if($this -> mysqli -> query("INSERT INTO notifiche (idUtente, descrizione, data) VALUES ('{$this -> idUtente}', '{$msg}', '".time()."')"))
        return true;

      else
        return $this -> mysqli -> error;
    }

    public function link($msg, $link) {
      $msg = $this -> mysqli -> real_escape_string($msg);

      $link = $this -> mysqli -> real_escape_string($link);

      if($this -> mysqli -> query("INSERT INTO notifiche (idUtente, descrizione, data, link) VALUES ('{$this -> idUtente}', '{$msg}', '".time()."', '{$link}')"))
        return true;

      else
        return $this -> mysqli -> error;
    }
  }
?>