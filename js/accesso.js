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
      email.disabled = false
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
      var email = $('email')
      var password = $('pwd')
      var invioForm = $('invioForm')

      // Disabilito tutti i campi
      email.disabled = true
      pwd.disabled = true
      invioForm.disabled = true

      // Cambio il testo del bottone
      invioForm.value = 'Accesso in corso'

      // Controllo i valori
      if(email.value === '')
        errore('Devi inserire il tuo indirizzo email')

      else if(!/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i.test(email.value))
        errore('Devi inserire un\'indirizzo email valido')

      else if(pwd.value === "")
        errore('Devi inserire una password!')

      else if(pwd.value.length < 6)
        errore('Devi inserire una password valida!')

      // Invio i dati al server
      else {

        var xhr = new XMLHttpRequest()

        // Creo una nuova richiesta XHR
        xhr.open('POST', '/ajax/accesso.php', true)
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
        xhr.send('email='+encodeURIComponent(email.value)+'&pwd='+encodeURIComponent(pwd.value))

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