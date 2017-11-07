;(function() {

  window.addEventListener('DOMContentLoaded', function() {
    document.getElementById('ricerca').addEventListener('keyup', cerca)
    document.getElementById('salva').addEventListener('submit', salva)
    document.getElementById('rimuovi').addEventListener('click', rimuovi)
  })

  function cerca() {
    var xhr = new XMLHttpRequest()
    xhr.open('POST', '/ajax/utente/anagrafiche/ricercaLuogo.php', true)
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    xhr.send('luogo=' + encodeURIComponent(document.getElementById('ricerca').value))

    xhr.onreadystatechange = function() {

      if(xhr.readyState === 4 && xhr.status === 200) {

        try {
          var res = JSON.parse(xhr.response)
        } catch(e) {
          document.getElementById('elenco').innerHTML = ''
        }

        var s = ''
        for(var i = 0; i < res.length; i++)
          s += '<li><a onclick="seleziona(\'' + res[i].belfiore + '\', \'' + res[i].comune + '\')">' + res[i].comune + ' (' + res[i].belfiore + ')</a></li>'

        document.getElementById('elenco').innerHTML = s

      } else if(xhr.readyState === 4)
        errore('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
    }
  }

  var salvataggioInCorso = false
  function salva(e) {

    e.preventDefault()

    if(salvataggioInCorso)
      return

    salvataggioInCorso = true

    bottoneSalva.disabled = true
    bottoneSalva.value = 'Salvataggio...'

    var belfiore = document.getElementById('luogoNascita')
    var id = document.getElementById('idUtente')

    // Invio il token al server
    var xhr = new XMLHttpRequest()
    xhr.open('POST', '/ajax/utente/anagrafiche/impostaLuogoNascita.php', true)
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    xhr.send('id=' + encodeURIComponent(id.value) + '&luogo=' + encodeURIComponent(belfiore.value))

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
    xhr.open('POST', '/ajax/utente/anagrafiche/impostaLuogoNascita.php', true)
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

function seleziona(belfiore, comune) {

  document.getElementById('elenco').innerHTML = ''
  document.getElementById('ricerca').value = comune
  document.getElementById('luogoNascita').value = belfiore
}
