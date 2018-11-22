<?php

namespace FabLabRomagna {

    use FabLabRomagna\SQLOperator\SQLOperator;

    /**
     * Interface Ricercabile
     *
     * @package FabLabRomagna
     */
    interface Ricercabile
    {

        /**
         * Metodo statico per effettuare le ricerche
         *
         * @param SQLOperator[] $dati   Dati di ricerca come instanze di SQLOperator
         * @param null          $limit  Limite di ricerca
         * @param null          $offset Offset di ricerca
         * @param array         $order  Ordinamento
         *
         * @return mixed
         */
        public static function ricerca(
            $dati,
            $limit = null,
            $offset = null,
            $order = []
        );
    }
}
