;(function() {

  window.addEventListener('DOMContentLoaded', function() {
    document.getElementById('salva').addEventListener('submit', salva)
    document.getElementById('rimuovi').addEventListener('click', rimuovi)
  })

  var salvataggioInCorso = false
  function salva(e) {

    e.preventDefault()

    if(salvataggioInCorso)
      return

    salvataggioInCorso = true

    bottoneSalva.disabled = true
    bottoneSalva.value = 'Salvataggio...'

    var cf = document.getElementById('cf')
    var id = document.getElementById('idUtente')

    // Invio il token al server
    var xhr = new XMLHttpRequest()
    xhr.open('POST', '/ajax/utente/anagrafiche/codiceFiscale.php', true)
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    xhr.send('id=' + encodeURIComponent(id.value) + '&cf=' + encodeURIComponent(cf.value))

    function errore(msg) {
      salvataggioInCorso = false
      document.getElementById('errore').innerHTML = msg

      bottoneSalva.disabled = false
      bottoneSalva.value = 'Salva'
    }

    xhr.onreadystatechange = function() {

      if(xhr.readyState === 4 && xhr.status === 200) {

        try {
          var res = JSON.parse(xhr.response)
        } catch(e) {
          errore('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
        }

        if(res.errore === true)
          errore(res.msg)

        else
          window.close();


      } else if(xhr.readyState === 4)
        errore('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
    }
  }

  var eliminazioneInCorso = false
  function rimuovi(e) {

    e.preventDefault()

    if(eliminazioneInCorso)
      return

    eliminazioneInCorso = true

    document.getElementById('rimuovi').innerHTML = 'Rimozione in corso...'

    var id = document.getElementById('idUtente')

    // Invio il token al server
    var xhr = new XMLHttpRequest()
    xhr.open('POST', '/ajax/utente/anagrafiche/codiceFiscale.php', true)
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    xhr.send('id=' + encodeURIComponent(id.value))

    function errore(msg) {
      eliminazioneInCorso = false
      document.getElementById('errore').innerHTML = msg
      document.getElementById('rimuovi').innerHTML = 'Rimuovi data di nascita'
    }

    xhr.onreadystatechange = function() {

      if(xhr.readyState === 4 && xhr.status === 200) {

        try {
          var res = JSON.parse(xhr.response)
        } catch(e) {
          errore('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
        }

        if(res.errore === true)
          errore(res.msg)

        else
          window.close();


      } else if(xhr.readyState === 4)
        errore('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
    }
  }
})()
