<?php

namespace FabLabRomagna {

    use FabLabRomagna\Data\DataGridFields;
    use FabLabRomagna\Data\TableHeader;
    use FabLabRomagna\SQLOperator\SQLOperator;
    use FabLabRomagna\SQLOperator\Equals;

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
    class Log implements DataGridFields
    {
        /**
         * Elenco delle proprietà del log
         */
        protected const PROP_LOG = [
            'id_log' => 'i',
            'id_utente' => 'i',
            'ip' => 's',
            'pacchetto' => 's',
            'oggetto' => 's',
            'testo' => 's',
            'debug' => 's',
            'ts' => 'i',
            'livello' => 'i'
        ];


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
            $this->livello = $livello;
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

            $sql = "INSERT INTO log (id_utente, livello, pacchetto, oggetto, testo, debug, ip, ts) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
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

        /**
         * @param SQLOperator[] $dati
         * @param null          $limit
         * @param null          $offset
         * @param array         $order
         *
         * @return RisultatoRicerca
         *
         * @throws \Exception
         */
        public static function ricerca(
            $dati,
            $limit = null,
            $offset = null,
            $order = [['id_log', false]]
        ) {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            if (gettype($dati) !== 'array') {
                throw new \Exception('$dati deve essere un array!');
            }

            if ($offset !== null && gettype($offset) !== 'integer') {
                throw new \Exception('Invalid offset!');
            }

            if ($limit !== null && gettype($limit) !== 'integer') {
                throw new \Exception('Invalid limit');
            }

            if ($limit === null && $offset !== null) {
                throw new \Exception('Offset requires limit!');
            }

            if ((gettype($order) !== 'array') && $order !== null) {
                throw new \Exception('Invalid order!');
            }

            $tipi = '';
            $dati_sql = [];
            $where_query = [];

            foreach ($dati as $campo_ricerca) {
                if (is_subclass_of($campo_ricerca, 'FabLabRomagna\SQLOperator\SQLOperator')) {
                    if (isset(self::PROP_LOG[$campo_ricerca->colonna])) {
                        $tipi .= $campo_ricerca->get_type();
                        $where_query[] = $campo_ricerca->get_sql();
                        $dati_sql[] = $campo_ricerca->valore;
                    }
                }
            }

            $where_query = implode(' AND ', $where_query);
            $calc = '';

            if ($limit !== null) {
                $calc = ' SQL_CALC_FOUND_ROWS';
            }

            $query = "SELECT" . $calc . " * FROM log";

            if ($where_query !== '') {
                $query .= ' WHERE ' . $where_query;
            }

            if ($order !== null) {

                $is_first = true;
                foreach ($order as $value) {
                    if (isset(self::PROP_LOG[$value[0]])) {
                        $t = $value[1] ? 'ASC' : 'DESC';

                        if ($is_first) {
                            $is_first = false;
                            $query .= ' ORDER BY';
                        } else {
                            $query .= ',';
                        }

                        $query .= ' ' . $value[0] . ' ' . $t;
                    }
                }
            }

            if ($limit !== null) {
                $query .= ' LIMIT ' . $limit;
            }

            if ($offset !== null) {
                $query .= ' OFFSET ' . $offset;
            }

            $stmt = $mysqli->prepare($query);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!' . $query);
            }

            if ($tipi !== '') {
                $ref = new \ReflectionClass('mysqli_stmt');
                $obj = $ref->getMethod('bind_param');

                $tmp = array_merge(array($tipi), $dati_sql);

                if (!$obj->invokeArgs($stmt, Utente::refValues($tmp))) {
                    throw new \Exception('Impossibile inserire i valori nella query!');
                }
            }

            if (!$stmt->execute()) {
                throw new \Exception('Impossibile eseguire la query!');
            }

            $risultati = $stmt->get_result();

            if ($limit !== null) {
                $stmt->close();
                $sql = "SELECT FOUND_ROWS() AS 'totale'";
                $stmt = $mysqli->query($sql);

                $row = $stmt->fetch_assoc();

                $totale = (int)$row['totale'];
            } else {
                $totale = $risultati->num_rows;
            }

            $res = [];

            while ($row = $risultati->fetch_assoc()) {
                $res[] = new Log(
                    $row['id_log'],
                    $row['livello'],
                    $row['id_utente'],
                    $row['ip'],
                    $row['pacchetto'],
                    $row['oggetto'],
                    $row['testo'],
                    $row['debug'],
                    $row['ts']
                );
            }

            $res = new RisultatoRicerca($res, $limit, $offset, $totale, $order);

            $stmt->close();

            return $res;
        }

        /**
         * @return array
         */
        public function getDataGridFields(): array
        {
            return get_object_vars($this);
        }

        /**
         * @param mixed $field
         *
         * @return false|mixed|string
         * @throws \Exception
         */
        public function HTMLDataGridFormatter($field)
        {
            switch ($field) {
                case 'id_log':
                    return '<a href="/gestione/log.php?id=' . $this->id_log . '">' . $this->id_log . '</a>';

                case 'id_utente':

                    if ($this->id_utente === null) {
                        return 'N/D';
                    }

                    $utente = Utente::ricerca([
                        new Equals('id_utente', $this->id_utente)
                    ]);

                    if (count($utente) == 0) {
                        return $this->id_utente;
                    }

                    $utente = $utente->risultato[0];

                    return '<a href="/gestione/utenti/utente.php?id=' . $utente->id_utente . '">' . $utente->nome . ' ' . $utente->cognome . '</a>';

                case 'ts':
                    return $this->{$field} === null ? '' : date('d/m/Y H:m:i',
                        $this->{$field});

                case 'livello':

                    if ($this->livello == 1) {
                        return '<span class="tag is-info">INFO</span>';
                    }

                    if ($this->livello == 2) {
                        return '<span class="tag is-warn">WARN</span>';
                    }

                    if ($this->livello == 3) {
                        return '<span class="tag is-danger">ERROR</span>';
                    }

                    return '<span class="tag is-light">TRACE</span>';

                default:
                    return $this->{$field};
            }
        }

        /**
         * Metodo che restituisce tutte le intestazioni di tabella
         *
         * @return array
         */
        public static function getDataGridTableHeaders()
        {
            return [
                'id_log' => new TableHeader('#', 'ID log'),
                'id_utente' => new TableHeader('Utente'),
                'pacchetto' => new TableHeader('Pacchetto'),
                'oggetto' => new TableHeader('Oggetto'),
                'ts' => new TableHeader('Data e ora'),
                'debug' => new TableHeader('Dati di debug'),
                'ip' => new TableHeader('IP dell\'utente'),
                'livello' => new TableHeader('Livello'),
                'testo' => new TableHeader('Descrizione')
            ];
        }
    }
}
