var cancellazioneInCorso = false;

function letto(self, id) {
  if(cancellazioneInCorso)
    return

  cancellazioneInCorso = true
  self.innerHTML = 'Lettura in corso'

  var xhr = new XMLHttpRequest()
  xhr.open('POST', '/ajax/notifiche/letto.php')
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.send('id='+encodeURIComponent(id))

  xhr.onreadystatechange = function() {

    if(xhr.readyState === 4 && xhr.status === 200) {

      var res = JSON.parse(xhr.response)

      if(res.errore === true) {
        cancellazioneInCorso = false
        self.innerHTML = 'Letto'
        alert(res.msg)

      } else
        location.href = location.href

    } else if(xhr.readyState === 4) {
      cancellazioneInCorso = false
      self.innerHTML = 'Letto'
      alert('Impossibile completare la richiesta!')
    }
  }
}