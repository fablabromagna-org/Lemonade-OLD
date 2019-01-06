<?php
declare(strict_types=1);

namespace FabLabRomagna {

    /**
     * Class Tag
     *
     * @package FabLabRomagna
     */
    class Tag implements Ricercabile
    {
        private const PROP_TAG = [
            'id_tag' => 'i',
            'nome' => 's'
        ];

        /**
         * @var int $id_tag ID del tag
         */
        private $id_tag;


        /**
         * @var string $nome Nome del tag
         */
        private $nome;

        /**
         * Tag constructor.
         *
         * @param int    $id_tag
         * @param string $nome
         */
        public function __construct(int $id_tag, string $nome)
        {
            $this->id_tag = $id_tag;
            $this->nome = $nome;
        }

        /**
         * Metodo per modificare un campo del tag
         *
         * @param string $campo  Nome del campo
         * @param mixed  $valore Valore da assegnare
         *
         * @throws \Exception
         */
        public function modifica(string $campo, $valore): void
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if ($campo === 'id_tag') {
                throw new \Exception('You can\'t set tag ID!');
            }

            switch ($campo) {
                case 'nome':
                    if (!self::valid_name($valore)) {
                        throw new \Exception('Invalid name!');
                    }

                    break;

                default:
                    throw new \Exception('Property not found!');
            }

            $sql = "UPDATE tag SET $campo = ? WHERE id_tag = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the statment!');
            }

            if (!$stmt->bind_param(self::PROP_TAG[$campo] . 'i', $valore, $this->id_tag)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the statement!');
            }

            $this->{$campo} = $valore;
        }

        /**
         * Metodo per eliminare un tag
         *
         * @throws \Exception
         */
        public function elimina(): void
        {
            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            $sql = "DELETE FROM tag WHERE id_tag = ?";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the statment!');
            }

            if (!$stmt->bind_param('i', $this->id_tag)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the statement!');
            }
        }

        /**
         * Metodo per creare nuovi tag
         *
         * @param string $nome
         *
         * @return Tag
         * @throws \Exception
         */
        public static function crea(string $nome): Tag
        {

            global $mysqli;

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('Expected mysqli instance as global variable');
            }

            if (!self::valid_name($nome)) {
                throw new \Exception('Invalid length!');
            }

            $sql = "INSERT INTO tag (nome) VALUES (?)";
            $stmt = $mysqli->prepare($sql);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the statment!');
            }

            if (!$stmt->bind_param('s', $nome)) {
                throw new \Exception('Unable to bind params!');
            }

            if (!$stmt->execute()) {
                throw new \Exception('Unable to execute the statement!');
            }

            return new Tag($stmt->insert_id, $nome);
        }

        /**
         * Metodo per controllare la validità del nome di un tag
         *
         * @param string $nome Nome del tag da controllare
         *
         * @return bool
         */
        public static function valid_name(string $nome): bool
        {
            return strlen($nome) >= 3 && strlen($nome) <= 30;
        }

        /**
         * Metodo per cercare uno o più tag
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
            $order = [['nome', true]]
        ) {

            global $mysqli;

            $risultati = new Ricerca($mysqli,
                $dati,
                $limit,
                $offset,
                $order,
                self::PROP_TAG,
                'tag');

            $res = [];

            foreach ($risultati->res as $row) {
                $res[] = new Tag($row['id_tag'], $row['nome']);
            }

            $res = new RisultatoRicerca($res, $limit, $offset, $risultati->totale, $order);

            return $res;
        }

        /**
         * @return string
         */
        public function __toString(): string
        {
            return $this->nome;
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
    }
}
