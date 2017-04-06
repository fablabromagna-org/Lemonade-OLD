/**
 * @author Edoardo Savini <sedoardo98@gmail.com>
 * @version 0.0.1
 */

;(function() {
  function $(id) { return document.getElementById(id) }

  window.addEventListener('DOMContentLoaded', function() {

    // Memorizzo se un tentativo di accesso è in corso
    var inCorso = false

    // Funzione per mostrare avviso e abilitare i campi
    function errore(msg) {
      alert(msg)

      // Abilito i campi
      utente.disabled = false
      pwd.disabled = false
      invioForm.disabled = false

      // Ripristino il testo del bottone
      invioForm.value = 'Accedi'

      // Abilito la registrazione
      inCorso = false
    }

    // Mi metto in ascolto per l'invio del form di accesso
    $('formAccesso').addEventListener('submit', function(e) {

      e.preventDefault()

      // Controllo che non sia presente un tentativo di login
      if(inCorso)
        return

      else
        inCorso = true

      // Memorizzo gli input
      var utente = $('utente')
      var password = $('pwd')
      var invioForm = $('invioForm')

      // Disabilito tutti i campi
      utente.disabled = true
      pwd.disabled = true
      invioForm.disabled = true

      // Cambio il testo del bottone
      invioForm.value = 'Verifica in corso'

      // Controllo i valori
      if(utente.value === '')
        errore('Devi inserire il tuo codice utente!')

      else if(!/^[0-9]+$/.test(utente.value))
        errore('Devi inserire un codice utente valido!')

      else if(pwd.value === "")
        errore('Devi inserire una password!')

      // Invio i dati al server
      else {

        var xhr = new XMLHttpRequest()

        // Creo una nuova richiesta XHR
        xhr.open('POST', '/ajax/scuolaweb.php', true)
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
        xhr.send('utente='+encodeURIComponent(utente.value)+'&pwd='+encodeURIComponent(pwd.value))

        xhr.onreadystatechange = function() {

          if(xhr.readyState === 4 && xhr.status === 200) {

            var res = JSON.parse(xhr.response)

            if(res.errore === true)
              errore(res.msg)

            else
              location.href = '/dashboard.php'

          } else if(xhr.readyState === 4)
            errore('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')

        } // Fine onreadystatechange
      } // Fine else controllo dati
    }) // Fine evento submit
  }) // Fine evento DOMContentLoaded
})()