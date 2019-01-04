console.log('%cATTENZIONE!', 'color: red; font-weight: bold; font-size: 45px; font-family: sans-serif;')
console.log('%cQuesta è una funzione pensata per gli sviluppatori.\n' +
    'Se qualcuno ti dice di incollare qualcosa in questa finestra %cNON FARLO%c!\nStanno tentando di sottrarti dei dati ' +
    'sensibili o di accedere al tuo me!\nPer ulteriori informazioni: https://en.wikipedia.org/wiki/Self-XSS',
    'font-size: 25px; font-family: sans-serif;', 'font-weight: bold; font-size: 30px; font-family: sans-serif;', 'font-weight: regular; font-size: 25px; font-family: sans-serif;')

$(document).ready(function () {

    // Check for click events on the navbar burger icon
    $('.navbar-burger').click(function () {

        // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
        $('.navbar-burger').toggleClass('is-active')
        $('.navbar-menu').toggleClass('is-active')
    })
})

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/pwa.js').then(function (registration) {
            // Registration was successful
            //console.log('ServiceWorker registration successful with scope: ', registration.scope)
        }, function (err) {
            // registration failed :(
            console.log('ServiceWorker registration failed: ', err)
        })
    })
}
