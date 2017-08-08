var editor
var salvataggioInCorso = false

function $(a) { return document.getElementById(a) }

window.addEventListener('DOMContentLoaded', function() {

  $('formAggiungi').addEventListener('submit', salva)

}) // Fine window.addEventListener DOMContentLoaded

function salva(e) {

  // Blocco l'esecuzione del form
  e.preventDefault()

  if(salvataggioInCorso)
    return

  salvataggioInCorso = true

  // Ricavo gli input
  var sorgente = editor.root.innerHTML
  var submit = $('salva')
  var id = $('id').value

  function errore(msg = 'Impossibile completare la richiesta!') {
    editor.enable()
    submit.disabled = false

    alert(msg)

    salvataggioInCorso = false
  }

  // Blocco tutti gli input
  editor.disable()
  submit.disabled = true

  // Invio i dati al server
  var xhr = new XMLHttpRequest()
  xhr.open('POST', '/ajax/modificaTemplate.php', true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

  xhr.send('sorgente='+encodeURIComponent(sorgente)+
           '&id='+encodeURIComponent(id))

  xhr.onreadystatechange = function() {

   if(xhr.readyState === 4 && xhr.status === 200) {

     var res = JSON.parse(xhr.response)

     if(res.errore === true)
      errore(res.msg)

     else {
       errore('Template modificato con successo!')
       location.href = '/gestione/generale/templates/'
     }


   } else if(xhr.readyState === 4)
    errore('Impossibile completare la richiesta!')
  }
}
