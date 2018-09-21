<?php

declare(strict_types=1);

namespace FabLabRomagna\SQLOperator {

    /**
     * Class LowerThanEquals
     *
     * @package FabLabRomagna\SQLOperator
     *
     * @property-read $operatore
     * @property-read $colonna
     * @property-read $case_insensitive
     * @property-read $tipo
     * @property-read $valore
     */
    final class LowerThanEquals extends SQLOperator
    {
        protected $operatore = 'LIKE';

        /**
         * LowerThanEquals constructor.
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
            parent::__construct($colonna, $valore, $case_insensitive, $tipo);
        }

        /**
         * Metodo che restituisce il completamento della query SQL
         *
         * @return string
         *
         * @throws \Exception
         */
        public function get_sql(): string
        {

            $cs = '';

            if ($this->get_type() === 's' && $this->case_insensitive) {
                $cs = 'COLLATE utf8mb4_unicode_ci ';
            }

            return $this->colonna . ' ' . $cs . ' <= ?';
        }
    }
}