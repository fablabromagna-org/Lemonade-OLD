function elimina(id) {

  var xhr = new XMLHttpRequest()

  xhr.open('POST', '/ajax/gestione.categoriaUtente.php', true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.send('id='+encodeURIComponent(id)+'&richiesta=elimina')

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

}

function modifica(id) {

  var nome = prompt('Inserisci il nome della categoria.')

  if(nome == null)
    return;

  var xhr = new XMLHttpRequest()

  xhr.open('POST', '/ajax/gestione.categoriaUtente.php', true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.send('id='+encodeURIComponent(id)+'&nome='+encodeURIComponent(nome)+'&richiesta=rinomina')

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

}

function spostaIn(id) {

  var idDest = prompt('Inserisci il nome della categoria.')

  if(idDest == null)
    return;

  var xhr = new XMLHttpRequest()

  xhr.open('POST', '/ajax/gestione.categoriaUtente.php', true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.send('id='+encodeURIComponent(id)+'&destinazione='+encodeURIComponent(idDest)+'&richiesta=sposta')

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

}

window.addEventListener('DOMContentLoaded', function() {

  document.getElementById('aggiungiForm').addEventListener('submit', function(e) {

    e.preventDefault()

    var xhr = new XMLHttpRequest()

    function $(a) { return document.getElementById(a) }

    xhr.open('POST', '/ajax/gestione.categoriaUtente.php', true)
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    xhr.send('nome='+encodeURIComponent($('nome').value)+'&portale='+encodeURIComponent($('portale').checked)+'&rete='+encodeURIComponent($('rete').checked)+'&richiesta=aggiungi')

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
