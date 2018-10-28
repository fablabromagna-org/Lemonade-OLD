<?php

namespace FabLabRomagna {

    /**
     * Class OggettoRegistro
     *
     * @package FabLabRomagna
     */
    class OggettoRegistro
    {
        /**
         * @var int $id_oggetto ID dell'oggetto di registro
         */
        protected $id_oggetto;


        /**
         * @var int $ts_inserimento Unix Time Stamp dell'inserimento dell'oggetto di registro
         */
        protected $ts_inserimento;


        /**
         * @var string $pacchetto Pacchetto generatore
         */
        protected $pacchetto;


        /**
         * @var string $oggetto Oggetto di cui tener traccia
         */
        protected $oggetto;


        /**
         * @var string $note Eventuali note, non standardizzate
         */
        protected $note;


        /**
         * @var string $ip IP generatore dell'evento
         */
        protected $ip;

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
         * @param mixed $name
         * @param mixed $value
         *
         * @throws \Exception
         */
        public function __set($name, $value)
        {
            throw new \Exception();
        }

        /**
         * OggettoRegistro constructor.
         *
         * @param string $id_oggetto     ID dell'oggetto di registro
         * @param string $ts_inserimento Unix Time Stamp dell'inserimento dell'oggetto di registro
         * @param string $pacchetto      Pacchetto generatore
         * @param string $oggetto        Oggetto di cui tener traccia
         * @param string $note           Eventuali note, non standardizzate
         * @param string $ip             IP generatore dell'evento
         */
        public function __construct($id_oggetto, $ts_inserimento, $pacchetto, $oggetto, $note, $ip)
        {
            $this->id_oggetto = $id_oggetto;
            $this->ts_inserimento = $ts_inserimento;
            $this->pacchetto = $pacchetto;
            $this->oggetto = $oggetto;
            $this->note = $note;
            $this->ip = $ip;
        }

        /**
         * Metodo per cercare tutti gli eventi (non scaduti) associati ad un dato IP
         * per un certo oggetto
         *
         * @param string $pacchetto Pacchetto dell'oggetto generatore
         * @param string $oggetto   Oggetto generatore
         * @param string $ip        Indirizzo IP per cui registrare il fallimento
         * @param int    $scadenza  Numero di secondi per il quale vale il fallimento (default: 900, 15 minuti)
         *
         * @return OggettoRegistro[]
         *
         * @throws \Exception
         */
        public static function ricerca_da_ip($pacchetto, $oggetto, $ip, $scadenza = 900)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            if (gettype($pacchetto) !== 'string') {
                throw new \Exception('Expected string in $pacchetto');
            }

            if (gettype($oggetto) !== 'string') {
                throw new \Exception('Expected string in $oggetto');
            }

            if (gettype($ip) !== 'string' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
                throw new \Exception('Expected IP (as string) in $ip');
            }

            $ts = time() - $scadenza;
            $sql = "SELECT * FROM registro WHERE pacchetto = ? AND oggetto = ? AND ip = ? AND ts_inserimento >= ? ORDER BY id_oggetto ASC";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the statment!');
            }

            if (!$stmt->bind_param('sssi', $pacchetto, $oggetto, $ip, $ts)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the statment!');
            }

            $res = $stmt->get_result();
            $stmt->close();

            $tmp = [];

            while ($row = $res->fetch_assoc()) {
                $tmp[] = new OggettoRegistro($row['id_oggetto'], $row['ts_inserimento'], $row['pacchetto'],
                    $row['oggetto'],
                    $row['note'], $row['ip']);
            }

            return $tmp;
        }

        /**
         * Metodo per registrare un nuovo oggetto di registro
         *
         * @param string   $pacchetto Pacchetto dell'oggetto generatore
         * @param string   $oggetto   Oggetto generatore
         * @param string   $ip        Indirizzo IP per cui registrare l'oggetto di registro
         * @param string   $note      Eventuali note (nessuno schema specificato)
         *
         * @global \mysqli $mysqli    Conessione al database
         *
         * @return OggettoRegistro
         *
         * @throws \Exception
         */
        public static function crea($pacchetto, $oggetto, $ip, $note = '')
        {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            if (gettype($pacchetto) !== 'string') {
                throw new \Exception('Expected string in $pacchetto');
            }

            if (gettype($oggetto) !== 'string') {
                throw new \Exception('Expected string in $oggetto');
            }

            if (gettype($ip) !== 'string' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
                throw new \Exception('Expected IP (as string) in $ip');
            }

            if (gettype($note) !== 'string') {
                throw new \Exception('Expected string in $note');
            }

            $sql = "INSERT INTO registro (ts_inserimento, pacchetto, oggetto, note, ip) VALUES (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the statment!');
            }

            $ts = time();
            if (!$stmt->bind_param('issss', $ts, $pacchetto, $oggetto, $note, $ip)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the statment!');
            }

            return new OggettoRegistro($stmt->insert_id, $ts, $pacchetto, $oggetto, $note, $ip);
        }
    }
}
