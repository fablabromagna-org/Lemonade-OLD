<?php

namespace FabLabRomagna {


    use FabLabRomagna\SQLOperator\SQLOperator;

    /**
     * Class Ricerca
     *
     * @package FabLabRomagna
     *
     * @property-read $res
     * @property-read $totale
     */
    final class Ricerca
    {
        /**
         * @var array $res Risultato della ricerca
         */
        private $res;


        /**
         * @var int Numero di righe totali
         */
        private $totale;

        /**
         * Ricerca constructor.
         *
         * @param \mysqli       $mysqli    Connessione al database
         * @param SQLOperator[] $dati      Ricerche da effettuare
         * @param int|null      $offset    Offset della ricerca
         * @param int|null      $limit     Limite di risultati
         * @param array|null    $order     Ordinamento
         * @param array         $proprieta Proprietà valide
         * @param string        $tabella   Nome della tabella
         *
         * @throws \Exception
         */
        public function __construct($mysqli, $dati, $limit, $offset, $order, $proprieta, $tabella)
        {

            if (!is_a($mysqli, 'mysqli')) {
                throw new \Exception('MySQLi as global variable expected!');
            }

            if (gettype($dati) !== 'array') {
                throw new \Exception('$dati deve essere un array!');
            }

            if ($offset !== null && gettype($offset) !== 'integer') {
                throw new \Exception('Invalid offset!');
            }

            if ($limit !== null && gettype($limit) !== 'integer') {
                throw new \Exception('Invalid limit');
            }

            if ($limit === null && $offset !== null) {
                throw new \Exception('Offset requires limit!');
            }

            if ((gettype($order) !== 'array') && $order !== null) {
                throw new \Exception('Invalid order!');
            }

            $tipi = '';
            $dati_sql = [];
            $where_query = [];

            $last = true;

            foreach ($dati as $campo_ricerca) {
                if (is_subclass_of($campo_ricerca, 'FabLabRomagna\SQLOperator\SQLOperator')) {

                    if (!is_a($campo_ricerca, 'FabLabRomagna\SQLOperator\SQLAnd') && !is_a($campo_ricerca,
                            'FabLabRomagna\SQLOperator\SQLOr')) {

                        if (!$last) {
                            $where_query[] = 'AND';
                        }

                        if (isset($proprieta[$campo_ricerca->colonna])) {
                            $tipi .= $campo_ricerca->get_type();
                            $where_query[] = $campo_ricerca->get_sql();
                            $dati_sql[] = $campo_ricerca->valore;

                            $last = false;
                        }

                    } else {
                        $where_query[] = $campo_ricerca->get_sql();
                        $last = true;
                    }
                }
            }

            $where_query = implode(' ', $where_query);
            $calc = '';

            if ($limit !== null) {
                $calc = ' SQL_CALC_FOUND_ROWS';
            }

            $query = "SELECT" . $calc . " * FROM " . $tabella;

            if ($where_query !== '') {
                $query .= ' WHERE ' . $where_query;
            }

            if ($order !== null) {

                $is_first = true;
                foreach ($order as $value) {
                    if (isset($proprieta[$value[0]])) {
                        $t = $value[1] ? 'ASC' : 'DESC';

                        if ($is_first) {
                            $is_first = false;
                            $query .= ' ORDER BY';
                        } else {
                            $query .= ',';
                        }

                        $query .= ' ' . $value[0] . ' ' . $t;
                    }
                }
            }

            if ($limit !== null) {
                $query .= ' LIMIT ' . $limit;
            }

            if ($offset !== null) {
                $query .= ' OFFSET ' . $offset;
            }

            $stmt = $mysqli->prepare($query);

            if ($stmt === false) {
                throw new \Exception('Unable to prepare the query!' . $query);
            }

            if ($tipi !== '') {
                $ref = new \ReflectionClass('mysqli_stmt');
                $obj = $ref->getMethod('bind_param');

                $tmp = array_merge(array($tipi), $dati_sql);

                if (!$obj->invokeArgs($stmt, self::refValues($tmp))) {
                    throw new \Exception('Impossibile inserire i valori nella query!');
                }
            }

            if (!$stmt->execute()) {
                throw new \Exception('Impossibile eseguire la query!');
            }

            $risultati = $stmt->get_result();

            $res = [];

            while ($row = $risultati->fetch_assoc()) {
                $res[] = $row;
            }

            $stmt->close();

            $this->res = $res;

            if ($limit !== null) {
                $sql = "SELECT FOUND_ROWS() AS 'totale'";
                $stmt = $mysqli->query($sql);

                $row = $stmt->fetch_assoc();

                $this->totale = (int)$row['totale'];
            } else {
                $this->totale = $risultati->num_rows;
            }
        }

        /**
         * Il metodo restituisce un vettore per riferimento
         *
         * @param $arr
         *
         * @return array
         */
        public static function refValues($arr)
        {
            if (strnatcmp(phpversion(), '5.3') >= 0) {
                $refs = array();
                foreach ($arr as $key => $value) {
                    $refs[$key] = &$arr[$key];
                }
                return $refs;
            }
            return $arr;
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
