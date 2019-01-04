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

// ID di tutti i modali aperti
var modali_aperti = []

$(document).ready(function () {

    document.addEventListener('keyup', function (e) {
        if (e.keyCode === 27 && !e.ctrlKey && !e.altKey && !e.shiftKey && modali_aperti.length > 0) {

            var modale = $('#' + modali_aperti[0])

            modale.find('button[type=\'reset\']').each(function (i) {
                if (i !== 0)
                    return

                $(this).click()
            })
        }
    })

    var modals = $('div.modal')

    modals.each(function () {

        var modal = $(this)

        modal.find('button[type=\'reset\']').each(function () {

            $(this).click(function () {
                if (modal.hasClass('is-active')) {
                    modal.removeClass('is-active')
                    modali_aperti.splice(modali_aperti.indexOf(modal[0].id), 1)
                }
            })

        })

        modal.children('div.modal-background').each(function () {
            $(this).click(function () {
                modal.find('button[type=\'reset\']').each(function (i) {
                    if (i !== 0)
                        return

                    $(this).click()
                })
            })
        })
    })

    $('.open-modal').each(function () {
        $(this).click(function () {

            var id = this.dataset.open

            $('#' + id).each(function () {
                $(this).addClass('is-active')
                modali_aperti.unshift(id)
            })
        })

    })

    bulmaCalendar.attach('.calendar-input', {
        displayMode: 'dialog',
        dateFormat: 'DD/MM/YYYY',
        lang: 'it',
        weekStart: 1,
        showHeader: false
    })
})

function Modal (titolo, options = {}) {
    this.titolo = titolo
    this.id = 'modal-builder-' + Date.now()
    this.options = options

    this.is_opened = false
    this.is_destroyed = false

    /*
     * Schema:
     * {
     *  e: EVENT_NAME,
     *  c: function
     * }
     */
    this._events = []
    this._loading = null
}

Modal.prototype.create = function () {

    self = this

    if (this.options.form !== undefined) {
        var form = document.createElement('form')

        form.id = 'modal-builder-form-' + Date.now()

        if (this.options.form.class !== undefined) {
            if (typeof this.options.form.class === 'string') {
                form.classList.add(this.options.form.class)

            } else if (Array.isArray(this.options.form.class)) {
                for (var i = 0; i < this.options.form.class.length; i++)
                    form.classList.add(this.options.form.class[i])
            }
        }

        if (this.options.form.action !== undefined)
            form.action = this.options.form.action

        if (this.options.form.method !== undefined)
            form.method = this.options.form.method
    }

    var modal = document.createElement('div')
    modal.classList.add('modal')
    modal.id = this.id

    var back = document.createElement('div')
    back.classList.add('modal-background')
    back.addEventListener('click', e_close)

    modal.appendChild(back)

    var card = document.createElement('div')
    card.classList.add('modal-card')

    var header = document.createElement('header')
    header.classList.add('modal-card-head')

    var title = document.createElement('p')
    title.classList.add('modal-card-title')
    title.appendChild(document.createTextNode(this.titolo))
    this.title = title

    var btn_chiudi = document.createElement('button')
    btn_chiudi.classList.add('delete')
    btn_chiudi.setAttribute('aria-label', 'close')
    btn_chiudi.type = 'reset'
    btn_chiudi.addEventListener('click', e_close)

    header.appendChild(title)
    header.appendChild(btn_chiudi)

    this.btn_chiudi = btn_chiudi

    card.appendChild(header)

    var section = document.createElement('section')
    section.classList.add('modal-card-body')

    card.appendChild(section)
    this.elem_cont = section

    var footer = document.createElement('footer')
    footer.classList.add('modal-card-foot')

    var btn_annulla = document.createElement('button')
    btn_annulla.classList.add('button')
    btn_annulla.appendChild(document.createTextNode('Annulla'))
    btn_annulla.type = 'reset'
    btn_annulla.addEventListener('click', e_close)

    var btn_invio = document.createElement('button')
    btn_invio.classList.add('button')
    btn_invio.classList.add('is-primary')
    btn_invio.appendChild(document.createTextNode('Salva'))
    this.btn_invio = btn_invio

    footer.appendChild(btn_annulla)
    footer.appendChild(btn_invio)

    card.appendChild(footer)
    modal.appendChild(card)

    if (this.options.form !== undefined) {
        form.appendChild(modal)
        document.body.appendChild(form)
        this.form = form

    } else {
        document.body.appendChild(modal)
    }

    this.elem = modal

    function e_close (e) {
        self.close(false)
    }

    this.fixFormEvents()
}

Modal.prototype.open = function () {

    if (this.is_destroyed || this.is_opened)
        return

    this._lancia_evento('before_open')

    this.elem.classList.add('is-active')
    this.is_opened = true
    modali_aperti.unshift(this.id)

    this._lancia_evento('open')
}

Modal.prototype.close = function (destroy = true) {

    if (this.is_destroyed)
        return

    this._lancia_evento('before_close')

    this.elem.classList.remove('is-active')
    this.is_opened = false

    modali_aperti.splice(modali_aperti.indexOf(this.id), 1)

    this._lancia_evento('close')

    if (destroy)
        this.destroy()
}

Modal.prototype.destroy = function () {

    if (this.is_opened || this.is_destroyed)
        return

    this._lancia_evento('before_destroy')

    if (this.form !== undefined)
        document.body.removeChild(this.form)
    else
        document.body.removeChild(this.elem)

    this.is_destroyed = true

    this._lancia_evento('destroy')
}

Modal.prototype.on = function (event, callback) {
    this._events.push({
        e: event,
        c: callback
    })
}

Modal.prototype._lancia_evento = function (nome) {
    for (var i = 0; i < this._events.length; i++)
        if (this._events[i].e === nome)
            if (this._events[i].c() === false)
                return false
}

Modal.prototype.loading = function () {

    if (this._loading === null) {

        this.elem_cont.classList.add('is-hidden')

        var section = document.createElement('section')
        section.classList.add('modal-card-body')
        section.id = 'modal-card-loading-' + Date.now()

        this._loading = section

        var progress = document.createElement('progress')
        progress.max = 100
        progress.classList.add('progress')
        progress.classList.add('is-small')
        progress.classList.add('is-primary')

        var txt = document.createElement('h4')
        txt.classList.add('is-4')
        txt.classList.add('title')
        txt.classList.add('has-text-centered')
        txt.appendChild(document.createTextNode('Caricamento...'))

        section.appendChild(progress)
        section.appendChild(txt)

        this.elem_cont.parentNode.insertBefore(section, this.elem_cont.nextSibling)

    } else {
        this.elem_cont.parentNode.removeChild(this._loading)
        this.elem_cont.classList.remove('is-hidden')
        this._loading = null
    }
}

Modal.prototype.fixFormEvents = function () {
    var input = $(this.elem_cont).find('input[type=\'file\']')
    input.each(file_change)

    var form = $(this.elem_cont).find('form:not(.no-traditional-sender)')
    form.each(form_sender)

    if (this.form !== undefined) {
        form = $('#' + this.form.id + ':not(.no-traditional-sender)')
        form.each(form_sender)
    }
}
