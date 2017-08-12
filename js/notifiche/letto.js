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

function leggiTutto() {

  var xhr = new XMLHttpRequest()
  xhr.open('GET', '/ajax/notifiche/leggiTutto.php', true)
  xhr.send()

  function errore(a) { alert(a) }

  xhr.onreadystatechange = function() {

    if(xhr.readyState === 4 && xhr.status === 200) {

      var res = JSON.parse(xhr.response)

      if(res.errore === true)
        errore(res.msg)

      else
        location.href = location.href


    } else if(xhr.readyState === 4)
      errore('Impossibile completare la richiesta!')
  }
}
