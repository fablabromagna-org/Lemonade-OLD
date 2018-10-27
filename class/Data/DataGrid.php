<?php
declare(strict_types=1);

namespace FabLabRomagna\Data {

    /**
     * Class DataSet
     *
     * @package FabLabRomagna\DataTable
     */
    abstract class DataGrid
    {

        /**
         * @var array $data Dati da stampare nella griglia
         */
        protected $data;


        /**
         * @var array $intestazioni Dati delle intestazioni di colonna
         */
        protected $intestazioni;


        /**
         * @var array $campi_rimossi Campi rimossi dalla visualizzazione
         */
        protected $campi_rimossi = [];

        /**
         * DataSet constructor.
         *
         * @param \FabLabRomagna\RisultatoRicerca $dati         Dati per cui creare la tabella
         * @param array|null                      $intestazione Intestazioni della tabella
         *
         */
        public function __construct(\FabLabRomagna\RisultatoRicerca $dati, ?array $intestazione = null)
        {
            $this->data = $dati;
            $this->intestazioni = $intestazione;
        }

        /**
         * Metodo per formattare il dataset
         *
         * @param array|null $options
         *
         * @return mixed
         */
        abstract function render(?array $options);

        /**
         * @param string|int $campo Permette di aggiungere un campo da ignorare durante il rendering
         */
        public function remove_field($campo)
        {
            $this->campi_rimossi[] = $campo;
        }
    }
}
