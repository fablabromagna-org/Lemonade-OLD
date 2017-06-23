var cancellazioneInCorso = false;

function elimina(self, id) {
  if(cancellazioneInCorso)
    return

  cancellazioneInCorso = true
  self.innerHTML = 'Cancellazione in corso'

  var xhr = new XMLHttpRequest()
  xhr.open('POST', '/ajax/notifiche/elimina.php')
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.send('id='+encodeURIComponent(id))

  xhr.onreadystatechange = function() {

    if(xhr.readyState === 4 && xhr.status === 200) {

      var res = JSON.parse(xhr.response)

      if(res.errore === true) {
        cancellazioneInCorso = false
        self.innerHTML = 'Elimina'
        alert(res.msg)

      } else
        location.href = location.href

    } else if(xhr.readyState === 4) {
      cancellazioneInCorso = false
      self.innerHTML = 'Elimina'
      alert('Impossibile completare la richiesta!')
    }
  }
}