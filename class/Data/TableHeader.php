<?php

namespace FabLabRomagna\Data {

    /**
     * Class TableHeader
     *
     * @package FabLabRomagna\Data
     */
    final class TableHeader
    {
        /**
         * @var string $header Intestazione della colonna
         */
        private $header;


        /**
         * @var string $abbr Abbreviazione
         */
        private $abbr = null;

        /**
         * TableHeader constructor.
         *
         * @param string      $header Intestazione della colonna
         * @param string|null $abbr   Abbreviazione
         */
        public function __construct(string $header, ?string $abbr = null)
        {
            $this->header = $header;
            $this->abbr = $abbr;
        }

        /**
         * @param string $name Nome del parametro
         *
         * @return mixed
         */
        public function __get($name)
        {
            return $this->{$name};
        }
    }
}
