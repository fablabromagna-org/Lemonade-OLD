<?php

declare(strict_types=1);

namespace FabLabRomagna\SQLOperator {

    /**
     * Class SQLOr
     *
     * @package FabLabRomagna\SQLOperator
     */
    final class SQLOr extends SQLOperator
    {

        /**
         * SQLOr constructor.
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
            return 'OR';
        }
    }
}
