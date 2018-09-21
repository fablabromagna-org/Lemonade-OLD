<?php

declare(strict_types=1);

namespace FabLabRomagna\SQLOperator {

    /**
     * Class SQLSearchOperator
     *
     * @package FabLabRomagna
     *
     * @property-read $operatore
     * @property-read $colonna
     * @property-read $case_insensitive
     * @property-read $tipo
     * @property-read $valore
     */
    abstract class SQLOperator
    {
        /**
         * @var string $operatore Operatore di ricerca
         */
        protected $operatore;


        /**
         * @var string $campo Colonna in cui effettuare la ricerca
         */
        protected $colonna;


        /**
         * @var string $valore Valore per cui si effettua la ricerca
         */
        protected $valore;


        /**
         * @var bool $case_insensitive Indica se la ricerca dovrà essere case insensitive
         */
        protected $case_insensitive;


        /**
         * @var string|null $tipo Indica il tipo del dato
         */
        protected $tipo;

        /**
         * SQLSearchOperator constructor.
         *
         * @param string      $colonna          Colonna in cui effettuare la ricerca
         * @param string      $valore           Valore per cui si effettua la ricerca
         * @param bool        $case_insensitive Indica se il campo deve essere case insensitive
         * @param string|null $tipo
         */
        public function __construct(
            string $colonna,
            string $valore,
            bool $case_insensitive = true,
            ?string $tipo = null
        ) {
            $this->colonna = $colonna;
            $this->valore = $valore;
            $this->tipo = $tipo;
        }

        /**
         * Metodo per "compilare" la porzione di query
         *
         * @return string
         */
        public abstract function get_sql(): string;

        /**
         * Metodo per ricevere il tipo adeguato
         *
         * @return string
         *
         * @throws \Exception
         */
        public function get_type(): string
        {

            if ($this->tipo !== null) {
                return $this->tipo;
            }

            switch (gettype($this->valore)) {
                case 'string':
                    return 's';

                case 'integer':
                case 'boolean':
                case 'NULL':
                    return 'i';

                case 'float':
                    return 'd';

                default:
                    if ($this->tipo !== null) {
                        return $this->tipo;
                    }

                    throw new \Exception('Unknown data type!');
            }

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