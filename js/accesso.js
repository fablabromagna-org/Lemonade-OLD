$(document).ready(function () {
  var accessoInCorso = false
  var boxCaptcha = false

  $('#formAccesso').submit(function (e) {
    e.preventDefault()

    if (accessoInCorso)
      return

    accessoInCorso = true

      function disabled (a = true) {

          $('#email').prop('disabled', a)
          $('#password').prop('disabled', a)
          $('#captcha').prop('disabled', a)

          if (a) {
              $('#invioForm').hide()
              $('.lds-ring.center').show()
          } else {
              $('.lds-ring.center').hide()
              $('#invioForm').show()
          }

      }

    var email = $('#email')
    var pwd = $('#password')
    var captcha = $('#captcha')
      disabled()

    if (email.val() === '') {
      email.effect('shake')
      accessoInCorso = false
        disabled(false)
      return
    }

    if ((pwd.val()).length < 6) {
      pwd.effect('shake')
      accessoInCorso = false
        disabled(false)
      return
    }

    if (captcha.val() === '') {
      captcha.effect('shake')
      accessoInCorso = false
        disabled(false)
      return
    }
      $('#caricamento').show()

    $.ajax('/ajax/accesso.php', {
      method: 'POST'
      , success: function () {
        setTimeout(function () {
          location.href = '/dashboard.php'
        }, 1500)
      },
      error: function (data) {
        data = JSON.parse(data.responseText)

          var field = data.field

          if (field !== undefined) {

              disabled(false)

              if (field === 'email') {
                  $('#credenziali').show()
                  email.effect('shake')
                  $('#invioForm').val('Avanti')
              }

              if (field === 'password') {
                  $('#credenziali').show()
                  pwd.effect('shake')
                  $('#invioForm').val('Avanti')
              }

              if (field === 'captcha') {
                  $('#captchaBox').show()
                  captcha.effect('shake')
              }

              accessoInCorso = false
              boxCaptcha = false

          } else {
              alert(data.alert)
              accessoInCorso = false
              boxCaptcha = false
              disabled(false)

          }

          if (data.refreshCaptcha === true) {
              $('#captchaImg').prop('src', '/ajax/captcha.php')
              captcha.val('')
          }

      }
      , data: {
        'email': email.val()
        , 'password': pwd.val()
        , 'captcha': captcha.val()
      }
    })
  })
})
