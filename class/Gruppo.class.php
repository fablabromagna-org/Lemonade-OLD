<?php

namespace FabLabRomagna {

    /**
     * Class Gruppo
     *
     * @package FabLabRomagna
     *
     * @property-read $id_gruppo
     * @property-read $nome
     * @property-read $descrizione
     * @property-read $eliminato
     * @property-read $default
     */
    class Gruppo implements Ricercabile
    {
        /**
         * Elenco delle proprietà dell'utente
         */
        protected const PROP_GRUPPO = [
            'id_gruppo' => 'i',
            'nome' => 's',
            'descrizione' => 's',
            'eliminato' => 'i',
            'default' => 'i'
        ];

        /**
         * @var int $id_gruppo ID del gruppo
         */
        protected $id_gruppo;


        /**
         * @var string $nome Nome del gruppo
         */
        protected $nome;


        /**
         * @var string|null $descrizione Descrizione del gruppo
         */
        protected $descrizione;


        /**
         * @var bool $eliminato Indica se un gruppo è stato eliminato
         */
        protected $eliminato;


        /**
         * @var bool $default Indica se un gruppo è quello di default utilizzato dal sistema
         *                    per dare i permessi ai nuovi iscritti
         */
        protected $default;

        /**
         * Gruppo constructor.
         *
         * @param int         $id_gruppo   ID del gruppo
         * @param string      $nome        Nome del gruppo
         * @param string|null $descrizione Descrizione del gruppo
         * @param bool        $eliminato   Indica se un gruppo è stato eliminato
         * @param bool        $default     Indica se un gruppo è quello di default utilizzato dal sistema
         *                                 per dare i permessi ai nuovi iscritti
         */
        public function __construct($id_gruppo, $nome, $descrizione = null, $eliminato = false, $default = false)
        {
            $this->id_gruppo = $id_gruppo;
            $this->nome = $nome;
            $this->descrizione = $descrizione;
            $this->eliminato = $eliminato;
            $this->default = $default;
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
         * Metodo per creare un nuovo gruppo
         *
         * @param string      $nome        Nome del gruppo
         * @param string|null $descrizione Descrizione del gruppo
         * @param bool        $default     Indica se un gruppo è quello di default utilizzato dal sistema
         *                                 per dare i permessi ai nuovi iscritti
         *
         * @global \mysqli    $mysqli      Connessione al database
         *
         * @return Gruppo
         *
         * @throws \Exception
         */
        public static function crea($nome, $descrizione = null, $default = false)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected \mysqli instance in $mysqli!');
            }

            if (gettype($nome) !== 'string') {
                throw new \Exception('Expected string in $nome!');
            }

            if (gettype($descrizione) !== 'string' && $descrizione !== null) {
                throw new \Exception('Expected string in $descrizione!');
            }

            if (gettype($default) !== 'boolean') {
                throw new \Exception('Expected boolean in $default!');
            }

            if (strlen($nome) < 3 || strlen($nome) > 25) {
                throw new \Exception('Invalid name length!');
            }

            if ($descrizione === '') {
                $descrizione = null;
            }

            $sql = "INSERT INTO gruppi (nome, descrizione, `default`) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('ssi', $nome, $descrizione, $default)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $id = $stmt->insert_id;
            $stmt->close();

            return new Gruppo($id, $nome, $descrizione, false, $default);
        }

        /**
         * Metodo per inserire un utente nel gruppo
         *
         * @param Utente   $utente Utente da inserire
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @throws \Exception
         */
        public function inserisci_utente($utente)
        {
            global $mysqli;

            if ($this->id_gruppo === null) {
                throw new \Exception('Group not found!');
            }

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected \mysqli instance in $mysqli!');
            }

            if (!is_a($utente, 'FabLabRomagna\Utente')) {
                throw new \Exception('Expected \FabLabRomagna\Utente instance in $utente!');
            }

            $sql = "INSERT INTO utenti_gruppi (id_utente, id_gruppo) VALUES (?, ?)";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('ii', $utente->id_utente, $this->id_gruppo)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $stmt->close();
        }

        /**
         * Metodo per rimuovere un utente nel gruppo
         *
         * @param Utente   $utente Utente da rimuovere
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @throws \Exception
         */
        public function rimuovi_utente($utente)
        {
            global $mysqli;

            if ($this->id_gruppo === null) {
                throw new \Exception('Group not found!');
            }

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected \mysqli instance in $mysqli!');
            }

            if (!is_a($utente, 'FabLabRomagna\Utente')) {
                throw new \Exception('Expected \FabLabRomagna\Utente instance in $utente!');
            }

            $sql = "DELETE FROM utenti_gruppi WHERE id_utente = ? AND id_gruppo = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('ii', $utente->id_utente, $this->id_gruppo)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $stmt->close();
        }

        /**
         * Metodo per verificare se l'utente fa parte di un gruppo
         *
         * @param Utente   $utente Utente da controllare
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @return bool
         *
         * @throws \Exception
         */
        public function fa_parte($utente)
        {
            global $mysqli;

            if ($this->id_gruppo === null) {
                throw new \Exception('Group not found!');
            }

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected \mysqli instance in $mysqli!');
            }

            if (!is_a($utente, 'FabLabRomagna\Utente')) {
                throw new \Exception('Expected \FabLabRomagna\Utente instance in $utente!');
            }

            $sql = "SELECT * FROM utenti_gruppi WHERE id_utente = ? AND id_gruppo = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('ii', $utente->id_utente, $this->id_gruppo)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $res = $stmt->get_result();

            $stmt->close();

            if ($res->num_rows === 1) {
                return true;
            }

            return false;
        }

        /**
         * Metodo per ricavare tutti gli utenti appartenenti al gruppo
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @return Utente[]
         *
         * @throws \Exception
         */
        public function get_utenti()
        {
            global $mysqli;

            if ($this->id_gruppo === null) {
                throw new \Exception('Group not found!');
            }

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected \mysqli instance in $mysqli!');
            }

            $sql = "SELECT id_utente FROM utenti_gruppi WHERE id_gruppo = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('i', $this->id_gruppo)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $res = $stmt->get_result();
            $stmt->close();

            $tmp = [];

            while ($row = $res->fetch_assoc()) {
                $tmp[] = Utente::ricerca([
                    new SQLOperator\Equals('id_utente', $row['id_utente'])
                ])->risultato[0];
            }

            return $tmp;
        }

        /**
         * Metodo per ricavare tutti i gruppi di un utente
         *
         * @param Utente   $utente Utente per cui cercare i gruppi corrispondenti
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @return array
         * @throws \Exception
         */
        public static function get_gruppi_utente($utente)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected \mysqli instance in $mysqli!');
            }

            if (!is_a($utente, 'FabLabRomagna\Utente')) {
                throw new \Exception('Expected \FabLabRomagna\Utente instance in $utente!');
            }

            $sql = "SELECT gruppi.* FROM utenti_gruppi INNER JOIN gruppi ON gruppi.id_gruppo = utenti_gruppi.id_gruppo WHERE utenti_gruppi.id_utente = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('i', $utente->id_utente)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $res = $stmt->get_result();
            $stmt->close();

            $tmp = [];

            while ($row = $res->fetch_assoc()) {
                $tmp[] = new Gruppo($row['id_gruppo'], $row['nome'], $row['descrizione'], (bool)$row['eliminato'],
                    (bool)$row['default']);
            }

            return $tmp;
        }

        /**
         * Metodo per cercare un gruppo conoscendo l'ID
         *
         * @param int      $id     ID del gruppo da cercare
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @return Gruppo|null
         *
         * @throws \Exception
         *
         * @deprecated
         */
        public static function get_gruppo_by_id($id)
        {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected \mysqli instance in $mysqli!');
            }

            if (gettype($id) !== 'integer' || $id < 1) {
                throw new \Exception('Invalid $id!');
            }

            $sql = "SELECT * FROM gruppi WHERE id_gruppo = ?";
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
            $res = $stmt->get_result();

            $stmt->close();

            if ($res->num_rows === 0) {
                return null;
            }

            $res = $res->fetch_assoc();

            return new Gruppo($res['id_gruppo'], $res['nome'], $res['descrizione'], (bool)$res['eliminato'],
                (bool)$res['default']);
        }

        /**
         * Metodo per copiare gli utenti da un gruppo ad un altro
         *
         * @param Gruppo $gruppo
         * @param        $elimina_orgine
         *
         * @throws \Exception
         */
        public function copia_utenti_da($gruppo, $elimina_orgine = false)
        {
            global $mysqli;

            if ($this->id_gruppo === null) {
                throw new \Exception('Group not found!');
            }

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected \mysqli instance in $mysqli!');
            }

            if (!is_a($gruppo, 'FabLabRomagna\Gruppo')) {
                throw new \Exception('Expected \FabLabRomagna\Gruppo instance in $gruppo!');
            }

            if (gettype($elimina_orgine) !== 'boolean') {
                throw new \Exception('Expected boolean in $elimina_origine!');
            }

            $utenti = $gruppo->get_utenti();

            $sql = "INSERT INTO utenti_gruppi (id_utente, id_gruppo) VALUES (?, ?)";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            foreach ($utenti as $utente) {

                if (!$this->fa_parte($utente)) {

                    if (!$stmt->bind_param('ii', $utente->id_utente, $this->id_gruppo)) {
                        throw new \Exception('Unable to bind params!');
                    }

                    if (!$stmt->execute()) {
                        throw new \Exception('Unable to execute the query!');
                    }
                }
            }
        }

        /**
         * Metodo per eliminare un gruppo
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @throws \Exception
         */
        public function elimina()
        {
            global $mysqli;

            if ($this->id_gruppo === null) {
                throw new \Exception('Group not found!');
            }

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected \mysqli instance in $mysqli!');
            }

            /*
             * ToDo: Completare eliminazione del gruppo
             *          - Aggiungere flag eliminato
             *          - Rimuovere eventuali utenti rimasti
             */
        }

        /**
         * @param bool $eliminati
         *
         * @return Gruppo[]
         *
         * @throws \Exception
         *
         * @deprecated
         */
        public static function get_groups($eliminati = false)
        {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected \mysqli instance in $mysqli!');
            }

            if (gettype($eliminati) !== 'boolean') {
                throw new \Exception('Expected boolean in $eliminati!');
            }

            $sql = "SELECT * FROM gruppi";

            if ($eliminati) {
                $sql .= " WHERE eliminato IS TRUE";
            }

            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $res = $stmt->get_result();

            $stmt->close();

            $tmp = [];

            while ($row = $res->fetch_assoc()) {
                $tmp[] = new Gruppo($row['id_gruppo'], $row['nome'], $row['descrizione'], (bool)$row['eliminato'],
                    (bool)$row['default']);
            }

            return $tmp;
        }

        /**
         * Metodo per cercare uno o più gruppi
         *
         * @param \FabLabRomagna\SQLOperator\SQLOperator[] $dati   Campi di ricerca
         * @param int|null                                 $limit  Lunghezza della ricerca
         * @param int|null                                 $offset Offset di ricerca
         * @param array|null                               $order  Ordinamento: [campo, ascendente] (default: ['id_gruppo', true] )
         *
         * @throws \Exception
         *
         * @return \FabLabRomagna\RisultatoRicerca
         */
        public static function ricerca(
            $dati,
            $limit = null,
            $offset = null,
            $order = [['id_gruppo', true]]
        ) {

            global $mysqli;

            $risultati = new Ricerca($mysqli,
                $dati,
                $limit,
                $offset,
                $order,
                self::PROP_GRUPPO,
                'gruppi');

            $res = [];

            foreach ($risultati->res as $row) {
                $res[] = new Gruppo($row['id_gruppo'], $row['nome'], $row['descrizione'], (bool)$row['eliminato'],
                    (bool)$row['default']);
            }

            $res = new RisultatoRicerca($res, $limit, $offset, $risultati->totale, $order);

            return $res;
        }
    }
}
