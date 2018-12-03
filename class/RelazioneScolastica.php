<?php
declare(strict_types=1);

namespace FabLabRomagna {

    use FabLabRomagna\SQLOperator\Equals;

    /**
     * Class RelazioneScolastica
     *
     * @package FabLabRomagna
     *
     * @property-read int         $id_relazione  ID della relazione
     * @property-read Utente      $utente        Utente coinvolto nella relazione
     * @property-read int         $ruolo         Tipologia della relazione: 0 studente, 1 docente, 2 personale
     * @property-read Scuola      $scuola        Istituto coinvolto nella relazioneù
     * @property-read int|null    $classe        dell'utente se studente, numero compreso tra 1 a 5
     * @property-read string|null $sezione       dell'utente se studente, testo libero, max. 5 caratteri
     */
    class RelazioneScolastica implements Ricercabile
    {

        private const PROP_RELAZIONE = [
            'id_relazione' => 'i',
            'utente' => 'i',
            'ruolo' => 'i',
            'scuola' => 's',
            'classe' => 'i',
            'sezione' => 's'
        ];


        /**
         * @var int $id_relazione ID della relazione
         */
        private $id_relazione;


        /**
         * @var Utente $utente Utente coinvolto nella relazione
         */
        private $utente;


        /**
         * @var int $ruolo Tipologia della relazione: 0 studente, 1 docente, 2 personale
         */
        private $ruolo;


        /**
         * @var Scuola $scuola Istituto coinvolto nella relazione
         */
        private $scuola;


        /**
         * @var int|null $classe Classe dell'utente se studente, numero compreso tra 1 a 5
         */
        private $classe;


        /**
         * @var string|null $sezione Sezione dell'utente se studente, testo libero, max. 5 caratteri
         */
        private $sezione;

        /**
         * RelazioneScolastica constructor.
         *
         * @param int         $id_relazione ID della relazione
         * @param Utente      $utente       Utente coinvolto dalla relazione
         * @param Scuola      $scuola       Istituto coinvolto nella relazione
         * @param int         $ruolo        Tipologia della relazione: 0 studente, 1 docente, 2 personale
         * @param int|null    $classe       Classe dell'utente se studente, numero compreso tra 1 a 5
         * @param string|null $sezione      Sezione dell'utente se studente, testo libero, max. 5 caratteri
         *
         * @throws \Exception
         */
        public function __construct(
            int $id_relazione,
            Utente $utente,
            Scuola $scuola,
            int $ruolo,
            ?int $classe = null,
            ?string $sezione = null
        ) {

            if ($classe !== null && ($classe > 5 || $classe < 1)) {
                throw new \Exception('Classe non valida!');
            }

            $this->id_relazione = $id_relazione;
            $this->utente = $utente;
            $this->scuola = $scuola;
            $this->ruolo = $ruolo;
            $this->classe = $classe;
            $this->sezione = $sezione;
        }

        /**
         * @param $name
         *
         * @return mixed
         */
        public function __get($name)
        {
            return $this->{$name};
        }

        /**
         * Metodo per cercare uno o più relazione utente-scuola
         *
         * @param \FabLabRomagna\SQLOperator\SQLOperator[] $dati   Campi di ricerca
         * @param int|null                                 $limit  Lunghezza della ricerca
         * @param int|null                                 $offset Offset di ricerca
         * @param array|null                               $order  Ordinamento: [campo, ascendente] (default: ['id_utente', true] )
         *
         * @global \mysqli                                 $mysqli Connessione al database
         *
         * @throws \Exception
         *
         * @return \FabLabRomagna\RisultatoRicerca
         */
        public static function ricerca(
            $dati,
            $limit = null,
            $offset = null,
            $order = []
        ) {
            global $mysqli;

            $risultati = new Ricerca($mysqli,
                $dati,
                $limit,
                $offset,
                $order,
                self::PROP_RELAZIONE,
                'relazioni_scuola');

            $res = [];

            foreach ($risultati->res as $row) {

                $utente = Utente::ricerca(array(
                    new Equals('id_utente', $row['utente'])
                ))->risultato[0];

                $scuola = Scuola::ricerca(array(
                    new Equals('codice', $row['scuola'])
                ))->risultato[0];

                $res[] = new RelazioneScolastica($row['id_relazione'], $utente, $scuola, $row['ruolo'], $row['classe'],
                    $row['sezione']);
            }

            $res = new RisultatoRicerca($res, $limit, $offset, $risultati->totale, $order);

            return $res;
        }

        /**
         * Metodo per creare una nuova relazione tra utente e scuola
         *
         * @param Utente      $utente  Utente coinvolto dalla relazione
         * @param Scuola      $scuola  Istituto coinvolto nella relazione
         * @param int         $ruolo   Tipologia della relazione: 0 studente, 1 docente, 2 personale
         * @param int|null    $classe  Classe dell'utente se studente, numero compreso tra 1 a 5
         * @param string|null $sezione Sezione dell'utente se studente, testo libero, max. 5 caratteri
         *
         * @global \mysqli    $mysqli  Connessione al database
         *
         * @return RelazioneScolastica
         *
         * @throws \Exception
         */
        public static function crea(
            Utente $utente,
            Scuola $scuola,
            int $ruolo,
            ?int $classe = null,
            ?string $sezione = null
        ): RelazioneScolastica {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected MySQLi as global variable!');
            }

            if (!is_int($ruolo) && ($ruolo < 0 || $ruolo > 2)) {
                throw new \Exception('Ruolo non valida!');
            }

            if ($classe !== null && ($classe > 5 || $classe < 1)) {
                throw new \Exception('Classe non valida!');
            }

            $sql = "INSERT INTO relazioni_scuola (utente, scuola, ruolo, classe, sezione) VALUES (?, ?, ?, ?, ?)";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the statment!');
            }

            if (!$stmt->bind_param('isiis', $utente->id_utente, $scuola->codice, $ruolo, $classe, $sezione)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the statement!');
            }

            return new RelazioneScolastica($stmt->insert_id, $utente, $scuola, $ruolo, $classe, $sezione);
        }

        /**
         * Metodo per eliminare una relazione tra utente e scuola
         *
         * @global \mysqli $mysqli Connessione al database
         *
         * @throws \Exception
         */
        public function elimina()
        {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected MySQLi as global variable!');
            }

            $sql = "DELETE FROM relazioni_scuola WHERE id_relazione = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the statment!');
            }

            if (!$stmt->bind_param('i', $this->id_relazione)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the statement!');
            }
        }
    }
}
