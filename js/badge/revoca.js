var revocaInCorso = false;

function revoca(self, id) {
  if(revocaInCorso)
    return

  revocaInCorso = true
  self.innerHTML = 'Revoca in corso'

  var xhr = new XMLHttpRequest()
  xhr.open('POST', '/ajax/badge/revoca.php')
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.send('id='+encodeURIComponent(id))

  xhr.onreadystatechange = function() {

    if(xhr.readyState === 4 && xhr.status === 200) {

      var res = JSON.parse(xhr.response)

      if(res.errore === true) {
        revocaInCorso = false
        self.innerHTML = 'Revoca'
        alert(res.msg)

      } else {
        alert('Badge revocato con successo!')
        location.href = location.href
      }


    } else if(xhr.readyState === 4) {
      revocaInCorso = false
      self.innerHTML = 'Revoca'
      alert('Impossibile completare la richiesta!')
    }
  }
}