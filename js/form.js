/*
                                        .,,cccd$$$$$$$$$$$ccc,
                                    ,cc$$$$$$$$$$$$$$$$$$$$$$$$$cc,
                                  ,d$$$$$$$$$$$$$$$$"J$$$$$$$$$$$$$$c,
                                d$$$$$$$$$$$$$$$$$$,$" ,,`?$$$$$$$$$$$$L
                              ,$$$$$$$$$$$$$$$$$$$$$',J$$$$$$$$$$$$$$$$$b
                             ,$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$i `$h
                             $$$$$$$$$$$$$$$$$$$$$$$$$P'  "$$$$$$$$$$$h $$
                            ;$$$$$$$$$$$$$$$$$$$$$$$$F,$$$h,?$$$$$$$$$$h$F
                            `$$$$$$$$$$$$$$$$$$$$$$$F:??$$$:)$$$$P",. $$F
                             ?$$$$$$$$$$$$$$$$$$$$$$(   `$$ J$$F"d$$F,$F
                              ?$$$$$$$$$$$$$$$$$$$$$h,  :P'J$$F  ,$F,$"
                               ?$$$$$$$$$$$$$$$$$$$$$$$ccd$$`$h, ",d$
                                "$$$$$$$$$$$$$$$$$$$$$$$$",cdc $$$$"
                       ,uu,      `?$$$$$$$$$$$$$$$$$$$$$$$$$$$c$$$$h
                   .,d$$$$$$$cc,   `$$$$$$$$$$$$$$$$??$$$$$$$$$$$$$$$,
                 ,d$$$$$$$$$$$$$$$bcccc,,??$$$$$$ccf `"??$$$$??$$$$$$$
                d$$$$$$$$$$$$$$$$$$$$$$$$$h`?$$$$$$h       d$$$$$$$$P
               d$$$$$$$$$$$$$$$$$$$$$$$$$$$$`$$$$$$$hc,,cd$$$$$$$$P"
           =$$?$$$$$$$$P' ?$$$$$$$$$$$$$$$$$;$$$$$$$$$???????",,
              =$$$$$$F       `"?????$$$$$$$$$$$$$$$$$$$$$$$$$$$$$bc
              d$$F"?$$k ,ccc$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$i
       .     ,ccc$$c`""u$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$P",$$$$$$$$$$$$h
    ,d$$$L  J$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$" `""$$$??$$$$$$$
  ,d$$$$$$c,"$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$F       `?J$$$$$$$'
 ,$$$$$$$$$$h`$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$F           ?$$$$$$$P""=,
,$$$F?$$$$$$$ $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$F              3$$$$II"?$h,
$$$$$`$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$P"               ;$$$??$$$,"?"
$$$$F ?$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$P",z'                3$$h   ?$F
       `?$$$$$$$$$$$$$$$??$$$$$$$$$PF"',d$P"                  "?$F
          """""""         ,z$$$$$$$$$$$$$P
                         J$$$$$$$$$$$$$$F
                        ,$$$$$$$$$$$$$$F
                        :$$$$$c?$$$$PF'
                        `$$$$$$$P
                         `?$$$$F
 */

$(document).ready(function () {
    var input = $('input[type=\'file\']')

    input.each(file_change)

    var form = $('form:not(.no-traditional-sender)')

    form.each(form_sender)
})

function file_change () {
    $(this).change(function () {

        if (this._fileChangeEvent === true)
            return

        var files = []
        for (var i = 0; i < this.files.length; i++) {
            files.push(this.files[i].name)
        }

        files = files.join('; ')

        var txt = $(this).parent().children('.file-name')
        txt.each(function () {
            this.innerText = files
            this.setAttribute('title', files)
        })

        this._fileChangeEvent = true
    })
}

function form_sender () {
    this._invioInCorso = false

    if (this._formSubmitEvent === true)
        return

    this._formSubmitEvent = true

    var _self = this

    this.addEventListener('submit', function (e) {
        e.preventDefault()

        if (this._invioInCorso)
            return

        this._invioInCorso = true

        var dati = {}
        var elementi = $(this).find('input:not([type=\'submit\']):not(.ajax-exclude), select:not(.ajax-exclude), textarea')

        elementi.each(function () {
            valorizzatore(this)
            this.disabled = true
        })

        $(this).find('input[type=\'submit\'], button').each(function () {
            $(this).disabled = true
        })

        $(this).find('button').each(function () {
            $(this).addClass('is-loading')
        })

        function abilitaForm () {
            _self._invioInCorso = false
        }

        var config = {
            method: _self.getAttribute('method')
            , url: this.action
            , dataType: 'json'
            , cache: false
            , complete: function (res) {
                if (res.responseJSON !== undefined) {
                    if (res.responseJSON.alert !== undefined)
                        alert(res.responseJSON.alert)

                    if (res.responseJSON.redirect !== undefined)
                        location.href = res.responseJSON.redirect

                    if (res.responseJSON.refreshCaptcha !== undefined) {
                        $(_self).find('.captcha-img').each(function () {
                            this.src = '/ajax/captcha.php'
                        })

                        $(_self).find('input[name=\'captcha\']').each(function () {
                            $(this).val('')
                        })
                    }

                    if (res.responseJSON.field !== undefined) {
                        $(_self).find('input[name=\'' + res.responseJSON.field + '\']').each(function () {
                            $(this).val('')
                            $(this).effect('shake')
                        })
                    }
                } else if (res.status !== 200 && res.status !== 204)
                    alert('Impossibile completare la richiesta.')

                if ((res.status === 200 || res.status === 204) && $(_self).hasClass('is-modal')) {
                    $(_self).children('div.modal.is-active').each(function () {
                        $(this).removeClass('is-active')
                    })
                }

                abilitaForm()

                elementi.each(function () {
                    this.disabled = false
                })

                $(_self).find('input[type=\'submit\'], button').each(function () {
                    $(this).disabled = false
                })

                $(_self).find('button').each(function () {
                    $(this).removeClass('is-loading')
                })
            }
        }

        if (_self.getAttribute('method') === 'get')
            config.data = dati

        else {
            config.contentType = 'application/json; charset=utf-8'
            config.data = JSON.stringify(dati)
        }

        $.ajax(config)

        function caster (input, data = null) {

            if (data === null)
                data = input.value

            if (data === 'null')
                return null

            if (input.dataset.type === undefined || input.dataset.type === 'string')
                return data

            if (input.dataset.type === 'integer')
                return parseInt(data)

            if (input.dataset.type === 'float')
                return parseFloat(data)

            if (input.dataset.type === 'boolean' && data === '')
                return null

            if (input.dataset.type === 'boolean')
                return data === 'true' || data === '1' || data === true

            return data
        }

        function valorizzatore (input) {

            if (input.nodeName === 'SELECT' && input.getAttribute('multiple') !== null) {

                dati[input.name] = []

                $.each($(input).val(), function () {
                    dati[input.name].push(caster(input, this))
                })

            } else if (input.type === 'checkbox' && input.checked && input.name.endsWith('[]')) {
                if (dati[input.name] === undefined) {
                    dati[input.name] = []
                    dati[input.name].push(caster(input))
                } else
                    dati[input.name].push(caster(input))

            } else if (input.type === 'checkbox') {
                dati[input.name] = caster(input, input.checked)

            } else if (input.type === 'radio' && input.checked) {
                dati[input.name] = caster(input)

            } else
                dati[input.name] = caster(input)

        }
    })
}
