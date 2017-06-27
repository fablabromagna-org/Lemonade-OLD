var revocaInCorso = false

function revoca(self, id) {

  if(revocaInCorso)
    return

  revocaInCorso = true
  self.innerHTML = "Revoca in corso"

  var xhr = new XMLHttpRequest()

  xhr.open('POST', '/ajax/totem/presenze/revoca.php', true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.send('id='+encodeURIComponent(id))

  xhr.onreadystatechange = function() {

    if(xhr.readyState === 4 && xhr.status === 200) {

      var res = JSON.parse(xhr.response)

      if(res.errore === true) {
        alert(res.msg)
        revocaInCorso = false
        self.innerHTML = "Revoca in corso"

      } else
        location.href = location.href

    } else if(xhr.readyState === 4) {
      alert('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
      revocaInCorso = false
      self.innerHTML = "Revoca in corso"
    }
  }
}