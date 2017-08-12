var annullamentoInCorso = false;

function annulla(self, id) {
  if(annullamentoInCorso)
    return

  annullamentoInCorso = true
  self.innerHTML = 'Annullamento in corso'

  var xhr = new XMLHttpRequest()
  xhr.open('POST', '/ajax/presenze/annulla.php')
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.send('id='+encodeURIComponent(id))

  xhr.onreadystatechange = function() {

    if(xhr.readyState === 4 && xhr.status === 200) {

      var res = JSON.parse(xhr.response)

      if(res.errore === true) {
        annullamentoInCorso = false
        self.innerHTML = 'Annulla'
        alert(res.msg)

      } else
        location.href = location.href

    } else if(xhr.readyState === 4) {
      annullamentoInCorso = false
      self.innerHTML = 'Annulla'
      alert('Impossibile completare la richiesta!')
    }
  }
}
