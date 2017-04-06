function elimina(id) {

  var xhr = new XMLHttpRequest()

  xhr.open('POST', '/ajax/eliminaMessaggioDashboard.php', true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.send('id='+encodeURIComponent(id))

  xhr.onreadystatechange = function() {

    if(xhr.readyState === 4 && xhr.status === 200) {

      var res = JSON.parse(xhr.response)

      if(res.errore === true)
        alert(res.msg)

      else
        location.href = location.href

    } else if(xhr.readyState === 4)
      alert('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')

  }

}

window.addEventListener('DOMContentLoaded', function() {

  document.getElementById('aggiungiForm').addEventListener('submit', function(e) {

    e.preventDefault()

    var xhr = new XMLHttpRequest()

    function $(a) { return document.getElementById(a) }

    xhr.open('POST', '/ajax/aggiungiMessaggioDashboard.php', true)
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    xhr.send('titolo='+encodeURIComponent($('titolo').value)+'&descrizione='+encodeURIComponent($('descrizione').value)+'&link='+encodeURIComponent($('link').value)+'&testo='+encodeURIComponent($('testo').value))

    xhr.onreadystatechange = function() {

      if(xhr.readyState === 4 && xhr.status === 200) {

        var res = JSON.parse(xhr.response)

        if(res.errore === true)
          alert(res.msg)

        else
          location.href = location.href

      } else if(xhr.readyState === 4)
        alert('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')

    }
  })
})
