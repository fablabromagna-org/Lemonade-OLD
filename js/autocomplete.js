$(document).ready(function () {

    $('.autocomplete:not(.no-auto)').each(function () {
        var requests = []

        $(this).keyup(function () {

            var autocompleteList = $(($(this).parent().parent().children('.autocomplete-list'))[0])
            var saveInput = $(this.dataset.realinput)

            if (this.value === '') {
                saveInput.val('')
                autocompleteList.html('')
                autocompleteList.addClass('invisible')
                requests[requests.length - 1] = false
                return
            }

            if (this.value.length < 3) {
                autocompleteList.html('')
                autocompleteList.addClass('invisible')
                requests[requests.length - 1] = false
                return
            }

            $($(this).parent()[0]).addClass('is-loading')

            var _self = this

            var dati = {
                ricerca: $(this).val().toUpperCase()
            }

            var id = null

            $.ajax({
                method: 'post'
                , url: _self.dataset.url
                , dataType: 'json'
                , cache: false
                , contentType: 'application/json; charset=utf-8'
                , data: JSON.stringify(dati)
                , beforeSend: function () {
                    id = requests.length
                    requests[id] = true

                    if (id !== 0)
                        if (requests[id - 1] !== false)
                            requests[id - 1] = false
                }
                , complete: function (res) {

                    if (requests[id] === false)
                        return

                    if (res.responseJSON !== undefined) {

                        autocompleteList.empty()

                        var str = document.createElement('p')

                        if (res.responseJSON.length === 0)
                            str.innerText = 'Nessun risultato corrispondente.'

                        else if (res.responseJSON.length === 1)
                            str.innerHTML = '<b>Un risultato</b> corrispondente alla ricerca <b>"' + dati.ricerca + '"</b>:'

                        else if (res.responseJSON.length > 1)
                            str.innerHTML = '<b>' + res.responseJSON.length + ' risultati</b> corrispondenti alla ricerca <b>"' + dati.ricerca.toUpperCase() + '"</b>:'

                        autocompleteList.append(str)

                        $.each(res.responseJSON, function () {

                            var li = document.createElement('li')
                            var a = document.createElement('a')

                            var data = this

                            var ricerca = data.text.replace(dati.ricerca, '<b>' + dati.ricerca + '</b>')

                            a.innerHTML = ricerca

                            a.addEventListener('click', function () {
                                saveInput.val(data.value)
                                _self.value = data.text
                                autocompleteList.addClass('invisible')
                            })

                            li.appendChild(a)
                            autocompleteList.append(li)
                        })

                        autocompleteList.removeClass('invisible')
                    }

                    $($(_self).parent()[0]).removeClass('is-loading')
                }
            })
        })
    })
})
