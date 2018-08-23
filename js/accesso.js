$(document).ready(function () {
  var accessoInCorso = false
  var boxCaptcha = false

  $('#formAccesso').submit(function (e) {
    e.preventDefault()

    if (accessoInCorso)
      return

    accessoInCorso = true

    var email = $('#email')
    var pwd = $('#password')
    var captcha = $('#captcha')
    $('#invioForm').prop('disabled', true)

    if (email.val() === '') {
      email.effect('shake')
      accessoInCorso = false
      $('#invioForm').prop('disabled', false)
      return
    }

    if ((pwd.val()).length < 6) {
      pwd.effect('shake')
      accessoInCorso = false
      $('#invioForm').prop('disabled', false)

      return
    }

    if (!boxCaptcha) {
      $('#credenziali').hide()
      $('#captchaBox').show()
      captcha.focus()
      accessoInCorso = false
      boxCaptcha = true
      $('#invioForm').val('Accedi')
      $('#invioForm').prop('disabled', false)
      return
    }

    if (captcha.val() === '') {
      captcha.effect('shake')
      accessoInCorso = false
      $('#invioForm').prop('disabled', false)
      return
    }

    $('#captchaBox').hide()
    $('#spinnerBox').show()

    $.ajax('/ajax/accesso.php', {
      method: 'POST'
      , success: function () {
        setTimeout(function () {
          location.href = '/dashboard.php'
        }, 1500)
      },
      error: function (data) {
        data = JSON.parse(data.responseText)

        setTimeout(function () {
          var field = data.field

          if (field !== undefined) {

            $('#spinnerBox').hide()

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
            $('#invioForm').prop('disabled', false)

          } else {
            alert(data.alert)
            accessoInCorso = false
            boxCaptcha = false
            $('#spinnerBox').hide()
            $('#credenziali').show()
            $('#invioForm').val('Avanti')
            $('#invioForm').prop('disabled', false)

          }

          if (data.refreshCaptcha === true) {
            $('#captchaImg').prop('src', '/ajax/captcha.php')
            captcha.val('')
          }

        }, 1500)
      }
      , data: {
        'email': email.val()
        , 'password': pwd.val()
        , 'captcha': captcha.val()
      }
    })
  })
})