<?php
declare(strict_types=1);

namespace FabLabRomagna {

    use FabLabRomagna\EntiLocali\Comune;
    use FabLabRomagna\EntiLocali\Provincia;
    use FabLabRomagna\EntiLocali\Regione;
    use FabLabRomagna\SQLOperator\Equals;
    use PhpParser\Node\Expr\BinaryOp\Equal;

    /**
     * Class Scuola
     *
     * @package FabLabRomagna
     *
     * @property-read string    $codice        Codice meccanografico dell'istituto scolastico
     * @property-read string    $denominazione Denominazione dell'istituto scolastico
     * @property-read string    $indirizzo     Indirizzo dell'istituto scolastico
     * @property-read Comune    $comune        Comune della scuola
     * @property-read Provincia $provincia     Provincia della scuola
     * @property-read Regione   $regione       Regione della scuola
     */
    class Scuola implements Ricercabile
    {
        private const PROP_SCUOLA = [
            'codice' => 's',
            'denominazione' => 's',
            'indirizzo' => 's',
            'comune' => 's',
            'provincia' => 'i',
            'regione' => 'i'
        ];

        /**
         * @var string $codice Codice meccanografico dell'istituto scolastico
         */
        private $codice;


        /**
         * @var string $denominazione Denominazione dell'istituto scolastico
         */
        private $denominazione;


        /**
         * @var string $indirizzo Indirizzo dell'istituto scolastico
         */
        private $indirizzo;


        /**
         * @var Comune $comune Comune della scuola
         */
        private $comune;


        /**
         * @var Provincia $provincia Provincia della scuola
         */
        private $provincia;


        /**
         * @var Regione $regione Regione della scuola
         */
        private $regione;

        /**
         * Scuola constructor.
         *
         * @param string    $codice        Codice meccanografico della scuola
         * @param string    $denominazione Denominazione dell'istituto
         * @param string    $indirizzo     Indirizzo dell'Istituto
         * @param Comune    $comune        Comune della sede principale della scuola
         * @param Provincia $provincia     Provincia della scuola
         * @param Regione   $regione       Regione della scuola
         */
        public function __construct(
            string $codice,
            string $denominazione,
            string $indirizzo,
            Comune $comune,
            Provincia $provincia,
            Regione $regione
        ) {

            $this->codice = $codice;
            $this->denominazione = $denominazione;
            $this->indirizzo = $indirizzo;
            $this->comune = $comune;
            $this->provincia = $provincia;
            $this->regione = $regione;
        }

        /**
         * @param SQLOperator\SQLOperator[] $dati
         * @param null                      $limit
         * @param null                      $offset
         * @param array                     $order
         *
         * @return array|RisultatoRicerca|mixed
         *
         * @throws \Exception
         */
        public static function ricerca(
            $dati,
            $limit = null,
            $offset = null,
            $order = [['codice', true]]
        ) {

            global $mysqli;

            $risultati = new Ricerca($mysqli,
                $dati,
                $limit,
                $offset,
                $order,
                self::PROP_SCUOLA,
                'scuole');

            $res = [];

            foreach ($risultati->res as $row) {

                $comune = (Comune::ricerca(array(
                    new Equals('belfiore', $row['comune'])
                )))->risultato[0];

                $provincia = (Provincia::ricerca(array(
                    new Equals('id_provincia', $row['provincia'])
                )))->risultato[0];

                $regione = (Regione::ricerca(array(
                    new Equals('id_regione', $row['regione'])
                )))->risultato[0];

                $res[] = new Scuola($row['codice'], $row['denominazione'], $row['indirizzo'], $comune, $provincia,
                    $regione);
            }

            $res = new RisultatoRicerca($res, $limit, $offset, $risultati->totale, $order);

            return $res;
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
         * @return string
         */
        public function __toString()
        {
            return $this->denominazione . ' (' . $this->codice . ')';
        }
    }
}
