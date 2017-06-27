window.addEventListener('DOMContentLoaded', function() {

  document.getElementById('aggiungiTotemForm').addEventListener('submit', function(e) {

    e.preventDefault()

    var xhr = new XMLHttpRequest()

    function $(a) { return document.getElementById(a) }

    xhr.open('POST', '/ajax/totem/presenze/aggiungi.php', true)
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    xhr.send('nome='+encodeURIComponent($('nomeTotem').value)+'&id='+encodeURIComponent($('idMakerSpace').value))

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