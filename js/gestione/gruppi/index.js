function apri_modifica (id_gruppo) {

    var modal = new Modal('Modifica gruppo', {
        form: {
            action: '/ajax/gruppo/gruppo.php',
            method: 'put'
        }
    })
    modal.create()

    modal.open()
    modal.loading()

    var dati = {
        id_gruppo: id_gruppo
    }

    $.ajax({
        method: 'get'
        , url: '/ajax/gruppo/gruppo.php'
        , cache: false
        , data: dati
        , complete: function (res) {
            if (res.responseJSON !== undefined) {

                var field = document.createElement('div')
                field.classList.add('field')

                var lbl = document.createElement('label')
                lbl.for = 'nome'
                lbl.appendChild(document.createTextNode('Nome:'))
                field.appendChild(lbl)

                var input = document.createElement('input')
                input.id = 'nome'
                input.type = 'text'
                input.name = 'nome'
                input.placeholder = 'Nome del gruppo'
                input.classList.add('input', 'is-primary')
                input.value = res.responseJSON.nome
                field.appendChild(input)

                modal.elem_cont.appendChild(field)

                field = document.createElement('div')
                field.classList.add('field')

                lbl = document.createElement('label')
                lbl.for = 'descrizione'
                lbl.appendChild(document.createTextNode('Descrizione del gruppo:'))
                field.appendChild(lbl)

                var div = document.createElement('div')
                div.classList.add('control')

                input = document.createElement('textarea')
                input.id = 'descrizione'
                input.name = 'descrizione'
                input.placeholder = 'Descrizione del gruppo'
                input.classList.add('textarea')
                input.value = res.responseJSON.descrizione
                field.appendChild(input)

                div.appendChild(field)
                modal.elem_cont.appendChild(div)

                lbl = document.createElement('label')
                lbl.for = 'default'

                input = document.createElement('input')
                input.id = 'default'
                input.name = 'default'
                input.type = 'checkbox'
                input.checked = res.responseJSON.default
                lbl.appendChild(input)

                lbl.appendChild(document.createTextNode('Gruppo di default per i nuovi iscritti.'))
                field.appendChild(lbl)

                input = document.createElement('input')
                input.name = 'id_gruppo'
                input.type = 'hidden'
                input.value = res.responseJSON.id_gruppo
                input.dataset.type = 'integer'
                modal.elem_cont.appendChild(input)

                modal.loading()

            } else {
                alert('Impossibile completare la richiesta.')
                modal.close()
            }
        }
    })
}

function apri_permessi (id_gruppo) {
    var modal = new Modal('Modifica permessi di gruppo', {
        form: {
            action: '/ajax/gruppo/permessi.php',
            method: 'post',
            class: 'is-modal'
        }
    })
    modal.create()
    modal.open()

    var dati = {
        id_gruppo: id_gruppo
    }

    $.ajax({
        method: 'get'
        , url: '/ajax/gruppo/permessi.php'
        , cache: false
        , data: dati
        , complete: function (res) {
            if (res.responseJSON !== undefined) {

                var html = ''

                for (var permesso in res.responseJSON) {

                    if (!res.responseJSON.hasOwnProperty(permesso))
                        continue

                    var dati_permesso = res.responseJSON[permesso]

                    html += '<div class="field"><div class="columns"><div class="column"><p><b>' + dati_permesso.nome +
                        '</b></p><p><samp style="font-size: 14px">' + permesso +
                        '</samp></p><p>' + dati_permesso.descrizione + '</p><p>Default: ' + (dati_permesso.default ? 'Sì' : 'No')
                        + '</p></div><div class="column is-3"><div class="select"><select name="' + permesso
                        + '" id="ruolo" data-type="boolean">'

                    if (dati_permesso.reale === undefined)
                        html += '<option value="" selected>Default</option><option value="1">Sì</option><option value="0">No</option>'

                    else if (dati_permesso.reale === true)
                        html += '<option value="">Default</option><option value="1" selected>Sì</option><option value="0">No</option>'

                    else
                        html += '<option value="">Default</option><option value="1">Sì</option><option value="0" selected>No</option>'

                    html += '</select></div></div></div></div>'
                }

                html += '<input type="hidden" name="id_gruppo" value="' + id_gruppo + '" data-type="integer" />'

                modal.elem_cont.innerHTML = html
                modal.fixFormEvents()
                modal.loading()

            } else {
                alert('Impossibile completare la richiesta.')
                modal.close()
            }
        }
    })

    modal.loading()

}

function elimina (id_gruppo) {
    var dati = {
        id_gruppo: id_gruppo
    }

    $.ajax({
        method: 'delete'
        , url: '/ajax/gruppo/gruppo.php'
        , cache: false
        , contentType: 'application/json; charset=utf-8'
        , data: JSON.stringify(dati)
        , complete: function (res) {
            if (res.responseJSON !== undefined) {

                if (res.responseJSON !== undefined) {
                    if (res.responseJSON.alert !== undefined)
                        alert(res.responseJSON.alert)

                    if (res.responseJSON.redirect !== undefined)
                        location.href = res.responseJSON.redirect

                } else if (res.status !== 200 && res.status !== 204)
                    alert('Impossibile completare la richiesta.')

            } else {
                alert('Impossibile completare la richiesta.')
            }
        }
    })
}
