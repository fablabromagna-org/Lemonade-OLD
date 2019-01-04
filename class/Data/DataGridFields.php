<?php
declare(strict_types=1);

namespace FabLabRomagna\Data {

    /**
     * Interface DataSetFields
     *
     * @package FabLabRomagna\Data
     */
    interface DataGridFields
    {

        /**
         * Metodo utilizzato dai figli di DataSet per ricavare tutte le proprietà
         *
         * @return mixed
         */
        public function getDataGridFields(): array;

        /**
         * Metodo per richiedere la formattazione del campo (HTML)
         *
         * @param mixed $field Campo da formattare
         *
         * @return mixed
         */
        public function HTMLDataGridFormatter($field);

        /**
         * Metodo per richiedere la formattazione del campo (solo testo)
         *
         * @param mixed $field Campo da formattare
         *
         * @return mixed
         */
        public function CSVDataGridFormatter($field);
    }
}
