<?php

declare(strict_types=1);

namespace FabLabRomagna\SQLOperator {

    /**
     * Class SQLAnd
     *
     * @package FabLabRomagna\SQLOperator
     */
    final class SQLAnd extends SQLOperator
    {

        /**
         * SQLAnd constructor.
         */
        public function __construct()
        {
            parent::__construct('', '', true, '');
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
            return 'AND';
        }
    }
}
