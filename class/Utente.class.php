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
            'id' => 'i',
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
         * @var int $id ID dell'utente generato dal database
         */
        protected $id;


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
                throw new \Exception('Utente non costruito correrttamente!');
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
                var_dump($campo, self::valida_campo($campo, $this->{$campo}));

                if (!self::valida_campo($campo, $this->{$campo})) {
                    return false;
                }
            }

            return true;
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

            if ($campo === 'id') {
                throw new \Exception('You can\'t assign ID!');
            }

            if (!self::valida_campo($campo, $valore) || !property_exists($this, $campo)) {
                throw new \Exception('Campo ' . $campo . ' con valore \'' . $valore . '\' inesistente o non valido!');
            }

            $stmt = $mysqli->prepare("UPDATE " . self::TABLE_NAME . " SET $campo = ? WHERE id = " . $this->id);

            if ($stmt === false) {
                throw new \Exception('Impossibile preparare la query!');
            }

            if (!$stmt->bind_param(self::PROP_UTENTE[$campo], $valore)) {
                throw new \Exception('Impossibile completare la query!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Impossibile eseguire la query!');
            }
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
                if (!self::valida_campo($campo, $dati[$campo]) && $campo !== 'id') {
                    throw new \Exception('Campo ' . $campo . ' con valore \'' . $dati[$campo] . '\' inesistente o non valido!');
                }
            }

            if (array_key_exists('id', $dati)) {
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

            if (!$obj->invokeArgs($stmt, $dati)) {
                throw new \Exception('Impossibile inserire i valori nella query!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Impossibile eseguire la query!');
            }
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
                case 'id':

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

                    return (bool)preg_match('/^[\p{L} \']{3,25}$/', $valore);

                case 'email':

                    if (gettype($valore) != 'string') {
                        return false;
                    }

                    if (filter_var($valore, FILTER_VALIDATE_EMAIL) === false) {
                        return false;
                    } else {
                        return true;
                    }

                case 'password':

                    if (gettype($valore) !== 'string') {
                        return false;
                    }

                    // Deve contenere almeno un carattere speciale, una lettera maiuscola,
                    // Una lettera minuscola e un numero
                    // Lunghezza minima 6 caratteri
                    return (bool)preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\p{L}\p{D}]).{6,}$/', $valore);

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
    }
}