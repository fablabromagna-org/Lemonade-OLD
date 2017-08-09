<?php
  require_once('../../../inc/autenticazione.inc.php');

  if($autenticazione -> gestionePortale!= 1)
    header('Location: /');
?>
<!DOCTYPE html>
<html lang="it">
  <head>
    <?php
      require_once('../../../inc/header.inc.php');
    ?>
    <script type="text/javascript" src="/js/template.js"></script>
  </head>
  <body>
    <?php
      include_once('../../../inc/nav.inc.php');

      $id = $mysqli -> real_escape_string(isset($_GET['id']) ? trim($_GET['id']) : '');

      $sql = "SELECT * FROM templates WHERE id = '{$id}'";

      $query = $mysqli -> query($sql);

      if(!$query) {

        $console -> alert('Impossibile eseguire la query di modifica dell\'attività', $autenticazione -> id);
    ?>
        <div id="contenuto">
          <h1>Impossibile completare la richiesta!</h1>
        </div>
    <?php
      } else if($query -> num_rows != 1) {
    ?>
      <div id="contenuto">
        <h1>Template inesistente!</h1>
      </div>
    <?php
      } else {

        $row = $query -> fetch_array();
    ?>
      <div id="contenuto">
        <h1>Modifica un template</h1>
        <form id="formAggiungi" style="margin-top: 20px;">

          <link href="https://cdn.quilljs.com/1.2.3/quill.snow.css" rel="stylesheet">
          <script src="https://cdn.quilljs.com/1.2.3/quill.js"></script>
          <div id="editor"></div>
          <script>
            editor = new Quill('#editor', {
              theme: 'snow'
            })
            editor.clipboard.dangerouslyPasteHTML('<?php echo base64_decode($row['sorgente']); ?>')
          </script>
          <div style="margin-top: 20px;">
            <input type="submit" id="salva" value="Salva il template" />
          </div>
          <input type="hidden" id="id" value="<?php echo $_GET['id']; ?>" />
        </form>
      </div>
    <?php
      }

      include_once('../../../inc/footer.inc.html');
    ?>
  </body>
</html>
