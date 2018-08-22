<?php

namespace FabLabRomagna {

    /**
     * Class Log
     *
     * @package FabLabRomagna
     *
     * @property-read $id_log
     * @property-read $id_utente
     * @property-read $ip
     * @property-read $pacchetto
     * @property-read $oggetto
     * @property-read $testo
     * @property-read $debug
     * @property-read $ts
     * @property-read $livello
     */
    class Log
    {
        /**
         * Tabella dove salvare i log
         */
        protected const TABLE_NAME = 'log';


        /**
         * @var int $id_log ID del log
         */
        protected $id_log;


        /**
         * @var int|null $id_utente ID dell'utente generatore del log
         */
        protected $id_utente;


        /**
         * @var string $ip Indirizzo IP
         */
        protected $ip;


        /**
         * @var string $pacchetto Pacchetto dell'oggetto generatore
         */
        protected $pacchetto;


        /**
         * @var string $oggetto Oggetto generatore
         */
        protected $oggetto;


        /**
         * @var string $testo Testo dell'errore
         */
        protected $testo;


        /**
         * @var string $debug Eventuale testo di debug prodotto da PHP
         */
        protected $debug;


        /**
         * @var int $ts Unix Time Stamp dell'inserimento del log
         */
        protected $ts;


        /**
         * @var int $livello Livello del log (0 = trace, 1 = info, 2 = warn, 3 = error)
         */
        protected $livello;

        /**
         * Log constructor.
         *
         * @param int      $id_log    ID del log
         * @param int      $livello   Livello del log (0 = trace, 1 = info, 2 = warn, 3 = error)
         * @param int|null $id_utente ID dell'utente generatore del log
         * @param string   $ip        Indirizzo IP
         * @param string   $pacchetto Pacchetto dell'oggetto generatore
         * @param string   $oggetto   Oggetto generatore
         * @param string   $testo     Testo dell'errore
         * @param string   $debug     Eventuale testo di debug prodotto da PHP
         * @param int      $ts        Unix Time Stamp dell'inserimento del log
         */
        public function __construct($id_log, $livello, $id_utente, $ip, $pacchetto, $oggetto, $testo, $debug, $ts)
        {
            $this->id_log = $id_log;
            $this->id_utente = $id_utente;
            $this->ip = $ip;
            $this->pacchetto = $pacchetto;
            $this->oggetto = $oggetto;
            $this->testo = $testo;
            $this->debug = $debug;
            $this->ts = $ts;
        }

        /**
         * @param string $name Nome della proprietà
         *
         * @throws \Exception
         *
         * @return mixed
         */
        public function __get($name)
        {
            if (property_exists($this, $name)) {
                return $this->{$name};
            }

            throw new \Exception('Undefined property');
        }

        /**
         * Metodo per creare un nuovo record nel registro dei log
         * A causa della sua natura (viene utilizzato per registrare anche le eccezioni)
         * non lancia eccezioni ma ritorna false o l'oggetto del log appena creato per indicare lo stato
         *
         * @param Utente|null $utente    Utente per cui registrare il log
         * @param int         $livello   Livello del log (0 = trace, 1 = info, 2 = warn, 3 = error)
         * @param string      $pacchetto Pacchetto dell'oggetto generatore
         * @param string      $oggetto   Oggetto generatore
         * @param string      $testo     Testo dell'errore
         * @param string|null $debug     Eventuale testo di debug prodotto da PHP
         * @param string|null $ip        Indirizzo IP (se non specificato viene utilizzato quello ricavato dal Firewall)
         *
         * @global \mysqli    $mysqli    Connessione al database
         *
         * @return Log|false
         */
        public static function crea($utente, $livello, $pacchetto, $oggetto, $testo, $debug = null, $ip = null)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                return false;
            }

            if (!is_a($utente, 'FabLabRomagna\Utente') && $utente !== null) {
                return false;
            }

            if (gettype($livello) !== 'integer' || $livello < 0 || $livello > 3) {
                return false;
            }

            if (gettype($pacchetto) !== 'string' || gettype($oggetto) !== 'string' || gettype($testo) !== 'string') {
                return false;
            }

            if (gettype($debug) !== 'string' && $debug !== null) {
                return false;
            }

            if (gettype($ip) !== 'string' && $ip !== null) {
                return false;
            }

            if (filter_var($ip, FILTER_VALIDATE_IP) === false && $ip !== null) {
                return false;
            }

            if ($ip === null) {
                $ip = Firewall::get_valid_ip();
            }

            $sql = "INSERT INTO " . self::TABLE_NAME . " (id_utente, livello, pacchetto, oggetto, testo, debug, ip, ts) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                return false;
            }

            $ts = time();
            if (!$stmt->bind_param('iisssssi', $utente->id_utente, $livello, $pacchetto, $oggetto,
                $testo, $debug, $ip, $ts)) {
                return false;
            }

            if (!$stmt->execute()) {
                return false;
            }

            return new Log($stmt->insert_id, $livello, $utente->id_utente, $ip, $pacchetto, $oggetto,
                $testo, $debug, $ts);
        }
    }
}