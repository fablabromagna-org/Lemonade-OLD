<?php
  class Dizionario {

    public $mysqli;
    public $tableName;

    public function __construct($mysqli, $tableName) {
      $this -> mysqli = $mysqli;
      $this -> tableName = $tableName;
    }

    public function getValue($key) {
      $key = $this -> mysqli -> real_escape_string($key);

      $sql = "SELECT * FROM {$this -> tableName} WHERE chiave = '{$key}'";
      $query = $this -> mysqli -> query($sql);

      if($query) {
        if($query -> num_rows != 1)
          return null;

        else {
          $row = $query -> fetch_assoc();
          return $row['valore'];
        }
      } else
        return false;
    }

    public function setValue($key, $value) {
      $key = trim($this -> mysqli -> real_escape_string($key));
      $value = $this -> mysqli -> real_escape_string($value);

      if($key == '')
        return false;

      $sql = "UPDATE {$this -> tableName} SET valore = '{$value}' WHERE chiave = '{$key}'";
      $query = $this -> mysqli -> query($sql);

      if($query)
        return true;

      else
        return false;
    }
  }
?>
