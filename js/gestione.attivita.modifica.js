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
  var descrizione = editor.root.innerHTML
  var giornoStart = $('giornoStart')
  var meseStart = $('meseStart')
  var annoStart = $('annoStart')
  var oraStart = $('oraStart')
  var minutoStart = $('minutoStart')
  var giornoEnd = $('giornoEnd')
  var meseEnd = $('meseEnd')
  var annoEnd = $('annoEnd')
  var oraEnd = $('oraEnd')
  var minutoEnd = $('minutoEnd')
  var fabcoin = $('fabcoin')
  var submit = $('salva')
  var id = $('id').value

  function errore(msg = 'Errore sconosciuto!') {
    editor.enable()
    giornoStart.disabled = false
    meseStart.disabled = false
    annoStart.disabled = false
    oraStart.disabled = false
    minutoStart.disabled = false
    giornoEnd.disabled = false
    meseEnd.disabled = false
    annoEnd.disabled = false
    oraEnd.disabled = false
    minutoEnd.disabled = false
    fabcoin.disabled = false
    submit.disabled = false

    alert(msg)

    salvataggioInCorso = false
  }

  // Blocco tutti gli input
  editor.disable()
  giornoStart.disabled = true
  meseStart.disabled = true
  annoStart.disabled = true
  oraStart.disabled = true
  minutoStart.disabled = true
  giornoEnd.disabled = true
  meseEnd.disabled = true
  annoEnd.disabled = true
  oraEnd.disabled = true
  minutoEnd.disabled = true
  fabcoin.disabled = true
  submit.disabled = true

  // Invio i dati al server
  var xhr = new XMLHttpRequest()
  xhr.open('POST', '/ajax/attivita/modifica.php', true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

  xhr.send('descrizione='+encodeURIComponent(descrizione)+
           '&giornoInizio='+encodeURIComponent(giornoStart.value)+
           '&meseInizio='+encodeURIComponent(meseStart.value)+
           '&annoInizio='+encodeURIComponent(annoStart.value)+
           '&oraInizio='+encodeURIComponent(oraStart.value)+
           '&minutoInizio='+encodeURIComponent(minutoStart.value)+
           '&giornoFine='+encodeURIComponent(giornoEnd.value)+
           '&meseFine='+encodeURIComponent(meseEnd.value)+
           '&annoFine='+encodeURIComponent(annoEnd.value)+
           '&oraFine='+encodeURIComponent(oraEnd.value)+
           '&minutoFine='+encodeURIComponent(minutoEnd.value)+
           '&fabcoin='+encodeURIComponent(fabcoin.value)+
           '&id='+encodeURIComponent(id))

  xhr.onreadystatechange = function() {

   if(xhr.readyState === 4 && xhr.status === 200) {

     var res = JSON.parse(xhr.response)

     if(res.errore === true)
      errore(res.msg)

     else {
       errore('Attività modificata con successo!')
       location.href = '/gestione/attivita/attivita.php?id='+id
     }


   } else if(xhr.readyState === 4)
    errore('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
  }
}