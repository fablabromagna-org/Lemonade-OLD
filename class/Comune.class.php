<?php

namespace FabLabRomagna {


    /**
     * Class Comune
     *
     * @package FabLabRomagna
     */
    class Comune
    {
        /**
         * @var string $nome Nome del comune o dello stato estero
         */
        protected $nome;


        /**
         * @var string $codice_belfiore Codice Belfiore del comune o dello stato estero
         */
        protected $codice_belfiore;


        /**
         * @var bool $is_stato_estero Se vero non è un comune ma uno stato estero
         */
        protected $is_stato_estero;


        /**
         * Nome della tabella nel database
         */
        protected const TABLE_NAME = 'comuni';

        /**
         * Comune constructor.
         *
         * @param string $nome            Nome del comune o dello stato estero
         * @param string $codice_belfiore Codice Belfiore del comune o dello stato estero
         * @param bool   $is_stato_estero Se vero non è un comune ma uno stato estero (default:_false)
         */
        public function __construct($nome, $codice_belfiore, $is_stato_estero = false)
        {
            $this->codice_belfiore = $codice_belfiore;
            $this->is_stato_estero = $is_stato_estero;
            $this->nome = $nome;
        }

        /**
         * Utilizzato per estrarre le proprietà
         *
         * @param string $name Nome della proprietà
         *
         * @throws \Exception
         *
         * @return mixed
         */
        public function __get($name)
        {
            switch ($name) {
                case 'codice_belfiore':
                    return $this->codice_belfiore;

                case 'nome':
                    return $this->nome;

                case 'is_stato_estero':
                    return $this->is_stato_estero;

                default:
                    throw new \Exception('Property not found!');
            }
        }

        /**
         * Questi valori non sono modificabili
         *
         * @param mixed $name
         * @param mixed $value
         *
         * @throws \Exception
         */
        public function __set($name, $value)
        {
            throw new \Exception('You cannot set this properties!');
        }

        /**
         * Permette la ricerca di un comune sapendo il codice belfiore o il nome dello stesso
         *
         * @param string $campo  Nome (nome) o Codice Belfiore (codice_belfiore)
         * @param string $valore Il valore da ricercare
         * @param bool   $simile (valido solo con il nome) Se true effettua una ricerca per similitudine
         *                       invece che uguaglianza
         *
         * @return Comune[]
         *
         * @throws \Exception
         */
        public static function trova_comune_by($campo, $valore, $simile = false)
        {

            if (gettype($campo) !== 'string' || gettype($valore) !== 'string') {
                throw new \Exception('$campo e $valore sono due strighe!');
            }

            if (gettype($simile) !== 'boolean') {
                throw new \Exception('$simile è un valore booleano!');
            }

            if ($campo === 'nome') {
                return self::trova_comune_by_nome($valore, $simile);
            } elseif ($campo === 'codice_belfiore') {
                return self::trova_comune_by_belfiore($valore);
            } else {
                throw new \Exception('Field not valid!');
            }
        }


        /**
         * Dato il nome trova i comuni compatibili con la ricerca effettuata
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @param string   $nome   Nome del comune
         * @param bool     $simile Indica se la ricerca deve essere fatta per somiglianza o uguaglianza
         *
         * @throws \Exception
         *
         * @return \FabLabRomagna\Comune[]
         */
        protected static function trova_comune_by_nome($nome, $simile)
        {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            $nome = mb_strtoupper($nome);

            $s = $simile ? 'LIKE ?' : '= ?';

            $sql = $mysqli->prepare("SELECT * FROM " . self::TABLE_NAME . " WHERE comune $s OR stato $s");
            $sql->bind_param('ss', $nome, $nome);


            if (!$sql->execute()) {
                throw new \Exception('Impossibile eseguire la query!');
            }

            $tmp = [];

            $result = $sql->get_result();

            while ($row = $result->fetch_assoc()) {

                $nome = $row['stato'] == null ? $row['comune'] : $row['stato'];
                $stato = $row['stato'] == null ? false : true;

                $tmp[] = new Comune($nome, $row['belfiore'], $stato);
            }

            return $tmp;
        }

        /**
         * Dato il codice Belfiore trova i comuni compatibili con la ricerca effettuata
         *
         * @global \mysqli $mysqli          Connessione al database
         *
         * @param string   $codice_belfiore Codice Belfiore da utilizzare per la ricerca
         *
         * @return \FabLabRomagna\Comune[]
         *
         * @throws \Exception
         */
        protected static function trova_comune_by_belfiore($codice_belfiore)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli'))
                throw new \Exception('MySQLi as global variable expected!');

            $sql = $mysqli->prepare("SELECT * FROM " . self::TABLE_NAME . " WHERE codiceCatastale = ?");

            if ($sql === false) {
                throw new \Exception('Impossibile preparare la query! ' . $mysqli->error);
            }

            $sql->bind_param('s', $codice_belfiore);

            if (!$sql->execute()) {
                throw new \Exception('Impossibile eseguire la query!');
            }

            $tmp = [];

            $result = $sql->get_result();

            while ($row = $result->fetch_assoc()) {

                $nome = $row['stato'] == null ? $row['comune'] : $row['stato'];
                $stato = $row['stato'] == null ? false : true;

                $tmp[] = new Comune($nome, $row['codiceCatastale'], $stato);
            }

            return $tmp;
        }
    }
}