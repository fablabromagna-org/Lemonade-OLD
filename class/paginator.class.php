<?php
  class Paginator {

    public $mysqli;
    private $query;
    private $offset;
    private $page;
    public $result;

    public function __construct($mysqli, $query, $page, $offset) {
      $this -> mysqli = $mysqli;
      $this -> query = $query;
      $this -> offset = (int)$offset;
      $this -> page = (int)$page;

      $pagina = ($this -> page - 1) * $this -> offset;
      $this -> result = $this -> mysqli -> query("{$query} LIMIT {$pagina},{$this -> offset}");
    }

    public function getButtons($qsName) {
      $tmp = "";

      // Previus page
      if($this -> page != 1)
        $tmp .= "<a href=\"".$this -> generateLink($this -> page - 1, $qsName)."\" class=\"button\" style=\"margin-right: 5px;\">".($this -> page - 1)." <i class=\"fa fa-arrow-circle-o-left\" aria-hidden=\"true\"></i></a>";

      // Current page
      $tmp .= "<a class=\"button\" style=\"margin-right: 5px;\">".$this -> page."</a>";

      // Next page
      $next = $this -> page * $this -> offset;
      $query = $this -> mysqli -> query("{$this -> query} LIMIT {$next},{$this -> offset}");

      if($query)
        if($query -> num_rows > 0)
          $tmp .= "<a href=\"".$this -> generateLink($this -> page + 1, $qsName)."\" class=\"button\"><i class=\"fa fa-arrow-circle-o-right\" aria-hidden=\"true\"></i> ".($this -> page + 1)."</a>";

      return $tmp;
    }

    private function generateLink($page, $qsName) {

      if($_SERVER['QUERY_STRING'] == '')
        return strtok($_SERVER['REQUEST_URI'], '?').'?'.urlencode($qsName).'='.$page;

      $qs = explode('&', $_SERVER['QUERY_STRING']);
      $tmp = array();

      $found = false;

      foreach($qs as $value) {
        $value = explode('=', $value);
        $tmp[$value[0]] = $value[1];
      }

      $qs = $tmp;

      foreach ($qs as $key => $value) {
        if($qs == $qsName) {
          $found = true;
          $value = $page;
        }
      }

      if(!$found)
        $qs[$qsName] = $page;

      $tmp = array();
      $i = 0;
      foreach ($qs as $key => $value) {
        $tmp[$i] = urlencode($key).'='.urlencode($value);
        $i++;
      }

      $qs = $tmp;

      $qs = implode('&', $qs);

      return strtok($_SERVER['REQUEST_URI'], '?').'?'.$qs;
    }
  }
?>