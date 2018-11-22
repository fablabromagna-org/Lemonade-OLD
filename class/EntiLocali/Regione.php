<?php

declare(strict_types=1);

namespace FabLabRomagna\EntiLocali {

    use \FabLabRomagna\Ricerca;
    use \FabLabRomagna\RisultatoRicerca;

    /**
     * Class Regione
     *
     * @package FabLabRomagna\EnteAreaVasta
     *
     * @property-read $id
     * @property-read $nome
     */
    class Regione extends EnteAreaVasta
    {
        protected const PROP_ENTE = [
            'id_regione' => 'i',
            'nome' => 's'
        ];

        /**
         * Metodo per cercare uno o più regioni
         *
         * @param \FabLabRomagna\SQLOperator\SQLOperator[] $dati   Campi di ricerca
         * @param int|null                                 $limit  Lunghezza della ricerca
         * @param int|null                                 $offset Offset di ricerca
         * @param array|null                               $order  Ordinamento: [campo, ascendente] (default: ['id', true])
         *
         * @throws \Exception
         *
         * @return \FabLabRomagna\RisultatoRicerca
         */
        public static function ricerca(
            $dati,
            $limit = null,
            $offset = null,
            $order = [['id_regione', true]]
        ) {
            global $mysqli;

            $risultati = new Ricerca($mysqli,
                $dati,
                $limit,
                $offset,
                $order,
                self::PROP_ENTE,
                'regioni');

            $res = [];

            foreach ($risultati->res as $row) {
                $res[] = new Regione($row['id_regione'], $row['nome']);
            }

            $res = new RisultatoRicerca($res, $limit, $offset, $risultati->totale, $order);

            return $res;
        }

        /**
         * Regione constructor.
         *
         * @param int    $id   ID della regione
         * @param string $nome Nome della regione
         */
        public function __construct(int $id, string $nome)
        {
            parent::__construct($id, $nome);
        }
    }
}
