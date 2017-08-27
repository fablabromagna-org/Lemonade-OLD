;(function() {
  window.addEventListener('DOMContentLoaded', function() {

    var elementi = document.querySelectorAll('.selectPermesso')

    for(var i = 0; i < elementi.length; i++)
      elementi[i].addEventListener('change', modificaPermesso)

  }) // Window.addEventListener

  function modificaPermesso(e) {
    console.log(e)

    e.srcElement.disabled = true
    var permesso = e.srcElement.dataset.value
    var valore = e.srcElement.value
    var id = e.srcElement.dataset.id

    var xhr = new XMLHttpRequest()

    xhr.open('POST', '/ajax/permessi/utenti.php', true)
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

    xhr.send('permesso=' + encodeURIComponent(permesso) + '&valore=' + valore + '&gruppo=' + id)

    xhr.onreadystatechange = function() {

      if(xhr.readyState === 4 && xhr.status === 200) {

        try {
          var res = JSON.parse(xhr.response)

        } catch(err) {
          alert('Impossibile completare la richiesta!')
          e.srcElement.disabled = false
          return
        }

        if(res.errore === true) {
          alert(res.msg)
          e.srcElement.disabled = false

        } else
          e.srcElement.disabled = false


      } else if(xhr.readyState === 4) {
        alert('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
        e.srcElement.disabled = false
      }

    } // onreadystatechange
  } // modificaPermesso
})() // Funzione lambda
