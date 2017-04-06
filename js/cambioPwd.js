/**
 * @author Edoardo Savini <sedoardo98@gmail.com>
 * @version 0.0.1
 */

;(function() {
  function $(id) { return document.getElementById(id) }

  window.addEventListener('DOMContentLoaded', function() {

    // Memorizzo se un tentativo di cambio è in corso
    var inCorso = false

    // Funzione per mostrare avviso e abilitare i campi
    function errore(msg) {
      alert(msg)

      // Abilito i campi
      pwdNuova.disabled = false
      ripeti.disabled = false
      invioForm.disabled = false

      // Ripristino il testo del bottone
      invioForm.value = 'Cambia'

      // Abilito il cambio della password
      inCorso = false
    }

    // Mi metto in ascolto per l'invio del form di accesso
    $('cambioPwd').addEventListener('submit', function(e) {

      e.preventDefault()

      // Controllo che non sia presente un tentativo di login
      if(inCorso)
        return

      else
        inCorso = true

      // Memorizzo gli input
      var pwdAttuale = $('pwdAttuale')
      var pwdNuova = $('pwdNuova')
      var ripeti = $('ripeti')
      var invioForm = $('invioForm')

      // Disabilito tutti i campi
      pwdNuova.disabled = true
      ripeti.disabled = true
      invioForm.disabled = true

      // Cambio il testo del bottone
      invioForm.value = 'Cambio in corso'

      // Controllo i valori
      if(pwdNuova.value === "" || pwdNuova.value.length < 6)
        errore('Devi inserire una password di almeno sei caratteri!')

      else if(pwdNuova.value !== ripeti.value)
        errore('Le password non corrispondono!')

      else if(pwdAttuale.value === "" || pwdAttuale.value.length < 6)
        errore('Devi inserire una password attuale di almeno sei caratteri!')

      // Invio i dati al server
      else {

        var xhr = new XMLHttpRequest()

        // Creo una nuova richiesta XHR
        xhr.open('POST', '/ajax/cambioPwd.php', true)
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
        xhr.send('pwdAttuale='+encodeURIComponent(pwdAttuale.value)+'&pwd='+encodeURIComponent(pwdNuova.value))
        
        xhr.onreadystatechange = function() {

          if(xhr.readyState === 4 && xhr.status === 200) {

            var res = JSON.parse(xhr.response)

            if(res.errore === true)
              errore(res.msg)

            else {
              $('cambioPwd').innerHTML = '<p><b>La password è stata modificata con successo!</b><br />Ricarica la pagina per modificarla di nuovo.</p>'
            }
          } else if(xhr.readyState === 4)
            errore('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')

        } // Fine onreadystatechange
      } // Fine else controllo dati
    }) // Fine evento submit
  }) // Fine evento DOMContentLoaded
})()