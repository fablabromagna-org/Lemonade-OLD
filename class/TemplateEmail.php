<?php
declare(strict_types=1);

namespace FabLabRomagna {

    /**
     * Class TemplateEmail
     *
     * @package FabLabRomagna
     *
     * @property-read string $nome
     * @property-read int    $id_file
     * @property-read string $file
     */
    class TemplateEmail
    {
        /**
         * Elenco delle proprietà dell'utente
         */
        protected const PROP_TEMPLATE = [
            'nome' => 's',
            'id_file' => 'i'
        ];

        /**
         * @var string $nome Nome del template
         */
        private $nome;


        /**
         * @var int $id_file ID del file del template
         */
        private $id_file;


        /**
         * @var string $file Testo dell'email
         */
        private $file;

        /**
         * TemplateEmail constructor.
         *
         * @param string $nome    Nome del template
         * @param int    $id_file ID del file del template
         *
         * @throws \Exception
         */
        public function __construct(string $nome, int $id_file)
        {
            $this->nome = $nome;
            $this->id_file = $id_file;

            $file = File::get_by_id($id_file);

            if ($file === null) {
                throw new \Exception('File not found (id: ' . $id_file . ')!');
            }

            $file->richiedi_file();

            $this->file = $file->file;
        }

        /**
         * @param string $name Nome della proprietà
         *
         * @return mixed
         */
        public function __get(string $name)
        {
            return $this->{$name};
        }

        /**
         * Metodo per sostituire le occorrenze con i loro valori reali
         *
         * @param string $field Segnaposto
         * @param mixed  $value Valore
         */
        public function replace(string $field, $value)
        {
            $tmp = preg_replace('/{{' . $field . '}}/', $value, $this->file);

            if ($tmp !== null) {
                $this->file = $tmp;
            }
        }

        /**
         * Metodo per aggiornare il template
         *
         * @param int $id_file ID del file del nuovo template
         *
         * @throws \Exception
         */
        public function update(int $id_file)
        {
            global $mysqli;

            $sql = "UPDATE email_templates SET id_file = ? WHERE nome = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('is', $id_file, $this->nome)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $this->id_file = $id_file;
            $file = File::get_by_id($id_file);

            if ($file === null) {
                throw new \Exception('File not found (id: ' . $id_file . ')!');
            }

            $file->richiedi_file();

            $this->file = $file->file;
        }

        /**
         * Metodo per effettuare la ricerca dei template
         *
         * @param \FabLabRomagna\SQLOperator\SQLOperator[] $dati Campi per cui effettuare la ricerca
         *
         * @return TemplateEmail|null
         *
         * @throws \Exception
         */
        public static function ricerca(
            $dati
        ) {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            if (gettype($dati) !== 'array') {
                throw new \Exception('$dati deve essere un array!');
            }

            $tipi = '';
            $dati_sql = [];
            $where_query = [];

            /**
             * @var \FabLabRomagna\SQLOperator\SQLOperator $campo_ricerca
             */
            foreach ($dati as $campo_ricerca) {
                if (is_subclass_of($campo_ricerca, 'FabLabRomagna\SQLOperator\SQLOperator')) {
                    if (isset(self::PROP_TEMPLATE[$campo_ricerca->colonna])) {
                        $tipi .= $campo_ricerca->get_type();
                        $where_query[] = $campo_ricerca->get_sql();
                        $dati_sql[] = $campo_ricerca->valore;
                    }
                }
            }

            $where_query = implode(' AND ', $where_query);
            $calc = '';

            $query = "SELECT" . $calc . " * FROM email_templates";

            if ($where_query !== '') {
                $query .= ' WHERE ' . $where_query;
            }

            $stmt = $mysqli->prepare($query);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!' . $query);
            }

            if ($tipi !== '') {
                $ref = new \ReflectionClass('mysqli_stmt');
                $obj = $ref->getMethod('bind_param');

                $tmp = array_merge(array($tipi), $dati_sql);

                if (!$obj->invokeArgs($stmt, Ricerca::refValues($tmp))) {
                    throw new \Exception('Impossibile inserire i valori nella query!');
                }
            }

            if (!$stmt->execute()) {
                throw new \Exception('Impossibile eseguire la query!');
            }

            $risultati = $stmt->get_result();

            if ($risultati->num_rows === 0) {
                return null;
            }

            $stmt->close();
            $row = $risultati->fetch_assoc();

            return new TemplateEmail($row['nome'], $row['id_file']);
        }
    }
}
