<?php
  class Console {
    private $mysqli;

    public function __construct($mysqli) {
      $this -> mysqli = $mysqli;
    }

    public function log($msg, $id) {
      $msg = $this -> mysqli -> real_escape_string($msg);
      $id = $this -> mysqli -> real_escape_string($id);

      if($this -> mysqli -> query("INSERT INTO log (idUtente, messaggio) VALUES ('{$id}', '{$msg}')")) {
        return true;
      } else
        return $this -> mysqli -> error;
    }

    public function warn($msg, $id) {
      $msg = $this -> mysqli -> real_escape_string($msg);
      $id = $this -> mysqli -> real_escape_string($id);

      $debug = $this -> mysqli -> real_escape_string(print_r(debug_backtrace(), true));

      @$this -> mysqli -> query("INSERT INTO log (livello, idUtente, messaggio, debug) VALUES ('WARN', '{$id}', '{$msg}', '{$debug}')");
    }

    public function alert($msg, $id) {
      $msg = $this -> mysqli -> real_escape_string($msg);
      $id = $this -> mysqli -> real_escape_string($id);

      $debug = $this -> mysqli -> real_escape_string(print_r(debug_backtrace(), true));

      @$this -> mysqli -> query("INSERT INTO log (livello, idUtente, messaggio, debug) VALUES ('ALERT', '{$id}', '{$msg}', '{$debug}')");
    }
  }
?>