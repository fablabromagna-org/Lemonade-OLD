<?php

namespace {

    require_once(__DIR__ . '/../vendor/autoload.php');
}

namespace FabLabRomagna {

    use FabLabRomagna\Data\DataGridFields;
    use FabLabRomagna\Data\TableHeader;
    use FabLabRomagna\EntiLocali\Comune;

    /**
     * Class Utente
     *
     * @package FabLabRomagna
     *
     * @author  Edoardo Savini <edoardo.savini@fablabromagna.org>
     *
     * @property-read $id_utente
     * @property-read $nome
     * @property-read $cognome
     * @property-read $email
     * @property-read $data_registrazione
     * @property-read $ip_registrazione
     * @property-read $sospeso
     * @property-read $codice_attivazione
     * @property-read $data_nascita
     * @property-read $codice_fiscale
     * @property-read $luogo_nascita
     * @property-read $sesso
     * @property-read $secretato
     * @property-read $id_foto
     */
    class Utente implements DataGridFields, Ricercabile
    {


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
            'secretato' => 'i',
            'id_foto' => 'i'
        ];

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
         * @var int|null $id_foto ID del file della foto
         */
        protected $id_foto;

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
                $this->luogo_nascita = Comune::trova_comune_by('codice_belfiore', $this->luogo_nascita)[0];
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

            $stmt = $mysqli->prepare("UPDATE utenti SET $campo = ? WHERE id_utente = " . $this->id_utente);

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
         * @return Utente $utente
         *
         * @throws \Exception
         */
        public static function crea_utente($dati)
        {

            $dati_utente = $dati;

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

            $stmt = $mysqli->prepare("INSERT INTO utenti ($cols) VALUES ($placeholders)");

            if ($stmt === false) {
                throw new \Exception('Impossibile preparare la query!');
            }

            $ref = new \ReflectionClass('mysqli_stmt');
            $obj = $ref->getMethod('bind_param');

            if (!$obj->invokeArgs($stmt, Ricerca::refValues($dati))) {
                throw new \Exception('Impossibile inserire i valori nella query!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Impossibile eseguire la query!');
            }

            $dati_utente['id_utente'] = $stmt->insert_id;

            return new Utente($dati_utente);
        }

        /**
         * Metodo per cercare uno o più utenti
         *
         * @param \FabLabRomagna\SQLOperator\SQLOperator[] $dati   Campi di ricerca
         * @param int|null                                 $limit  Lunghezza della ricerca
         * @param int|null                                 $offset Offset di ricerca
         * @param array|null                               $order  Ordinamento: [campo, ascendente] (default: ['id_utente', true] )
         *
         * @throws \Exception
         *
         * @return \FabLabRomagna\RisultatoRicerca
         */
        public static function ricerca(
            $dati,
            $limit = null,
            $offset = null,
            $order = [['id_utente', true]]
        ) {

            global $mysqli;

            $risultati = new Ricerca($mysqli,
                $dati,
                $limit,
                $offset,
                $order,
                self::PROP_UTENTE,
                'utenti');

            $res = [];

            foreach ($risultati->res as $row) {
                $res[] = new Utente([
                    'id_utente' => (int)$row['id_utente'],
                    'nome' => $row['nome'],
                    'cognome' => $row['cognome'],
                    'email' => $row['email'],
                    'data_registrazione' => (int)$row['data_registrazione'],
                    'ip_registrazione' => $row['ip_registrazione'],
                    'sospeso' => (bool)$row['sospeso'],
                    'codice_attivazione' => $row['codice_attivazione'],
                    'data_nascita' => $row['data_nascita'],
                    'codice_fiscale' => $row['codice_fiscale'],
                    'luogo_nascita' => $row['luogo_nascita'],
                    'sesso' => ($row['sesso'] === null) ? null : (bool)$row['sesso'],
                    'secretato' => (bool)$row['secretato'],
                    'id_foto' => $row['id_foto']
                ]);
            }

            $res = new RisultatoRicerca($res, $limit, $offset, $risultati->totale, $order);

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

                    if ($valore === null) {
                        return true;
                    }

                    if (gettype($valore) !== 'string') {
                        return false;
                    }

                    if (filter_var($valore, FILTER_VALIDATE_EMAIL) === false) {
                        return false;
                    }

                    return true;

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

                case 'id_foto':
                    if (gettype($valore) !== 'integer' && $valore !== null) {
                        return false;
                    }

                    if ($valore < 1 && $valore !== null) {
                        return false;
                    }

                    return true;

                default:
                    return false;
            }
        }

        /**
         * Metodo utilizzato dai figli di DataSet per ricavare tutte le proprietà
         *
         * @return array
         */
        public function getDataGridFields(): array
        {
            return get_object_vars($this);
        }

        /**
         * @param mixed $field
         *
         * @return mixed
         */
        public function HTMLDataGridFormatter($field)
        {
            switch ($field) {
                case 'id_utente':
                case 'nome':
                case 'cognome':
                    return '<a href="/gestione/utenti/utente.php?id=' . $this->id_utente . '">' . $this->{$field} . '</a>';

                case 'email':

                    if ($this->email === null) {
                        return '';
                    }

                    return '<a href="/gestione/utenti/utente.php?id=' . $this->id_utente . '">' . $this->{$field} . '</a>';

                case 'data_registrazione':
                    return $this->{$field} === null ? '' : date('d/m/Y H:i:s',
                        $this->{$field});

                case 'data_nascita':
                    return $this->{$field} === null ? '' : date('d/m/Y',
                        $this->{$field});

                case 'sospeso':
                case 'secretato':
                    return $this->sospeso ? 'Sì' : 'No';

                case 'codice_attivazione':
                    return $this->codice_attivazione === null ? 'Verificato' : 'Non verificato';

                case 'sesso':
                    return $this->sesso === null ? '' : ($this->sesso ? 'Femminile' : 'Maschile');

                default:
                    return $this->{$field};
            }
        }

        /**
         * @param mixed $field
         *
         * @return mixed
         */
        public function CSVDataGridFormatter($field)
        {
            switch ($field) {
                case 'data_registrazione':
                    return $this->{$field} === null ? '' : date('d/m/Y H:i:s',
                        $this->{$field});

                case 'data_nascita':
                    return $this->{$field} === null ? '' : date('d/m/Y',
                        $this->{$field});

                case 'sospeso':
                case 'secretato':
                    return $this->sospeso ? 'Sì' : 'No';

                case 'codice_attivazione':
                    return $this->codice_attivazione === null ? 'Verificato' : 'Non verificato';

                case 'sesso':
                    return $this->sesso === null ? '' : ($this->sesso ? 'Femminile' : 'Maschile');

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
                'id_utente' => new TableHeader('#', 'ID utente'),
                'nome' => new TableHeader('Nome'),
                'cognome' => new TableHeader('Cognome'),
                'email' => new TableHeader('E-Mail'),
                'data_registrazione' => new TableHeader('Data reg.', 'Data di registrazione'),
                'ip_registrazione' => new TableHeader('IP reg.', 'IP registrazione'),
                'sospeso' => new TableHeader('Sospeso'),
                'codice_attivazione' => new TableHeader('Verifica me'),
                'data_nascita' => new TableHeader('Data di nascita'),
                'codice_fiscale' => new TableHeader('Codice fiscale'),
                'luogo_nascita' => new TableHeader('Luogo di nascita'),
                'sesso' => new TableHeader('Sesso'),
                'secretato' => new TableHeader('Secretato'),
                'id_foto' => new TableHeader('ID foto')
            ];
        }
    }
}
