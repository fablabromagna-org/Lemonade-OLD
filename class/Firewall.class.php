<?php

namespace {

    require_once(__DIR__ . '/../vendor/autoload.php');
}

namespace FabLabRomagna {

    /**
     * Class Firewall
     *
     * @package FabLabRomagna
     */
    class Firewall
    {
        /**
         * Indirizzo della pagina per la quale va effettuata la redirect
         */
        protected const REDIRECT = '/firewall.html';

        /**
         * Metodo per aggiungere una regola al firewall
         *
         * @param string   $ip       Indirizzo IP da bloccare
         * @param int      $cidr     Suffisso CIDR (default: 32)
         * @param string   $action   Azione da eseguire (default: allow)
         * @param int|null $scadenza Scadenza della regola (default: null)
         *
         * @global \mysqli $mysqli   Connessione al database
         *
         * @return int|null ID della regola, null se la regola è stata ignorata
         *
         * @throws \Exception
         */
        public static function aggiungi_regola($ip, $cidr = 32, $action = 'accept', $scadenza = null)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
                throw new \Exception('Invalid IP!');
            }

            if (gettype($cidr) !== 'integer' || $cidr < 0 || $cidr > 32) {
                throw new \Exception('Invalid CIDR!');
            }

            if ($action !== 'accept' && $action !== 'reject') {
                throw new \Exception('Invalid action!');
            }

            if (gettype($scadenza) !== 'integer' && $scadenza !== null) {
                throw new \Exception('Invalid exipiration!');
            }

            $scadenza = time() + $scadenza;

            // Controllo se la regola esiste già
            // Nel caso ignoro l'inserimento
            $sql = "SELECT * FROM firewall WHERE ip = ? AND cidr = ? AND (ts_scadenza >= ? OR ts_scadenza IS NULL)";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('sii', $ip, $cidr, $scadenza)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the statment!');
            }

            $res = $stmt->get_result();

            if ($res->num_rows !== 0) {
                return null;
            }

            $stmt->close();

            // Inserisco la regola
            $sql = "INSERT INTO firewall (action, ip, cidr, ts_scadenza) VALUES (?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('ssii', $action, $ip, $cidr, $scadenza)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the statment!');
            }

            return $stmt->insert_id;
        }

        /**
         * Metodo per effettuare un controllo sul client
         * se non viene indentificata la sorgente della richiesta
         * viene restituisce false
         *
         * @param string|null $ip     IP da controllare (default: null, viene ricavato quello attualmente
         *                            utilizzato dall'utente)
         *
         * @global \mysqli    $mysqli Connessione al database
         *
         * @return bool
         *
         * @throws \Exception
         */
        public static function controllo($ip = null)
        {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if ($ip === null) {
                $ip = self::get_valid_ip();
            } elseif (filter_var($ip, FILTER_VALIDATE_IP) === false) {
                throw new \Exception('Invalid IP!');
            }

            if ($ip === false) {
                return false;
            }

            $sql = "SELECT * FROM firewall WHERE ts_scadenza > ? OR ts_scadenza IS NULL ORDER BY cidr ASC, id_regola_firewall ASC";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            $ts = time();
            if (!$stmt->bind_param('i', $ts)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $res = $stmt->get_result();
            $stmt->close();

            $regole = [];

            while ($row = $res->fetch_assoc()) {
                $regole[] = $row;
            }

            $res = true;

            foreach ($regole as $item) {

                if (strpos($item['ip'], '.') !== false) {
                    $ipRange = new \Vectorface\Whip\IpRange\Ipv4Range($item['ip'] . '/' . $item['cidr']);
                } else {
                    $ipRange = new \Vectorface\Whip\IpRange\Ipv6Range($item['ip'] . '/' . $item['cidr']);
                }

                if ($ipRange->containsIp($ip)) {
                    $res = $item['action'] === 'accept';
                }
            }

            return $res;
        }

        /**
         * Per ora effettua solo una semplice redirect all'indirizzo configurato
         */
        public static function firewall_redirect()
        {
            header('Location: ' . self::REDIRECT);
        }

        /**
         * Per ora è solo un collegamento a \Vectorface\Whip\Whip -> getValidIpAddress
         *
         * @return string|false
         */
        public static function get_valid_ip()
        {
            return (new \Vectorface\Whip\Whip())->getValidIpAddress();
        }
    }
}