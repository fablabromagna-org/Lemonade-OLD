var aggiuntaInCorso = false

window.addEventListener('DOMContentLoaded', function() {
  document.getElementById('aggiungiForm').addEventListener('submit', aggiungi)
})

// Funzione per inviare al server una richiesta di
// Aggiunta di una coppia di chiavi/valore
function aggiungi(e) {

  // Annullo l'evento
  e.preventDefault()

  // Controllo che non ci sia già un'aggiunta in corso
  if(aggiuntaInCorso)
    return

  aggiuntaInCorso = true

  // Ricavo i valori
  var chiave = document.getElementById('chiave')
  var valore = document.getElementById('valore')

  // Funzione per far tornare il form allo stato originale
  // E aprire un popup di errore
  function stampaErrore(errore = 'Impossibile completare la richiesta!') {
    alert(errore)

    chiave.disabled = false
    valore.disabled = false

    aggiuntaInCorso = false
  }

  // Controllo che la chiave non sia vuota
  if(chiave.value.trim() === '')
    stampaErrore('Devi inserire una chiave valida!')

  var xhr = new XMLHttpRequest()
  xhr.open('POST', '/ajax/dizionario/aggiungi.php', true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.send('chiave=' + encodeURIComponent(chiave.value) + '&valore=' + encodeURIComponent(valore.value))

  xhr.onreadystatechange = function() {

    if(xhr.readyState === 4 && xhr.status === 200) {

      var res = JSON.parse(xhr.response)

      if(res.errore === true)
        stampaErrore(res.msg)

      else {
        alert('Elemento aggiunto con successo!')
        location.href = location.href
      }


    } else if(xhr.readyState === 4)
      stampaErrore('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
  }
}

var eliminazioneInCorso = false

// Funzione per inviare al server una richiesta di
// Rimozione di un elemento
function rimuovi(id) {

  // Controllo che non ci sia già un'aggiunta in corso
  if(eliminazioneInCorso)
    return

  eliminazioneInCorso = true

  // Funzione per far tornare il form allo stato originale
  // E aprire un popup di errore
  function stampaErrore(errore = 'Impossibile completare la richiesta!') {
    alert(errore)

    eliminazioneInCorso = false
  }

  var xhr = new XMLHttpRequest()
  xhr.open('POST', '/ajax/dizionario/elimina.php', true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.send('id='+encodeURIComponent(id))

  xhr.onreadystatechange = function() {

    if(xhr.readyState === 4 && xhr.status === 200) {

      var res = JSON.parse(xhr.response)

      if(res.errore === true)
        stampaErrore(res.msg)

      else {
        alert('Elemento eliminato con successo!')
        location.href = location.href
      }


    } else if(xhr.readyState === 4)
      stampaErrore('Impossibile completare la richiesta!')
  }
}

var salvataggioInCorso = false

// Funzione per inviare al server una richiesta di
// Rimozione di un elemento
function salva(id) {

  // Controllo che non ci sia già un'aggiunta in corso
  if(salvataggioInCorso)
    return

  salvataggioInCorso = true

  // Funzione per far tornare il form allo stato originale
  // E aprire un popup di errore
  function stampaErrore(errore = 'Impossibile completare la richiesta!') {
    alert(errore)

    salvataggioInCorso = false
  }

  var xhr = new XMLHttpRequest()
  xhr.open('POST', '/ajax/dizionario/modifica.php', true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.send('id='+encodeURIComponent(id) + '&valore='+encodeURIComponent(document.getElementById('dizionario-id' + id).value))

  xhr.onreadystatechange = function() {

    if(xhr.readyState === 4 && xhr.status === 200) {

      var res = JSON.parse(xhr.response)

      if(res.errore === true)
        stampaErrore(res.msg)

      else {
        alert('Elemento aggiornato con successo!')
        location.href = location.href
      }


    } else if(xhr.readyState === 4)
      stampaErrore('Impossibile completare la richiesta!')
  }
}
