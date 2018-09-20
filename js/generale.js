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
            console.log('ServiceWorker registration successful with scope: ', registration.scope)
        }, function (err) {
            // registration failed :(
            console.log('ServiceWorker registration failed: ', err)
        })
    })
}