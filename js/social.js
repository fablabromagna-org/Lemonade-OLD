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
