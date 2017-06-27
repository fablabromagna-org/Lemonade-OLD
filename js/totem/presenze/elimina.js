var eliminazioneInCorso = false

function elimina(self, id) {

  if(eliminazioneInCorso)
    return

  eliminazioneInCorso = true
  self.innerHTML = "Eliminazione in corso"

  var xhr = new XMLHttpRequest()

  xhr.open('POST', '/ajax/totem/presenze/elimina.php', true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.send('id='+encodeURIComponent(id))

  xhr.onreadystatechange = function() {

    if(xhr.readyState === 4 && xhr.status === 200) {

      var res = JSON.parse(xhr.response)

      if(res.errore === true) {
        alert(res.msg)
        eliminazioneInCorso = false
        self.innerHTML = "Eliminazione in corso"

      } else
        location.href = location.href

    } else if(xhr.readyState === 4) {
      alert('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
      eliminazioneInCorso = false
      self.innerHTML = "Eliminazione in corso"
    }
  }
}