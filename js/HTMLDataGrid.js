$(document).ready(function () {

    var colonne = $('.php_HTMLDataGrid')

    for (var i = 0; i < colonne.length; i++)
        colonne[i].addEventListener('click', colonna)

    function colonna () {

        if (this.dataset.order === undefined) {
            this.innerHTML += '<i class="fas fa-caret-down"></i>'
            this.dataset.order = '0'

        } else if (this.dataset.order === '0') {
            this.childNodes[1].remove()
            this.innerHTML += '<i class="fas fa-caret-up"></i>'
            this.dataset.order = '1'

        } else {
            this.childNodes[1].remove()
            delete this.dataset.order
        }

        ricaricaTabella()
    }

    var qs = new Url()
    console.log(qs)

    function ricaricaTabella () {
        var url = new Url()
        var qs = url.query

        var order = []
        for (var i = 0; i < colonne.length; i++) {
            if (colonne[i].dataset.order !== undefined) {
                order.push({
                    'column': colonne[i].dataset.column
                    , 'order': colonne[i].dataset.order
                })
            }
        }

        qs.order = JSON.stringify(order)

        url.query = qs
        location.href = url.toString()
    }

})
