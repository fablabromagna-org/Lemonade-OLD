<?php
  class TemplateManager {
    public $mysqli;
    public $tableName;

    public function __construct($mysqli, $tableName) {
      $this -> mysqli = $mysqli;
      $this -> tableName = $tableName;
    }

    public function getTemplate($templateName, $variables = array()) {
      $sql = "SELECT * FROM {$this -> tableName} WHERE titolo = '{$templateName}' LIMIT 0, 1";
      $query = $this -> mysqli -> query($sql);

      if(!$query) {
        $console -> alert('Impossibile completare la richiesta! '.$this -> mysqli -> error, 0);
        return false;
      }

      if($query -> num_rows == 0)
        return null;

      $template = $query -> fetch_assoc();
      $template = base64_decode($template['sorgente']);

      foreach($variables as $key => $value)
        $template = str_replace("%%{$key}%%", $value, $template);

      return $template;
    }
  }
?>
