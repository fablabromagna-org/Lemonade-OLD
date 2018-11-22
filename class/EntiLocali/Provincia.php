<?php

declare(strict_types=1);

namespace FabLabRomagna\EntiLocali {

    use \FabLabRomagna\Ricerca;
    use \FabLabRomagna\RisultatoRicerca;

    /**
     * Class Provincia
     *
     * @package FabLabRomagna\EntiLocali
     *
     * @property-read $id
     * @property-read $nome
     */
    class Provincia extends EnteAreaVasta
    {
        protected const PROP_ENTE = [
            'id_provincia' => 'i',
            'nome' => 's'
        ];

        /**
         * Metodo per cercare uno o più province
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
            $order = [['id_provincia', true]]
        ) {
            global $mysqli;

            $risultati = new Ricerca($mysqli,
                $dati,
                $limit,
                $offset,
                $order,
                self::PROP_ENTE,
                'province');

            $res = [];

            foreach ($risultati->res as $row) {
                $res[] = new Provincia($row['id_provincia'], $row['nome']);
            }

            $res = new RisultatoRicerca($res, $limit, $offset, $risultati->totale, $order);

            return $res;
        }

        /**
         * Provincia constructor.
         *
         * @param int    $id   ID della provincia
         * @param string $nome Nome della provincia
         */
        public function __construct(int $id, string $nome)
        {
            parent::__construct($id, $nome);
        }
    }
}
