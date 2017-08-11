var telegram = false

function rimuoviCollegamentoTelegram() {

  if(telegram)
    return

  telegram = true

  var xhr = new XMLHttpRequest()
  xhr.open('GET', '/ajax/social/telegram/rimuovi.php', true)
  xhr.send()

  function errore(a) { alert(a) }

  xhr.onreadystatechange = function() {

    if(xhr.readyState === 4 && xhr.status === 200) {

      var res = JSON.parse(xhr.response)

      if(res.errore === true)
        errore(res.msg)

      else {
        errore('Disconnessione da Telegram completata con successo!')
        location.href = location.href
      }


    } else if(xhr.readyState === 4)
      errore('Impossibile completare la richiesta!')
  }
}

function rimuoviCollegamentoFacebook() {

  if(telegram)
    return

  telegram = true

  var xhr = new XMLHttpRequest()
  xhr.open('GET', '/ajax/social/facebook/remove.php', true)
  xhr.send()

  function errore(a) { alert(a) }

  xhr.onreadystatechange = function() {

    if(xhr.readyState === 4 && xhr.status === 200) {

      var res = JSON.parse(xhr.response)

      if(res.errore === true)
        errore(res.msg)

      else {
        errore('Disconnessione da Telegram completata con successo!')
        location.href = location.href
      }


    } else if(xhr.readyState === 4)
      errore('Impossibile completare la richiesta!')
  }
}

window.addEventListener('DOMContentLoaded', function() {
  document.getElementById('fbLogin').addEventListener('click', fbLogin)
})

var accessoFbInCorso = false

// Accesso con Facebook
function fbLogin(e) {

  e.preventDefault()

  if(accessoFbInCorso)
    return

  _this = this

  FB.login(function(res){

    // L'utente ha autorizzato l'applicazione
    if(res.authResponse) {

      // Ricavo il token di autenticazione
      var token = FB.getAuthResponse()['accessToken']

      // Disabilito la richiesta
      accessoFbInCorso = true
      _this.innerHTML = 'Accesso in corso...'

      // Invio il token al server
      var xhr = new XMLHttpRequest()
      xhr.open('POST', '/ajax/social/facebook/collega.php', true)
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
      xhr.send('token=' + encodeURIComponent(token))

      function errore(msg) {
        alert(msg)
        accessoFbInCorso = false
        _this.innerHTML = '<i class="fa fa-facebook-official" aria-hidden="true"></i>Accedi con Facebook'
      }

      xhr.onreadystatechange = function() {

        if(xhr.readyState === 4 && xhr.status === 200) {

          var res = JSON.parse(xhr.response)

          if(res.errore === true)
            errore(res.msg)

          else
            location.href = location.href


        } else if(xhr.readyState === 4)
          errore('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
      }
    }
  }, {}); // FB.login
}
