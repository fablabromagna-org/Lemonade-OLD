var aggiuntaInCorso = false;

function aggiungi(self) {
  if(aggiuntaInCorso)
    return

  aggiuntaInCorso = true
  self.innerHTML = 'Aggiunta in corso'

  var rfid = prompt('Inserisci il RFID di 10 cifre del nuovo badge.\nSe utilizzi un lettore in emulazione di tastiera, il form verrà inviato automaticamente.')

  if(rfid == null) {
    aggiuntaInCorso = false
    self.innerHTML = 'Aggiungi'
    return

  } else if(!/^([0-9]{10})$/.test(rfid)) {
    aggiuntaInCorso = false
    self.innerHTML = 'Aggiungi'
    alert('RFID non valido!')

  } else {

    var xhr = new XMLHttpRequest()
    xhr.open('POST', '/ajax/badge/aggiungi.php')
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    xhr.send('rfid='+encodeURIComponent(rfid)+'&id='+encodeURIComponent(document.getElementById('idUtente').value))

    xhr.onreadystatechange = function() {

      if(xhr.readyState === 4 && xhr.status === 200) {

        var res = JSON.parse(xhr.response)

        if(res.errore === true) {
          aggiuntaInCorso = false
          self.innerHTML = 'Aggiungi'
          alert(res.msg)

        } else {
          alert('Badge aggiunto con successo!')
          location.href = location.href
        }


      } else if(xhr.readyState === 4) {
        aggiuntaInCorso = false
        self.innerHTML = 'Aggiungi'
        alert('Impossibile completare la richiesta!')
      }
    }
  }
}