<?php

namespace FabLabRomagna {


    /**
     * Class Autenticazione
     *
     * @package FabLabRomagna
     *
     * @author  Edoardo Savini <edoardo.savini@fablabromagna.org>
     */
    class Autenticazione
    {
        /**
         * Metodo per controllare la validità SINTATTICA di una password
         *
         * @param $password
         *
         * @return bool
         */
        public static function is_valid_password($password)
        {
            if (gettype($password) !== 'string') {
                return false;
            }

            // Deve contenere almeno un carattere speciale, una lettera maiuscola,
            // Una lettera minuscola e un numero
            // Lunghezza minima 6 caratteri
            return (bool)preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\p{L}\p{N}]*[^\p{L}\p{N}]).{6,}$/u',
                $password);
        }

        /**
         * Metodo per controllare se la password inserita è corretta
         *
         * @param \FabLabRomagna\Utente $utente   Utente di cui bisogna controllare la password
         * @param string                $password Password
         *
         * @global \mysqli              $mysqli   Connessione al database
         *
         * @return bool
         *
         * @throws \Exception
         */
        public static function verify_password_hash($utente, $password)
        {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if (!is_a($utente, 'FabLabRomagna\Utente')) {
                throw new \Exception('Expected \FabLabRomagna\Utente instance for $utente');
            }

            if (gettype($password) !== 'string') {
                throw new \Exception('La password deve essere una stringa');
            }

            $stmt = $mysqli->prepare("SELECT * FROM password WHERE id_utente = ? ORDER BY id_password DESC LIMIT 0, 1");

            if ($stmt === false) {
                throw new \Exception('Impossibile preparare la query!');
            }

            if (!$stmt->bind_param('i', $utente->id_utente)) {
                throw new \Exception('Impossibile effettuare la sostituzione dei parametri di ricerca!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Impossibile eseguire la query!');
            }

            $res = $stmt->get_result();
            $stmt->close();

            if ($res->num_rows === 0) {
                return false;
            }

            $res = $res->fetch_assoc();

            if (password_verify($password, $res['password'])) {
                return true;
            }

            return false;
        }

        /**
         * Metodo per ottenere l'hash della password
         *
         * @param string $password Password di cui calcolare l'hash
         *
         * @return bool|string
         *
         * @throws \Exception
         */
        protected static function generate_password_hash($password)
        {
            if (gettype($password) !== 'string') {
                throw new \Exception('Invalid password type!');
            }

            return password_hash($password, PASSWORD_BCRYPT, ['cost' => 15]);
        }

        /**
         * Metodo per impostare la password all'utente
         *
         * @param   Utente $utente   Utente a cui impostare la password
         * @param   string $password Password da impostare
         *
         * @global \mysqli $mysqli
         *
         * @throws \Exception
         */
        public static function set_user_password($utente, $password)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if (!is_a($utente, 'FabLabRomagna\Utente')) {
                throw new \Exception('Expected \FabLabRomagna\Utente instance for $utente');
            }

            if (gettype($password) !== 'string') {
                throw new \Exception('La password deve essere una stringa');
            }

            $sql = "INSERT INTO password (id_utente, password, ts_inserimento) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            $ts = time();
            $password = self::generate_password_hash($password);
            if (!$stmt->bind_param('isi', $utente->id_utente, $password, $ts)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to bind params!');
            }
        }

        /**
         * Metodo controllare se l'utente ha già utilizzato la password nei mesi precedenti indicati
         *
         * @param Utente   $utente   Utente per cui effettuare il controllo
         * @param string   $password Password per cui effettuare il controllo
         * @param int      $mesi     Numero di mesi in cui la password non può essere trovata (default: 12)
         *
         * @global \mysqli $mysqli   Connessione al database
         *
         * @return bool
         *
         * @throws \Exception
         */
        public static function already_used_password($utente, $password, $mesi = 12)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if (!is_a($utente, 'FabLabRomagna\Utente')) {
                throw new \Exception('Expected \FabLabRomagna\Utente instance for $utente');
            }

            if (gettype($password) !== 'string') {
                throw new \Exception('Expected string in password!');
            }

            if (gettype($mesi) !== 'integer' || $mesi < 0) {
                throw new \Exception('Expected integer in $mesi (>= 0).');
            }

            $sql = "SELECT * FROM password WHERE id_utente = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            if (!$stmt->bind_param('i', $utente->id_utente)) {
                throw new \Exception('Unable to bind id_utente!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the query!');
            }

            $res = $stmt->get_result();
            $stmt->close();

            while ($row = $res->fetch_assoc()) {
                if (password_verify($password, $row['password'])
                    && $row['ts_inserimento'] >= time() - 3600 * 24 * 30 * $mesi) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Metodo per creare una sessione ed eventualmente impostare il relativo cookie
         *
         * @param Utente $utente           Utente per il quale creare la sessione
         * @param string $tipo_dispositivo Tipologia del dispositivo (utilizzare enumeratore)
         * @param string $user_agent       User Agent (o equivalente) del dispositivo
         * @param int    $secondi_durata   Tempo indicante la durata della sessione espresso in secondi (default: 3600, 1h)
         * @param bool   $imposta_cookie   Se true imposta anche il cookie
         *
         * @return Sessione
         *
         * @throws \Exception
         */
        public static function create_session(
            $utente,
            $tipo_dispositivo,
            $user_agent,
            $secondi_durata = 3600,
            $imposta_cookie = false
        ) {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if (!is_a($utente, 'FabLabRomagna\Utente')) {
                throw new \Exception('Expected \FabLabRomagna\Utente instance for $utente');
            }

            if (gettype($tipo_dispositivo) !== 'string') {
                throw new \Exception('Expected string in $tipo_dispositivo');
            }

            if (gettype($user_agent) !== 'string') {
                throw new \Exception('Expected string in $user_agent.');
            }

            if (gettype($secondi_durata) !== 'integer' || $secondi_durata < 1) {
                throw new \Exception('Expected integer in $secondi_durata (>= 1).');
            }

            if (gettype($imposta_cookie) !== 'boolean') {
                throw new \Exception('Expected boolean in $imposta_cookie!');
            }

            $token = uniqid('', true);
            $ts_creazione = $ts_ultima_attivita = time();
            $ts_scadenza = time() + $secondi_durata;

            $sql = "INSERT INTO sessioni (id_utente, token, tipo_dispositivo, user_agent, 
                ts_creazione, ts_scadenza, ts_ultima_attivita) VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the statment!');
            }

            if (!$stmt->bind_param('isssiii', $utente->id_utente, $token, $tipo_dispositivo, $user_agent,
                $ts_creazione, $ts_scadenza, $ts_ultima_attivita)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the statement!');
            }

            $sessione = new Sessione($stmt->insert_id, $utente->id_utente, $token, $tipo_dispositivo, $user_agent,
                $ts_creazione, $ts_scadenza, $ts_ultima_attivita);

            $stmt->close();

            if ($imposta_cookie) {
                setcookie(AUTH_COOKIE_NAME, $token, $ts_scadenza, '/', '', !DEBUG, true);
            }

            return $sessione;
        }

        /**
         * Metodo per ricavare la sessione corrente (se esistente)
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @return Sessione|null
         *
         * @throws \Exception
         */
        public static function get_sessione_attiva()
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if (!isset($_COOKIE[AUTH_COOKIE_NAME])) {
                return null;
            }

            $cookie = $_COOKIE[AUTH_COOKIE_NAME];

            if (!self::is_valid_token($cookie)) {
                return null;
            }

            $sql = "SELECT * FROM sessioni WHERE token = ? AND ts_scadenza > ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the statment!');
            }

            $ts = time();
            if (!$stmt->bind_param('si', $cookie, $ts)) {
                throw new \Exception('Unable to bind param!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the statement!');
            }

            $res = $stmt->get_result();
            $stmt->close();

            if ($res->num_rows !== 1) {
                return null;
            }

            $res = $res->fetch_assoc();

            return new Sessione($res['id_sessione'], $res['id_utente'], $res['token'], $res['tipo_dispositivo'],
                $res['user_agent'], $res['ts_creazione'], $res['ts_scadenza'], $res['ts_ultima_attivita']);
        }

        /**
         * Metodo per effettuare un controllo SINTATTICO sul token
         *
         * @param $token
         *
         * @return bool
         */
        protected static function is_valid_token($token)
        {
            return gettype($token) === 'string' && preg_match('/^[a-f0-9.]{23}$/i', $token);
        }

        /**
         * Metodo per generare una password casuale
         *
         * @return string
         */
        public static function generatePassword()
        {
            $tmp = self::pwd_phase('23456789') . self::pwd_phase('abcdefghjkmnpqrstuvwxyz') .
                self::pwd_phase('ABCDEFGHJKLMNPQRSTUVWXYZ') . self::pwd_phase('.@!#?');

            $randomString = '';
            $characters = str_split($tmp);

            for ($i = 0; $i < strlen($tmp); $i++) {
                $j = rand(0, count($characters) - 1);
                $randomString .= $characters[$j];
                array_splice($characters, $j, 1);
            }

            return $randomString;
        }

        /**
         * Metodo per generare una stringa casuale
         *
         * @param string $characters Caratterri di partenza
         * @param int    $length     Lunghezza
         *
         * @return string
         */
        private static function pwd_phase($characters, $length = 5)
        {
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        /**
         * Metodo per ottenere tutte le sessioni dell'utente
         *
         * @param Utente $utente Utente per cui cercare le sessioni
         *
         * @return Sessione[]
         *
         * @throws \Exception
         */
        public static function get_user_sessions($utente)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if (!is_a($utente, 'FabLabRomagna\Utente')) {
                throw new \Exception('Expected user instance in $utente');
            }

            $sql = "SELECT * FROM sessioni WHERE id_utente = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the statment!');
            }

            if (!$stmt->bind_param('i', $utente->id_utente)) {
                throw new \Exception('Unable to bind param!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the statement!');
            }

            $res = $stmt->get_result();
            $stmt->close();

            if ($res->num_rows !== 1) {
                return [];
            }

            $tmp = [];

            while ($row = $res->fetch_assoc()) {
                $tmp[] = new Sessione($row['id_sessione'], $row['id_utente'], $row['token'], $row['tipo_dispositivo'],
                    $row['user_agent'], $row['ts_creazione'], $row['ts_scadenza'], $row['ts_ultima_attivita']);

            }

            return $tmp;
        }
    }


    /**
     * Class Sessione
     *
     * @package FabLabRomagna
     *
     * @property-read int    $id_sessione
     * @property-read int    $id_utente
     * @property-read string $token
     * @property-read string $tipo_dispositivo
     * @property-read string $user_agent
     * @property-read int    $ts_creazione
     * @property-read int    $ts_scadenza
     * @property-read int    $ts_ultima_attivita
     */
    class Sessione
    {
        /**
         * @var int $id_sessione ID della sessione
         */
        public $id_sessione;


        /**
         * @var int $id_utente ID dell'utente proprietario della sessione
         */
        public $id_utente;


        /**
         * @var string $token Token della sessione
         */
        public $token;


        /**
         * @var string $tipo_dispositivo Tipo del dispositivo.
         *                               Attualmente supportati: web, android
         */
        public $tipo_dispositivo;


        /**
         * @var string $user_agent User-Agent (o qualcosa di simile) del dispositivo
         */
        public $user_agent;


        /**
         * @var string $ts_creazione Unix Time Stamp della creazione della sessione
         */
        public $ts_creazione;


        /**
         * @var string $ts_scadenza Unix Time Stamp della scadenza della sessione
         */
        public $ts_scadenza;


        /**
         * @var string $ Unix Time Stamp dell'ultima attività della sessione
         */
        public $ts_ultima_attivita;

        /**
         * Sessione constructor.
         *
         * @param int    $id_sessione ID della sessione
         * @param int    $id_utente   ID dell'utente proprietario della sessione
         * @param string $token
         * @param string $tipo_dispositivo
         * @param string $user_agent
         * @param int    $ts_creazione
         * @param int    $ts_scadenza
         * @param int    $ts_ultima_attivita
         */
        public function __construct(
            $id_sessione,
            $id_utente,
            $token,
            $tipo_dispositivo,
            $user_agent,
            $ts_creazione,
            $ts_scadenza,
            $ts_ultima_attivita
        ) {
            $this->id_sessione = $id_sessione;
            $this->id_utente = $id_utente;
            $this->token = $token;
            $this->tipo_dispositivo = $tipo_dispositivo;
            $this->user_agent = $user_agent;
            $this->ts_creazione = $ts_creazione;
            $this->ts_scadenza = $ts_scadenza;
            $this->ts_ultima_attivita = $ts_ultima_attivita;
        }

        /**
         * Metodo per aggiornare l'ultima attività della
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @throws \Exception
         */
        public function aggiorna_ultima_attivita()
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if ($this->id_sessione === null) {
                throw new \Exception('Session not found!');
            }

            $sql = "UPDATE sessioni SET ts_ultima_attivita = ? WHERE id_sessione = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            $ts = time();

            if (!$stmt->bind_param('ii', $ts, $this->id_sessione)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Uable to execute the query!');
            }

            $stmt->close();
        }

        /**
         * Metodo per eliminare permanentemente la sessione
         *
         * @param bool     $elimina_cookie Se true elimina anche il cookie (default: true)
         *
         * @global \mysqli $mysqli         Connessione al database
         *
         * @throws \Exception
         *
         * @deprecated Utilizzare Sessione -> termina() al suo posto.
         */
        public function elimina($elimina_cookie = true)
        {
            $this->termina($elimina_cookie);
        }

        /**
         * Metodo per terminare la sessione
         *
         * @param bool     $elimina_cookie Se true elimina anche il cookie (default: true)
         *
         * @global \mysqli $mysqli         Connessione al database
         *
         * @throws \Exception
         */
        public function termina($elimina_cookie = true)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if ($this->id_sessione === null) {
                throw new \Exception('Session not found!');
            }

            $sql = "UPDATE sessioni SET terminata = TRUE WHERE id_sessione = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the statment!');
            }

            if (!$stmt->bind_param('i', $this->id_sessione)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the statment!');
            }

            $this->id_sessione = null;
            $this->token = null;
            $this->tipo_dispositivo = null;
            $this->user_agent = null;
            $this->ts_creazione = null;
            $this->ts_scadenza = null;
            $this->ts_ultima_attivita = null;

            if ($elimina_cookie) {
                setcookie(AUTH_COOKIE_NAME, '', 0, '/', '', !DEBUG, true);
            }
        }

        /**
         * Metodo per aggiornare il token della sessione
         *
         * @param bool     $aggiorna_cookie Se true aggiorna anche il cookie dell'utente
         *                                  Attenzione, se il cookie non è impostato verrà ricreato! (default: false)
         *
         * @global \mysqli $mysqli          Connessione al database
         *
         * @throws \Exception
         */
        public function aggiorna_token($aggiorna_cookie = false)
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if ($this->id_sessione === null) {
                throw new \Exception('Session not found!');
            }

            $sql = "UPDATE sessioni SET token = ? WHERE id_sessione = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!');
            }

            $token = uniqid('', true);

            if (!$stmt->bind_param('si', $token, $this->id_sessione)) {
                throw new \Exception('Unable to bidn params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the statment!');
            }

            $stmt->close();

            $this->token = $token;

            if ($aggiorna_cookie) {
                setcookie(AUTH_COOKIE_NAME, $token, $this->ts_scadenza, '/', '', !DEBUG, true);
            }
        }
    }
}
