<?php
define('ELENCO_PERMESSI', [
    'fabcoin.visualizzare_transazioni_proprie' => [
        'default' => true,
        'richiede_pannello_gestione' => false,
        'nome' => 'Visualizzazione transazioni valuta FabCoin',
        'descrizione' => 'Indica se l\'utente può visualizzare le proprie transazioni in valuta FabCoin, comprende la visualizzazione del proprio saldo.'

    ],
    'attivita.visualizzare_proprie' => [
        'default' => true,
        'richiede_pannello_gestione' => false,
        'nome' => 'Visualizzazione le proprie attività',
        'descrizione' => 'Indica se l\'utente può visualizzare le proprie attività svolte.'
    ]
]);