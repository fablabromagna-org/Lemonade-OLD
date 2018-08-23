<?php
/**
 * Metodo per rispondere via API alle richieste
 *
 * @param int        $code                   Codice di stato HTTP
 * @param string     $reason                 Ragione del codice di stato HTTP
 * @param array|null $dati                   Eventuali dati di risposta (verranno codificati in JSON)
 * @param bool       $non_ricaricare_captcha Se true non elimina il codice CAPTCHA precedente dal server
 */
function reply($code = 204, $reason = 'No Content', $dati = null, $non_ricaricare_captcha = false)
{
    header('HTTP/1.1 ' . $code . ' ' . $reason);

    if (!$non_ricaricare_captcha) {
        unset($_SESSION['captcha']);
    }

    if ($dati !== null) {
        echo json_encode($dati);
    }

    exit();
}

/**
 * Risponde con l'header di JSON
 */
function json()
{
    header('Content-Type: application/json');
}

/**
 * Funzione per generare una stringa casuale da utilizzare con i captcha
 *
 * @param int $length Lunghezza della stringa da generare
 *
 * @return string
 */
function genera_testo_captcha($length = 6)
{
    $char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($char);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $char[rand(0, $charactersLength - 1)];
    }

    return $randomString;
}

?>