<?php

declare(strict_types=1);

namespace FabLabRomagna {

    /**
     * Class RisultatoRicerca
     *
     * @package FabLabRomagna
     *
     * @property array[]    $risultato
     * @property bool       $case_insensitive
     * @property int|null   $limit
     * @property int|null   $offset
     * @property int|null   $total_rows
     * @property array|null $order
     */
    final class RisultatoRicerca implements \Countable
    {

        /**
         * @var array $risultato Risultato della ricerca
         */
        private $risultato;


        /**
         * @var int|null $offset Offset utilizzato nella ricerca
         */
        private $offset;


        /**
         * @var int|null $limit Lunghezza della ricerca
         */
        private $limit;


        /**
         * @var int|null $total_rows Numero di elementi totali corrispondenti alla ricerca
         */
        public $total_rows;


        /**
         * @var array|null $order Ordinamento: [$campo, $ascendente]
         */
        private $order;


        /**
         * RicercaUtente constructor.
         *
         * @param array      $risultato
         * @param int|null   $limit
         * @param int|null   $offset
         * @param int|null   $total_rows
         * @param array|null $order
         */
        public function __construct(
            array $risultato,
            ?int $limit = null,
            ?int $offset = null,
            ?int $total_rows = null,
            ?array $order = null
        ) {
            $this->risultato = $risultato;
            $this->offset = $offset;
            $this->limit = $limit;
            $this->total_rows = $total_rows;
            $this->order = $order;
        }

        /**
         * Metodo dell'interfaccia Countable
         *
         * @return int
         */
        public function count(): int
        {
            return count($this->risultato);
        }

        /**
         * @param string $name Nome della proprietà
         *
         * @return mixed
         *
         * @throws \Exception
         */
        public function __get(string $name)
        {
            if (property_exists($this, $name)) {
                return $this->{$name};
            }

            throw new \Exception('Property not found!');
        }
    }
}