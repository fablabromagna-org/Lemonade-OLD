<?php

namespace FabLabRomagna {

    /**
     * Class Permesso
     *
     * @package FabLabRomagna
     *
     * @property-read $id_permesso
     * @property-read $id_utente_gruppo
     * @property-read $utente
     * @property-read $permesso
     * @property-read $valore
     */
    class Permesso
    {
        /**
         * @var int $id_permesso ID della registrazione del permesso
         */
        protected $id_permesso;


        /**
         * @var bool $utente Se true il permesso si riferisce ad un utente.
         *                   Se false si riferisce ad un gruppo.
         */
        protected $utente;


        /**
         * @var int $id_utente_gruppo ID dell'utente o del gruppo
         */
        protected $id_utente_gruppo;


        /**
         * @var string $permesso Permesso
         */
        protected $permesso;


        /**
         * @var bool $valore Se true il permesso è stato concesso
         */
        protected $valore;

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
         * Permesso constructor.
         *
         * @param int $id_permesso ID della registrazione del permesso
         * @param     $utente
         * @param     $id_utente_gruppo
         * @param     $permesso
         * @param     $valore
         */
        public function __construct($id_permesso, $utente, $id_utente_gruppo, $permesso, $valore)
        {
            $this->id_permesso = $id_permesso;
            $this->utente = $utente;
            $this->id_utente_gruppo = $id_utente_gruppo;
            $this->permesso = $permesso;
            $this->valore = $valore;
        }

        /**
         * Metodo che restituisce tutti i permessi CALCOLATI per l'utente
         *
         * @param Utente   $utente Utente per cui calcolare i permessi
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @return array
         *
         * @throws \Exception
         */
        public static function what_can_i_do($utente)
        {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            if (!is_a($utente, 'FabLabRomagna\Utente')) {
                throw new \Exception('Invalid user!');
            }

            $sql = "SELECT * FROM permessi WHERE utente = ? AND id_utente_gruppo = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            $true = true;
            $false = false;

            $permessi_utente = ELENCO_PERMESSI;

            // Permessi di gruppo
            $gruppi = Gruppo::get_gruppi_utente($utente);

            foreach ($gruppi as $gruppo) {

                if (!$stmt->bind_param('ii', $false, $gruppo->id_gruppo)) {
                    throw new \Exception('Unable to bind params!');
                }

                if (!$stmt->execute()) {
                    throw new \Exception('Unable to execute the query!');
                }

                $res = $stmt->get_result();

                while ($row = $res->fetch_assoc()) {
                    if (isset($permessi_utente[$row['permesso']])) {
                        $permessi_utente[$row['permesso']]['reale'] = $permessi_utente[$row['permesso']]['reale'] === true ? true : (bool)$row['valore'];
                    }
                }
            }

            // Permessi utente
            if (!$stmt->bind_param('ii', $true, $utente->id_utente)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $res = $stmt->get_result();

            while ($row = $res->fetch_assoc()) {
                if (isset($permessi_utente[$row['permesso']])) {
                    $permessi_utente[$row['permesso']]['reale'] = (bool)$row['valore'];
                }
            }

            $stmt->close();

            // Eventuali permessi rimasti senza valore "reale"
            foreach ($permessi_utente as $key => $value) {
                if (!isset($value['reale'])) {
                    $permessi_utente[$key]['reale'] = $value['default'];
                }
            }

            return $permessi_utente;
        }

        /**
         * Metodo per aggiungere un permesso ad un gruppo o ad un utente
         * Se il permesso esiste già lo reimposta senza cambiare l'ID
         *
         * @param Utente|Gruppo $utente_gruppo Utente o gruppo a cui aggiungere il permesso
         * @param string        $permesso      Permesso da inserire
         * @param bool          $valore        Valore da inserire
         *
         * @global \mysqli      $mysqli        Connessione al database
         *
         * @return Permesso
         *
         * @throws \Exception
         */
        public static function aggiungi_permesso($utente_gruppo, $permesso, $valore)
        {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            if (!is_a($utente_gruppo, 'FabLabRomagna\Utente') && !is_a($utente_gruppo, 'FabLabRomagna\Gruppo')) {
                throw new \Exception('Invalid group or user!');
            }

            if (gettype($permesso) !== 'string') {
                throw new \Exception('Invalid $permesso!');
            }

            if (gettype($valore) !== 'boolean') {
                throw new \Exception('Invalid $valore!');
            }

            if (is_a($utente_gruppo, 'FabLabRomagna\Utente')) {
                $id = $utente_gruppo->id_utente;
                $utente = true;
            } else {
                $id = $utente_gruppo->id_gruppo;
                $utente = false;
            }

            $verifica = self::get_permission($utente_gruppo, $permesso);
            if ($verifica !== null) {
                $verifica->update($valore);
                return $verifica;
            }

            $sql = "INSERT INTO permessi (utente, id_utente_gruppo, permesso, valore) VALUES (?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('iisi', $utente, $id, $permesso, $valore)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $id_stmt = $stmt->insert_id;
            $stmt->close();

            return new Permesso($id_stmt, $utente, $id, $permesso, $valore);
        }

        /**
         * Metodo per cercare un permesso assegnato ad un utente o un gruppo
         *
         * @param Utente|Gruppo $utente_gruppo Utente o gruppo per il quale effettuare la ricerca
         * @param string        $permesso      Permesso da ricercare
         *
         * @global \mysqli      $mysqli        Connessione al database
         *
         * @return Permesso|null
         *
         * @throws \Exception
         */
        public static function get_permission(
            $utente_gruppo,
            $permesso
        ) {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            if (!is_a($utente_gruppo, 'FabLabRomagna\Utente') && !is_a($utente_gruppo, 'FabLabRomagna\Gruppo')) {
                throw new \Exception('Invalid group or user!');
            }

            if (gettype($permesso) !== 'string') {
                throw new \Exception('Invalid $permesso!');
            }

            if (is_a($utente_gruppo, 'FabLabRomagna\Utente')) {
                $id = $utente_gruppo->id_utente;
                $utente = true;
            } else {
                $id = $utente_gruppo->id_gruppo;
                $utente = false;
            }

            $sql = "SELECT * FROM permessi WHERE utente = ? AND id_utente_gruppo = ? AND permesso = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('iis', $utente, $id, $permesso)) {
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

            return new Permesso($res['id_permesso'], (bool)$res['utente'], $res['id_utente_gruppo'], $res['permesso'],
                (bool)$res['valore']);
        }

        /**
         * Metodo per rimuovere un permesso
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @throws \Exception
         */
        public function rimuovi()
        {
            global $mysqli;

            if ($this->id_permesso === null) {
                throw new \Exception('Permission not found!');
            }

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            $sql = "DELETE FROM permessi WHERE id_permesso = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('i', $this->id_permesso)) {
                throw new \Exception('Unable to bind param!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $stmt->close();

            $this->id_permesso = null;
            $this->id_utente_gruppo = null;
            $this->utente = null;
            $this->permesso = null;
            $this->valore = null;
        }

        /**
         * Metodo per aggiornare il valore di un permesso
         *
         * @param bool     $valore Nuovo valore da impostare
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @throws \Exception
         */
        public function update($valore)
        {
            global $mysqli;

            if ($this->id_permesso === null) {
                throw new \Exception('Permission not found!');
            }

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            if (gettype($valore) !== 'boolean') {
                throw new \Exception('Invalid $valore!');
            }

            $sql = "UPDATE permessi SET valore = ? WHERE id_permesso = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('ii', $valore, $this->id_permesso)) {
                throw new \Exception('Unable to bind param!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }
        }
    }
}