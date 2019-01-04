<?php

namespace FabLabRomagna\Data {

    /**
     * Class CSVDataGrid
     *
     * @package FabLabRomagna\Data
     */
    final class CSVDataGrid extends DataGrid
    {

        private $dati_renderizzati = "";

        public function render(?array $options): string
        {
            // Controllo che tutti i risultati siano un'implementazione di DataSetFields
            foreach ($this->data->risultato as $risultato) {
                if (!$risultato instanceof DataGridFields) {
                    throw new \Exception(get_class() . ' don\'t implements DataGridFields!');
                }
            }

            if (count($this->data) !== 0) {
                $arr = [];
                foreach ($this->data->risultato[0]->getDataGridFields() as $key => $value) {
                    if (!in_array($key, $this->campi_rimossi)) {
                        $arr[] = $key;
                    }
                }

                $this->dati_renderizzati .= $this->arrayToCsv($arr, ',') . "\n";
            }

            foreach ($this->data->risultato as $risultato) {

                $arr = [];

                foreach ($risultato->getDataGridFields() as $key => $value) {
                    if (!in_array($key, $this->campi_rimossi)) {
                        $arr[] = $risultato->CSVDataGridFormatter($key);
                    }
                }

                $this->dati_renderizzati .= $this->arrayToCsv($arr, ',') . "\n";
            }

            return $this->dati_renderizzati;
        }

        private function arrayToCsv(array &$fields, $delimiter)
        {
            $enclosure = '"';
            $delimiter_esc = preg_quote($delimiter, '/');
            $enclosure_esc = preg_quote($enclosure, '/');
            $output = array();
            foreach ($fields as $field) {
                if (gettype($field) === 'boolean') {
                    $field = (int)$field;
                }

                if (preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field)) {
                    $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
                } else {
                    $output[] = $field;
                }
            }
            return implode($delimiter, $output);
        }
    }
}
