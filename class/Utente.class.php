<?php

namespace {

    require_once('../vendor/autoload.php');
}

namespace FabLabRomagna {

    /**
     * Class Utente
     *
     * @package FabLabRomagna
     *
     * @author  Edoardo Savini <edoardo.savini@fablabromagna.org>
     *
     * @property-read $id_utente
     * @property      $nome
     * @property      $cognome
     * @property      $email
     * @property      $data_registrazione
     * @property      $ip_registrazione
     * @property      $sospeso
     * @property      $codice_attivazione
     * @property      $data_nascita
     * @property      $codice_fiscale
     * @property      $luogo_nascita
     * @property      $sesso
     * @property      $secretato
     */
    class Utente
    {

        /**
         * Nome della tabella
         */
        protected const TABLE_NAME = 'utenti';


        /**
         * Elenco delle proprietà dell'utente
         */
        protected const PROP_UTENTE = [
            'id_utente' => 'i',
            'nome' => 's',
            'cognome' => 's',
            'email' => 's',
            'data_registrazione' => 'i',
            'ip_registrazione' => 's',
            'sospeso' => 'i',
            'codice_attivazione' => 's',
            'data_nascita' => 'i',
            'codice_fiscale' => 's',
            'luogo_nascita' => 's',
            'sesso' => 'i',
            'secretato' => 'i'
        ];

        /**
         * @var int $id_utente ID dell'utente generato dal database
         */
        protected $id_utente;


        /**
         * @var string $nome Nome dell'utente
         */
        protected $nome;


        /**
         * @var string $cognome Cognome dell'utente
         */
        protected $cognome;


        /**
         * @var string $email E-Mail dell'utente
         */
        protected $email;


        /**
         * @var int $data_registrazione Unix Time Stamp della data di registrazione
         */
        protected $data_registrazione;


        /**
         * @var string $ip_registrazione IP al momento della registrazione
         */
        protected $ip_registrazione;


        /**
         * @var bool $sospeso Indica se l'utente è sospeso
         */
        protected $sospeso;


        /**
         * @var string|null $codice_attivazione Se diverso da null contiene il codice di attivazione dell'indirizzo email
         *                                      attualmente associato all'account
         */
        protected $codice_attivazione;


        /**
         * @var int|null $data_nascita Unix Time Stamp della data di nascita dell'utente
         */
        protected $data_nascita;


        /**
         * @var string|null $codice_fiscale Codice fiscale dell'utente
         */
        protected $codice_fiscale;


        /**
         * @var Comune|null $luogo_nascita Luogo di nascita dell'utente
         */
        protected $luogo_nascita;


        /**
         * @var bool|null $sesso Sesso dell'utente
         */
        protected $sesso;


        /**
         * @var bool $secretato Se vero i dati del profilo vanno sostituiti con degli asterischi per tutto il personale
         *                      non autorizzato
         */
        protected $secretato;


        /**
         * Utente constructor.
         *
         * @param array $dati
         *
         * @throws \Exception
         */
        public function __construct($dati)
        {
            if (gettype($dati) != 'array') {
                throw new \Exception('Array expected!');
            }

            foreach ($dati as $campo => $valore) {
                if (self::valida_campo($campo, $valore)) {
                    $this->{$campo} = $valore;
                } else {
                    throw new \Exception('Campo ' . $campo . ' con valore \'' . $valore . '\' inesistente o non valido!');
                }
            }

            if ($this->is_corrupted()) {
                throw new \Exception('Utente non costruito correttamente!');
            }

            if ($this->luogo_nascita !== null) {
                $this->luogo_nascita = Comune::trova_comune_by('belfiore', $this->luogo_nascita);
            }
        }

        /**
         * Metodo per controllare se le proprietà dell'utente sono impostate correttamente
         *
         * @throws \Exception
         *
         * @return bool Ritorna true se l'oggetto ha delle proprietà con valore non valido
         */
        public function is_corrupted()
        {

            foreach (self::PROP_UTENTE as $campo => $valore) {
                if (!self::valida_campo($campo, $this->{$campo})) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Metodo per modificare un campo dell'utente
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @param string   $campo  Campo da modificare
         * @param string   $valore Valore da inserire
         *
         * @throws \Exception
         */
        public function set_campo($campo, $valore)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            if ($campo === 'id_utente') {
                throw new \Exception('You can\'t assign ID!');
            }

            if (!self::valida_campo($campo, $valore) || !property_exists($this, $campo)) {
                throw new \Exception('Campo ' . $campo . ' con valore \'' . $valore . '\' inesistente o non valido!');
            }

            $stmt = $mysqli->prepare("UPDATE " . self::TABLE_NAME . " SET $campo = ? WHERE id_utente = " . $this->id_utente);

            if ($stmt === false) {
                throw new \Exception('Impossibile preparare la query!');
            }

            if (!$stmt->bind_param(self::PROP_UTENTE[$campo], $valore)) {
                throw new \Exception('Impossibile completare la query!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Impossibile eseguire la query!');
            }

            $this->{$campo} = $valore;
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
         * @param mixed $name
         * @param mixed $value
         *
         * @throws \Exception
         */
        public function __set($name, $value)
        {
            if (!property_exists($this, $name)) {
                throw new \Exception('Undefined property');
            }

            $this->set_campo($name, $value);
        }

        /**
         * Metodo per la creazione di un nuovo utente
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @param array    $dati   Dizionario con i dati dell'utente da creare
         *
         * @throws \Exception
         */
        public static function crea_utente($dati)
        {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            if (gettype($dati) !== 'array') {
                throw new \Exception('Array expected!');
            }

            foreach (self::PROP_UTENTE as $campo => $valore) {

                if (!isset($dati[$campo]) && !self::valida_campo($campo, null) && $campo !== 'id_utente') {
                    throw new \Exception('Campo ' . $campo . ' inesitente o non valido!');

                } elseif ((isset($dati[$campo]) && !self::valida_campo($campo,
                            $dati[$campo])) && $campo !== 'id_utente') {
                    throw new \Exception('Campo ' . $campo . ' con valore \'' . $dati[$campo] . '\' inesistente o non valido!');
                }
            }

            if (array_key_exists('id_utente', $dati)) {
                throw new \Exception('You can\'t set user ID!');
            }

            $cols = implode(',', array_keys($dati));

            $tipi = '';
            $dati_sql = [];
            $placeholders = [];

            foreach ($dati as $campo => $valore) {
                $tipi .= self::PROP_UTENTE[$campo];
                $dati_sql[] = $valore;
                $placeholders[] = '?';
            }

            $dati = array_merge(array($tipi), $dati_sql);
            $placeholders = implode(',', $placeholders);

            $stmt = $mysqli->prepare("INSERT INTO " . self::TABLE_NAME . " ($cols) VALUES ($placeholders)");

            if ($stmt === false) {
                throw new \Exception('Impossibile preparare la query!');
            }

            $ref = new \ReflectionClass('mysqli_stmt');
            $obj = $ref->getMethod('bind_param');

            if (!$obj->invokeArgs($stmt, self::refValues($dati))) {
                throw new \Exception('Impossibile inserire i valori nella query!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Impossibile eseguire la query!');
            }
        }

        /**
         * Metodo per cercare uno o più utenti
         *
         * @param array      $dati           Campi di ricerca
         * @param bool       $case_insentive Case insensitive (default: true)
         * @param int|null   $limit          Lunghezza della ricerca
         * @param int|null   $offset         Offset di ricerca
         * @param array|null $order          Ordinamento: [campo, ascendente] (default: ['id_utente', true] )
         *
         * @throws \Exception
         *
         * @return \FabLabRomagna\RicercaUtente
         */
        public static function ricerca(
            $dati,
            $case_insentive = true,
            $limit = null,
            $offset = null,
            $order = ['id_utente', true]
        ) {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            if (gettype($dati) !== 'array') {
                throw new \Exception('$dati deve essere un array!');
            }

            if (gettype($case_insentive) !== 'boolean') {
                throw new \Exception('Invalid case insensitive option!');
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

            if ((gettype($order) !== 'array' || !isset($order[0]) || !isset($order[1])) && $order !== null) {
                throw new \Exception('Invalid order!');
            }

            $tipi = '';
            $dati_sql = [];
            $where_query = '';

            foreach ($dati as $campo => $valore) {

                $copia = str_replace('%', '', $valore);

                if (!self::valida_campo($campo, $copia)) {
                    unset($dati[$campo]);
                } else {
                    $tipi .= self::PROP_UTENTE[$campo];
                    $dati_sql[] = $valore;

                    $cs = '';

                    if ($case_insentive && self::PROP_UTENTE[$campo] === 's') {
                        $cs = 'COLLATE utf8mb4_unicode_ci ';
                    }

                    $where_query .= ' ' . $campo . ' ' . $cs . ' LIKE ? ';
                }
            }

            $calc = '';

            if ($limit !== null) {
                $calc = ' SQL_CALC_FOUND_ROWS';
            }

            $query = "SELECT" . $calc . " * FROM " . self::TABLE_NAME;

            if ($where_query !== '') {
                $query .= ' WHERE' . $where_query;
            }

            if ($order !== null) {
                $t = $order[1] ? 'ASC' : 'DESC';
                $query .= ' ORDER BY ' . $order[0] . ' ' . $t;
            }

            if ($limit !== null) {
                $query .= ' LIMIT ' . $limit;
            }

            if ($offset !== null) {
                $query .= ' OFFSET ' . $offset;
            }

            $stmt = $mysqli->prepare($query);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if ($tipi !== '') {
                $ref = new \ReflectionClass('mysqli_stmt');
                $obj = $ref->getMethod('bind_param');

                $tmp = array_merge(array($tipi), $dati_sql);

                if (!$obj->invokeArgs($stmt, self::refValues($tmp))) {
                    throw new \Exception('Impossibile inserire i valori nella query!');
                }
            }

            if (!$stmt->execute()) {
                throw new \Exception('Impossibile eseguire la query!');
            }

            $stmt = $stmt->get_result();

            $res = [];

            while ($row = $stmt->fetch_assoc()) {
                $res[] = new Utente([
                    'id_utente' => (int)$row['id_utente'],
                    'nome' => $row['nome'],
                    'cognome' => $row['cognome'],
                    'email' => $row['email'],
                    'data_registrazione' => (int)$row['data_registrazione'],
                    'ip_registrazione' => $row['ip_registrazione'],
                    'sospeso' => (bool)$row['sospeso'],
                    'codice_attivazione' => ($row['codice_attivazione'] == null) ? null : $row['codice_attivazione'],
                    'data_nascita' => ($row['data_nascita'] == null) ? null : (int)$row['data_nascita'],
                    'codice_fiscale' => ($row['codice_fiscale'] == null) ? null : $row['codice_fiscale'],
                    'luogo_nascita' => ($row['luogo_nascita'] == null) ? null : $row['luogo_nascita'],
                    'sesso' => ($row['sesso'] == null) ? null : $row['sesso'],
                    'secretato' => (bool)$row['secretato']
                ]);
            }

            $res = new RicercaUtente($res, $case_insentive, $limit, $offset, null, $order);

            if ($limit !== null) {

                $sql = "SELECT FOUND_ROWS() AS 'totale'";
                $stmt = $mysqli->prepare($sql);
                $stmt->execute();
                $stmt = $stmt->get_result();

                $row = $stmt->fetch_assoc();

                $res->total_rows = $row['totale'];
            } else {
                $res->total_rows = count($res->risultato);
            }

            $stmt->close();

            return $res;
        }

        /**
         * Permette il controllo dei valori in base al campo
         *
         * @param string $campo  Campo da verificare
         * @param string $valore Valore da verificare
         *
         * @throws \Exception
         *
         * @return bool
         */
        public static function valida_campo($campo, $valore)
        {

            switch ($campo) {
                case 'id_utente':

                    if (gettype($valore) !== 'integer') {
                        return false;
                    } elseif ($valore < 1) {
                        return false;
                    }

                    return true;

                case 'nome':
                case 'cognome':

                    if (gettype($valore) !== 'string') {
                        return false;
                    }

                    return (bool)preg_match('/^[\p{L} \']{3,25}$/u', $valore);

                case 'email':

                    if (gettype($valore) != 'string') {
                        return false;
                    }

                    if (filter_var($valore, FILTER_VALIDATE_EMAIL) === false) {
                        return false;
                    } else {
                        return true;
                    }

                case 'data_registrazione':
                    if (gettype($valore) !== 'integer') {
                        return false;
                    }

                    return true;

                case 'data_nascita':

                    if (gettype($valore) !== 'integer' && $valore !== null) {
                        return false;
                    }

                    return true;

                case 'ip_registrazione':

                    if (gettype($valore) !== 'string') {
                        return false;
                    } elseif (filter_var($valore, FILTER_VALIDATE_IP) === false) {
                        return false;
                    } elseif (filter_var($valore, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false && !DEBUG) {
                        return false;
                    } else {
                        return true;
                    }

                case 'sospeso':
                case 'secretato':

                    if (gettype($valore) !== 'boolean') {
                        return false;
                    }

                    return true;

                case 'codice_attivazione':

                    if (gettype($valore) !== 'string' && $valore !== null) {
                        return false;
                    } elseif ($valore === null) {
                        return true;
                    }

                    return (bool)preg_match('/^[a-f0-9]{13}$/', $valore);

                case 'codice_fiscale':

                    if (gettype($valore) !== 'string' && $valore !== null) {
                        return false;
                    } elseif ($valore === null) {
                        return true;
                    }

                    $cf = new \CodiceFiscale\Validator($valore);

                    return $cf->isFormallyValid();

                case 'luogo_nascita':

                    if (gettype($valore) !== 'string' && $valore !== null) {
                        return false;
                    } elseif ($valore === null) {
                        return true;
                    }

                    $res = Comune::trova_comune_by('codice_belfiore', $valore);

                    if (count($res) !== 1) {
                        return false;
                    }

                    return true;

                case 'sesso':

                    if (gettype($valore) !== 'boolean' && $valore !== null) {
                        return false;
                    }

                    return true;

                default:
                    return false;
            }
        }

        /**
         * Il metodo restituisce un vettore per riferimento
         *
         * @param $arr
         *
         * @return array
         */
        private static function refValues($arr)
        {
            if (strnatcmp(phpversion(), '5.3') >= 0) {
                $refs = array();
                foreach ($arr as $key => $value) {
                    $refs[$key] = &$arr[$key];
                }
                return $refs;
            }
            return $arr;
        }
    }

    /**
     * Class RicercaUtente
     *
     * @package FabLabRomagna
     *
     * @property Utente[]   $risultato
     * @property bool       $case_insensitive
     * @property int|null   $limit
     * @property int|null   $offset
     * @property int|null   $total_rows
     * @property array|null $order
     */
    class RicercaUtente
    {

        /**
         * @var Utente[] $risultato Risultato della ricerca
         */
        public $risultato;


        /**
         * @var bool $case_insensitive Case insensitive
         */
        public $case_insensitive;


        /**
         * @var int|null $offset Offset utilizzato nella ricerca
         */
        public $offset;


        /**
         * @var int|null $limit Lunghezza della ricerca
         */
        public $limit;


        /**
         * @var int|null $total_rows Numero di elementi totali corrispondenti alla ricerca
         */
        public $total_rows;


        /**
         * @var array|null $order Ordinamento: [$campo, $ascendente]
         */
        public $order;


        /**
         * RicercaUtente constructor.
         *
         * @param Utente[]   $risultato
         * @param bool       $case_insensitive
         * @param int|null   $limit
         * @param int|null   $offset
         * @param int|null   $total_rows
         * @param array|null $order
         */
        public function __construct(
            $risultato,
            $case_insensitive = true,
            $limit = null,
            $offset = null,
            $total_rows = null,
            $order = null
        ) {
            $this->risultato = $risultato;
            $this->case_insensitive = $case_insensitive;
            $this->offset = $offset;
            $this->limit = $limit;
            $this->total_rows = $total_rows;
            $this->order = $order;
        }
    }
}