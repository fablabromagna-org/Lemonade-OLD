window.addEventListener('DOMContentLoaded', function() {

  var salvataggioNomeInCorso = false

  // Modifica del nome
  document.getElementById('modificaGenerale').addEventListener('submit', function(e) {

    e.preventDefault()

    if(salvataggioNomeInCorso)
      return

    salvataggioNomeInCorso = true

    var nome = document.getElementById('nome')
    var id = document.getElementById('idMakerSpace')
    var bottone = document.getElementById('salvaMakerSpace')

    function errore(msg) {
      alert(msg)
      nome.disabled = false
      bottone.disabled = false

      salvataggioNomeInCorso = false
    }

    nome.disabled = true
    bottone.disabled = true

    var xhr = new XMLHttpRequest()
    xhr.open('POST', '/ajax/makerspace/modifica.php', true)
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    xhr.send('nome='+encodeURIComponent(nome.value)+'&id='+encodeURIComponent(id.value))

    xhr.onreadystatechange = function() {
      if(xhr.readyState === 4 && xhr.status === 200) {

        var res = JSON.parse(xhr.response)

        if(res.errore === true)
          errore(res.msg)

        else {
          alert('Modifica completata!')
          location.href = location.href
        }

      } else if(xhr.readyState === 4)
        errore('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
    }
  })
})
