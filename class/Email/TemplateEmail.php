<?php
declare(strict_types=1);

namespace FabLabRomagna\Email {

    use FabLabRomagna\File;

    /**
     * Class TemplateEmail
     *
     * @package FabLabRomagna\Email
     *
     * @property-read string $nome
     * @property-read int    $id_file
     * @property-read string $file
     */
    final class TemplateEmail extends Email
    {

        /**
         * Elenco delle proprietà dell'utente
         */
        protected const PROP_TEMPLATE = [
            'nome' => 's',
            'id_file' => 'i',
            'oggetto' => 's'
        ];

        /**
         * @var string $nome Nome del template
         */
        protected $nome;

        /**
         * @var int $id_file ID del file del template
         */
        private $id_file;

        /**
         * TemplateEmail constructor.
         *
         * @param string $nome    Nome del template
         * @param string $oggetto Oggetto
         * @param int    $id_file ID del file del template
         *
         * @throws \Exception
         */
        public function __construct(string $nome, string $oggetto, int $id_file)
        {
            $this->nome = $nome;
            $this->id_file = $id_file;

            $file = File::get_by_id($id_file);

            if ($file === null) {
                throw new \Exception('File not found (id: ' . $id_file . ')!');
            }

            $file->richiedi_file();

            $this->scarica_allegati();

            parent::__construct($oggetto, (string)$file->file);
        }

        /**
         * Metodo per sostituire le occorrenze con i loro valori reali
         *
         * @param string $field Segnaposto
         * @param mixed  $value Valore
         */
        public function replace(string $field, $value)
        {
            $tmp = preg_replace('/{{' . $field . '}}/', $value, $this->messaggio);

            if ($tmp !== null) {
                $this->messaggio = $tmp;
            }
        }

        /**
         * Metodo per aggiornare il template
         *
         * @param File $file File del nuovo template
         *
         * @throws \Exception
         */
        public function update_template(File $file)
        {
            global $mysqli;

            $sql = "UPDATE email_templates SET id_file = ? WHERE nome = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('is', $file->id_file, $this->nome)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $this->id_file = $file->id_file;
            $file = File::get_by_id($file->id_file);

            if ($file === null) {
                throw new \Exception('File not found (id: ' . $file . ')!');
            }

            $file->richiedi_file();

            $this->messaggio = $file->file;
        }

        /**
         * Metodo per aggiornare l'oggetto del template
         *
         * @param string $oggetto Oggetto della mail
         *
         * @throws \Exception
         */
        public function update_subject(string $oggetto)
        {
            global $mysqli;

            $sql = "UPDATE email_templates SET oggetto = ? WHERE nome = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('ss', $oggetto, $this->nome)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $this->oggetto = $oggetto;
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

                if (!$obj->invokeArgs($stmt, \FabLabRomagna\Ricerca::refValues($tmp))) {
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

            return new TemplateEmail($row['nome'], (string)$row['oggetto'], $row['id_file']);
        }

        /**
         * Metodo per aggiungere un allegato standard al template
         *
         * @param File $file        File da associare
         * @param bool $incorporato Indica se la risorsa è incorporata alla mail o allegata
         *
         * @throws \Exception
         */
        public function aggiungi_allegato(File $file, bool $incorporato = false)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            $sql = "INSERT INTO allegati_template_email VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('sii', $this->nome, $file->id_file, $incorporato)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            if ($incorporato) {
                $this->immagini_incorporate[] = $file;
            } else {
                $this->allegati[] = $file;
            }
        }

        /**
         * Metodo per eliminare un allegato standard al template
         *
         * @param File $file File da associare
         *
         * @throws \Exception
         */
        public function elimina_allegato(File $file)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            $sql = "DELETE FROM allegati_template_email WHERE nome = ? AND id_file = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('si', $this->nome, $file->id_file)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $this->scarica_allegati();
        }

        /**
         * Metodo per caricare tutti gli allegati disponibili
         *
         * @throws \Exception
         */
        private function scarica_allegati()
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            $sql = "SELECT * FROM allegati_template_email WHERE nome = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('s', $this->nome)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $res = $stmt->get_result();

            $this->immagini_incorporate = [];
            $this->allegati = [];

            while ($row = $res->fetch_assoc()) {
                if ((bool)$row['embedded']) {
                    $this->immagini_incorporate[] = File::get_by_id($row['id_file']);
                } else {
                    $this->allegati[] = File::get_by_id($row['id_file']);
                }
            }
        }
    }
}
