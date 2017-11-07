;(function() {

  window.addEventListener('DOMContentLoaded', function() {
    document.getElementById('salva').addEventListener('submit', salva)
  })

  var salvataggioInCorso = false
  function salva(e) {

    e.preventDefault()

    if(salvataggioInCorso)
      return

    salvataggioInCorso = true

    bottoneSalva.disabled = true
    bottoneSalva.value = 'Salvataggio...'

    var sesso = document.getElementById('sesso')
    var id = document.getElementById('idUtente')

    // Invio il token al server
    var xhr = new XMLHttpRequest()
    xhr.open('POST', '/ajax/utente/anagrafiche/sesso.php', true)
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    xhr.send('id=' + encodeURIComponent(id.value) + '&sesso=' + encodeURIComponent(sesso.value))

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
})()
