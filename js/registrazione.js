/**
 * @author Edoardo Savini <sedoardo98@gmail.com>
 * @version 0.0.1
 */

;(function() {
  function $(id) { return document.getElementById(id) }

  window.addEventListener('DOMContentLoaded', function() {

    // Per prevenire più invii al server memorizzo
    // Se è in corso una registrazione, un utente potrebbe aggirare
    // I campi disabilitati
    var registrazione = false

    // Funzione per mostrare avviso e abilitare i campi
    function errore(msg) {
      alert(msg)

      // Abilito i campi
      nome.disabled = false
      cognome.disabled = false
      email.disabled = false
      tipoAccount.disabled = false
      pwd.disabled = false
      invioForm.disabled = false

      // Ripristino il testo del bottone
      invioForm.value = 'Registrati'

      // Abilito la registrazione
      registrazione = false
    }

    // Mi metto in ascolto per il submit del form di registrazione
    $('formRegistrazione').addEventListener('submit', function(e) {

      e.preventDefault()

      // Se è presente una registrazione in corso
      // Annullo la richiesta
      if(registrazione)
        return

      // Altrimenti imposto la registrazione in corso
      else
        registrazione = true


      // Raccolgo gli input
      var nome = $('nome')
      var cognome = $('cognome')
      var email = $('email')
      var tipoAccount = $('tipoAccount')
      var pwd = $('pwd')
      var invioForm = $('invioForm')

      // Disabilito i campi
      nome.disabled = true
      cognome.disabled = true
      email.disabled = true
      tipoAccount.disabled = true
      pwd.disabled = true
      invioForm.disabled = true

      // Cambio il testo del bottone
      invioForm.value = 'Registrazione in corso'

      // Controllo i valori
      if(nome.value === "")
        errore('Devi inserire il tuo nome!')

      else if(!/^[a-z ,.'-]+$/i.test(nome.value))
        errore('Devi inserire un nome valido!')

      else if(cognome.value === "")
        errore('Devi inserire il tuo cognome!')

      else if(!/^[a-z ,.'-]+$/i.test(cognome.value))
        errore('Devi inserire un cognome valido!')

      else if(email.value === "")
        errore('Devi inserire il tuo indirizzo email!')

      else if(!/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i.test(email.value))
        errore('Devi inserire un indirizzo email valido!')

      else if(tipoAccount.value !== "0" && tipoAccount.value !== "1" && tipoAccount.value !== "2" && tipoAccount.value !== "3")
        errore('Devi selezionare una categoria valida!')

      else if(pwd.value === "" || pwd.value.length < 6)
        errore('Devi inserire una password di almeno sei caratteri!')

      // Tutto okay, invio i dati al server
      else {

        var xhr = new XMLHttpRequest()

        // Creo una nuova richiesta XHR
        xhr.open('POST', '/ajax/registrazione.php', true)
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
        xhr.send('nome='+encodeURIComponent(nome.value)+
          '&cognome='+encodeURIComponent(cognome.value)+
          '&email='+encodeURIComponent(email.value)+
          '&tipoAccount='+encodeURIComponent(tipoAccount.value)+
          '&pwd='+encodeURIComponent(pwd.value))

        xhr.onreadystatechange = function() {

          if(xhr.readyState === 4 && xhr.status === 200) {

            var res = JSON.parse(xhr.response)

            if(res.errore === true)
              errore(res.msg)

            else
              location.href = '/confermaMail.php'

          } else if(xhr.readyState === 4)
            errore('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')

        } // Fine onreadystatechange
      } // Fine else controllo dati
    }) // Fine evento submit
  }) // Fine evento DOMContentLoaded
})()