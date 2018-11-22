<?php

declare(strict_types=1);

namespace FabLabRomagna\EntiLocali {

    /**
     * Class EnteAreaVasta
     *
     * @package FabLabRomagna
     */
    abstract class EnteAreaVasta implements \FabLabRomagna\Ricercabile
    {
        /**
         * @var int $id ID dell'ente di area vasta
         */
        protected $id;

        /**
         * @var string $nome Nome dell'ente di area vasta
         */
        protected $nome;

        /**
         * Metodo per cercare uno o più utenti
         *
         * @param \FabLabRomagna\SQLOperator\SQLOperator[] $dati   Campi di ricerca
         * @param int|null                                 $limit  Lunghezza della ricerca
         * @param int|null                                 $offset Offset di ricerca
         * @param array|null                               $order  Ordinamento: [campo, ascendente]
         *
         * @throws \Exception
         *
         * @return \FabLabRomagna\RisultatoRicerca
         */
        abstract public static function ricerca(
            $dati,
            $limit = null,
            $offset = null,
            $order = []
        );

        /**
         * EnteAreaVasta constructor.
         *
         * @param int    $id
         * @param string $nome
         */
        public function __construct(int $id, string $nome)
        {
            $this->id = $id;
            $this->nome = $nome;
        }

        /**
         * @param $name
         *
         * @return mixed
         */
        public function __get(string $name)
        {
            return $this->{$name};
        }

        /**
         * @return string
         */
        public function __toString(): string
        {
            return $this->nome;
        }
    }
}
