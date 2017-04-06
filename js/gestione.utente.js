/**
 * @author Edoardo Savini <sedoardo98@gmail.com>
 * @version 0.0.1
 */

;(function() {
  function $(id) { return document.getElementById(id) }

  window.addEventListener('DOMContentLoaded', function() {

    // Mi metto in ascolto per l'invio del form di modifica del profilo
    var profiloInCorso = false
    $('modificaProfilo').addEventListener('submit', function(e) {

      e.preventDefault()

      if(profiloInCorso)
        return;

      else
        profiloInCorso = true

      // Recupero tutti gli input
      var nome = $('profiloNome')
      var cognome = $('profiloCognome')
      var email = $('profiloMail')
      var idUtente = $('idUtente').value
      var bottone = $('salvaProfilo')

      // Disabilito i campi
      nome.disabled = true
      cognome.disabled = true
      email.disabled = true
      bottone.disabled = true

      // Cambio il testo al bottone
      bottone.value = 'Modifica in corso'

      // Funzione per mostrare avviso e abilitare i campi
      function errore(msg) {
        alert(msg)

        // Disabilito i campi
        nome.disabled = false
        cognome.disabled = false
        email.disabled = false
        bottone.disabled = false

        // Cambio il testo al bottone
        bottone.value = 'Salva'

        // Riabilito la modifica
        profiloInCorso = false
      }

      // Controllo che gli input siano diversi da vuoto
      // Un controllo più approfondito lo farà il server
      if(nome.value === '')
        errore('Devi inserire un nome!')

      else if(cognome.value === '')
        errore('Devi inserire un cognome!')

      else if(email.value === '')
        errore('Devi inserire un indirizzo email!')

      // Inoltro i dati al server
      else {

        var xhr = new XMLHttpRequest()

        // Creo la richiesta
        xhr.open('POST', '/ajax/gestione.modificaUtente.profilo.php', true)
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

        // Invio la richiesta
        xhr.send('nome='+encodeURIComponent(nome.value)+'&cognome='+encodeURIComponent(cognome.value)+'&email='+encodeURIComponent(email.value)+'&id='+encodeURIComponent(idUtente))

        xhr.onreadystatechange = function() {

          if(xhr.readyState === 4 && xhr.status === 200) {

            var res = JSON.parse(xhr.response)

            if(res.errore === true)
              errore(res.msg)

            else {
              errore('Profilo modificato con successo!')
              location.href = location.href
            }


          } else if(xhr.readyState === 4)
            errore('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
        }

      } // Fine controllo dati
    }) // Fine evento submit #profilo

    // Mi metto in ascolto per l'invio del form di modifica del profilo
    var permessiInCorso = false
    $('modificaPermessi').addEventListener('submit', function(e) {

      e.preventDefault()

      if(permessiInCorso)
        return;

      else
        permessiInCorso = true

      // Recupero tutti gli input
      var sospensione = $('profiloSospeso')
      var gestionePortale = $('profiloGestione')
      var gestioneRete = $('profiloGestioneRete')
      var confermaMail = $('profiloConferma')
      var categoria = $('profiloCategoria')
      var idUtente = $('idUtente').value
      var bottone = $('salvaPermessi')

      // Disabilito i campi
      sospensione.disabled = true
      gestionePortale.disabled = true
      gestioneRete.disabled = true
      confermaMail.disabled = true
      categoria.disabled = true
      bottone.disabled = true

      // Cambio il testo al bottone
      bottone.value = 'Modifica in corso'

      // Funzione per mostrare avviso e abilitare i campi
      function errore(msg) {
        alert(msg)

        // Disabilito i campi
        sospensione.disabled = false
        gestionePortale.disabled = false
        gestioneRete.disabled = false
        confermaMail.disabled = false
        categoria.disabled = false
        bottone.disabled = false

        // Cambio il testo al bottone
        bottone.value = 'Salva'

        // Riabilito la modifica
        permessiInCorso = false
      }

      // In questa versione non controllo i dati
      // Demando completamente l'operazione al server

      var xhr = new XMLHttpRequest()

      // Creo la richiesta
      xhr.open('POST', '/ajax/gestione.modificaUtente.permessi.php', true)
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

      // Invio la richiesta
      xhr.send('sospensione='+encodeURIComponent(sospensione.value)+'&gestionePortale='+encodeURIComponent(gestionePortale.value)+'&gestioneRete='+encodeURIComponent(gestioneRete.value)+'&categoria='+encodeURIComponent(categoria.value)+'&confermaMail='+encodeURIComponent(confermaMail.value)+'&id='+encodeURIComponent(idUtente))

      xhr.onreadystatechange = function() {

        if(xhr.readyState === 4 && xhr.status === 200) {

          var res = JSON.parse(xhr.response)

          if(res.errore === true)
            errore(res.msg)

          else {
            errore('Profilo modificato con successo!')
            location.href = location.href
          }


        } else if(xhr.readyState === 4)
          errore('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
      }
    }) // Fine evento submit #permessi

    // Mi metto in ascolto per la richiesta di una nuova password
    var cambioInCorso = false
    $('cambiaPwd').addEventListener('click', function(e) {

      e.preventDefault()

      if(cambioInCorso)
        return;

      else
        cambioInCorso = true

      // Disabilito il bottone
      this.className += ' disabled'
      this.innerHTML = 'Richiesta in corso'

      // Se l'attributo data-email non contiene l'indirizzo
      // Dico all'utente di ricaricare la pagina
      if(this.getAttribute('data-email') == '' || this.getAttribute('data-email') == undefined) {
        alert('Errore! Ricarica la pagina.')
        this.innerHTML = 'Errore!'

      // Inoltro la richiesta al server
      } else {

        var xhr = new XMLHttpRequest()

        // Creo una nuova richiesta XHR
        xhr.open('POST', '/ajax/recupero.php', true)
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
        xhr.send('email='+encodeURIComponent(this.getAttribute('data-email')))

        xhr.onreadystatechange = function() {

          if(xhr.readyState === 4 && xhr.status === 200) {

            var res = JSON.parse(xhr.response)

            if(res.errore === true) {
              alert(res.msg)
              $('cambiaPwd').innerHTML = 'Errore!'

            } else
              $('cambiaPwd').innerHTML = 'Password inviata!'

          } else if(xhr.readyState === 4) {
            alert('Impossibile completare la richiesta!\nRiprova tra qualche minuto.')
            $('cambiaPwd').innerHTML = 'Errore!'
          }
        }
      }
    }) // Fine evento submit #email

  }) // Fine evento DOMContentLoaded
})()