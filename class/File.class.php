<?php

namespace FabLabRomagna {

    /**
     * Class File
     *
     * @package FabLabRomagna
     *
     * @author  Edoardo Savini <edoardo.savini@fablabromagna.org>
     *
     * @property-read string|null $nome
     * @property-read string      $mime
     * @property-read int         $id_file
     * @property-read resource    $file
     * @property-read int         $ts_inserimento
     * @property-read string      $sha256
     * @property-read int         $md5
     */
    class File
    {
        /**
         * @var int $id_file ID del file
         */
        protected $id_file;


        /**
         * @var string|null $nome Nome del file
         */
        protected $nome;


        /**
         * @var string $mime Mime del file
         */
        protected $mime;


        /**
         * @var resource|null $file File caricato sul server (attenzione, di default non viene scaricato immediatamente
         *                          il file ma solo un riferimento ad esso)
         *                          Per evitare sovraccarichi inutili al database e alla memoria RAM
         */
        protected $file;


        /**
         * @var int $ts_inserimento Unix Time Stamp dell'inserimento nel database
         */
        protected $ts_inserimento;


        /**
         * @var string $sha256 SHA256 del file inserito
         */
        protected $sha256;


        /**
         * @var string $md5 MD5 del file inserito
         */
        protected $md5;

        /**
         * File constructor.
         *
         * @param int         $id_file        ID del file
         * @param int         $ts_inserimento Unix Time Stamp dell'inserimento nel DB
         * @param string|null $mime           Mime Type del file
         * @param string|null $nome           Nome del file
         * @param resource    $file           File
         *
         * @throws \Exception
         */
        public function __construct(
            $id_file,
            $ts_inserimento,
            $mime = null,
            $nome = null,
            $md5 = null,
            $sha256 = null
        ) {
            if (gettype($id_file) !== 'integer' || $id_file < 1) {
                throw new \Exception('Invalid file ID!');
            }

            if (gettype($ts_inserimento) !== 'integer') {
                throw new \Exception('Invalid file timestamp!');
            }

            if ((gettype($mime) !== 'string' || strlen($mime) < 1) && $mime !== null) {
                throw new \Exception('Invalid mime type!');
            }

            if ((gettype($nome) !== 'string' || strlen($nome) < 1) && $nome !== null) {
                throw new \Exception('Invalid file name!');
            }

            if ((gettype($md5) !== 'string' || strlen($md5) !== 32) && $md5 !== null) {
                throw new \Exception('Invalid MD5 hash!');
            }

            if ((gettype($sha256) !== 'string' || strlen($sha256) !== 64) && $sha256 !== null) {
                throw new \Exception('Invalid SHA256 hash!');
            }

            $this->id_file = $id_file;
            $this->ts_inserimento = $ts_inserimento;
            $this->mime = $mime;
            $this->nome = $nome;
            $this->file = $file;
            $this->md5 = $md5;
            $this->sha256 = $sha256;
        }

        /**
         * @param string $nome Nome della proprietà
         *
         * @return mixed
         *
         * @throws \Exception
         */
        public function __get($nome)
        {
            if (property_exists($this, $nome)) {
                return $this->{$nome};
            }

            throw new \Exception('Property not found!');
        }

        /**
         * @param $nome
         * @param $valore
         *
         * @throws \Exception
         */
        public function __set($nome, $valore)
        {
            throw new \Exception('You can\'t set values directly!');
        }

        /**
         * Metodo per rimuovere dal database un file
         * Una volta rimosso non è più recuperabile!
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @throws \Exception
         */
        public function elimina()
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('');
            }

            if ($this->id_file === null) {
                throw new \Exception('File not found!');
            }

            $sql = "DELETE FROM files WHERE id_file = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('i', $this->id_file)) {
                throw new \Exception('Unable to bind params to the query!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $this->id_file = null;
            $this->nome = null;
            $this->mime = null;
            $this->ts_inserimento = null;
        }

        /**
         * Metodo per richiedere anche il file
         * (vedi descrizione proprietà $file)
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @throws \Exception
         */
        public function richiedi_file()
        {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if ($this->id_file === null) {
                throw new \Exception('File not found!');
            }

            $sql = "SELECT file FROM files WHERE id_file = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('i', $this->id_file)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $stmt = $stmt->get_result();

            if ($stmt->num_rows === 0) {
                throw new \Exception('Unable to find the file!');
            }

            $row = $stmt->fetch_assoc();

            $this->file = $row['file'];
        }

        /**
         * Metodo per salvare un nuovo file
         *
         * @param string      $file_path File da caricare
         * @param string|null $mime
         * @param string|null $nome
         *
         * @global \mysqli    $mysqli    Connessione al database
         *
         * @return \FabLabRomagna\File
         *
         * @throws \Exception
         */
        public static function salva($file_path, $mime = null, $nome = null)
        {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if (gettype($file_path) !== 'string') {
                throw new \Exception('Invalid file path!');
            }

            if ((gettype($mime) !== 'string' || strlen($mime) < 1) && $mime !== null) {
                throw new \Exception('Invalid mime type!');
            }

            if ((gettype($nome) !== 'string' || strlen($nome) < 1) && $nome !== null) {
                throw new \Exception('Invalid file name!');
            }

            if (!file_exists($file_path)) {
                throw new \Exception('File not found!');
            }

            if (is_dir($file_path)) {
                throw new \Exception('The file is a directory!');
            }

            $sql = "INSERT INTO files (file, mime, nome, ts_inserimento) VALUES (?, ?, ?, ?)";

            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Impossibile preparare la query!');
            }

            $null = null;
            $ts = time();

            if (!$stmt->bind_param('bssi', $null, $mime, $nome, $ts)) {
                throw new \Exception('Unable to bind params!');
            }

            $fop = @fopen($file_path, 'r');

            if ($fop === false) {
                throw new \Exception('Impossibile aprire il file!');
            }

            while (!feof($fop)) {

                $data = fread($fop, 8192);

                if ($data === false) {
                    throw new \Exception('Impossibile leggere il file!');
                }

                if (!$stmt->send_long_data(0, $data)) {
                    throw new \Exception('Unable to send file data!');
                }
            }

            if (!fclose($fop)) {
                throw new \Exception('Impossibile chiudere il file!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Impossibile eseguire la query!' . $mysqli->error);
            }

            if (!unlink($file_path)) {
                throw new \Exception('Unable to delete tmp file!');
            }

            return new File($stmt->insert_id, $ts, $mime, $nome);
        }

        /**
         * Metodo per salvare un nuovo file dalla memoria
         *
         * @param string      $data   File da caricare
         * @param string|null $mime
         * @param string|null $nome
         *
         * @global \mysqli    $mysqli Connessione al database
         *
         * @return \FabLabRomagna\File
         *
         * @throws \Exception
         */
        public static function salva_mem($data, $mime = null, $nome = null)
        {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if (gettype($data) !== 'string') {
                throw new \Exception('Invalid file path!');
            }

            if ((gettype($mime) !== 'string' || strlen($mime) < 1) && $mime !== null) {
                throw new \Exception('Invalid mime type!');
            }

            if ((gettype($nome) !== 'string' || strlen($nome) < 1) && $nome !== null) {
                throw new \Exception('Invalid file name!');
            }

            $sql = "INSERT INTO files (file, mime, nome, ts_inserimento) VALUES (?, ?, ?, ?)";

            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Impossibile preparare la query!');
            }

            $ts = time();

            if (!$stmt->bind_param('sssi', $data, $mime, $nome, $ts)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Impossibile eseguire la query!' . $mysqli->error);
            }

            return self::get_by_id($stmt->insert_id);
        }

        /**
         * Metodo per estrarre un file conoscendo l'ID
         *
         * @param int $id ID del file da estrarre
         *
         * @return File|null
         *
         * @throws \Exception
         */
        public static function get_by_id($id)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if (gettype($id) !== 'integer') {
                throw new \Exception('Invalid file timestamp!');
            }

            $sql = "SELECT nome, mime, ts_inserimento, id_file, `sha256`, `md5`  FROM files WHERE id_file = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('i', $id)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $stmt = $stmt->get_result();

            if ($stmt->num_rows !== 1) {
                return null;
            }

            $row = $stmt->fetch_assoc();

            return new File($id, $row['ts_inserimento'], $row['mime'], $row['nome'], $row['md5'], $row['sha256']);
        }
    }
}
