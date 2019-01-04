<?php
declare(strict_types=1);

namespace FabLabRomagna\Data {

    /**
     * Class HTMLDataSet
     *
     * @package FabLabRomagna
     */
    final class HTMLDataGrid extends DataGrid
    {

        /**
         * @var array $colonne_da_aggiungere Colonne da aggiungere alla fine della tabella
         */
        private $colonne_da_aggiungere = [];

        /**
         * Metodo per generare i bottoni di paging
         *
         * @param int         $pagina_attuale
         * @param null|string $qs_pagina
         *
         * @return string
         */
        private function genera_bottoni(int $pagina_attuale, ?string $qs_pagina): string
        {

            $html = '';

            $html .= '<div class="buttons has-addons is-centered" style="margin-top: 30px;">';

            // Se richiesto inserisco i bottoni del paging
            if ($this->data->limit !== null && $pagina_attuale > 1) {
                $html .= '<a href="' . self::genera_link_query_string($pagina_attuale - 1,
                        $qs_pagina) . '" class="button">';
                $html .= '<span class="icon is-small">';
                $html .= '<i class="fas fa-angle-left"></i></span>Precedente</a>';
            }

            if ($this->data->total_rows === 0) {
                $html .= '<span class="button">Nessun risultato.</span>';

            } elseif ($this->data->limit === null) {
                $html .= '<span class="button">N. risultati: ' . $this->data->total_rows . '</span>';

            } else {
                $html .= '<span class="button">' . ($this->data->offset + 1) . '-' . ($this->data->offset + count($this->data)) . ' di ' . $this->data->total_rows . '</span>';
            }

            if ($this->data->limit !== null && $this->data->limit + $this->data->offset < $this->data->total_rows) {
                $html .= '<a href="' . self::genera_link_query_string($pagina_attuale + 1,
                        $qs_pagina) . '" class="button">';
                $html .= 'Successivo';
                $html .= '<span class="icon is-small"><i class="fas fa-angle-right"></i></span></a>';
            }

            $html .= '</div>';

            return $html;
        }

        /**
         * Metodo per formattare il dataset
         *
         * @param array|null $options         Opzioni di default:
         *                                    -> qs_pagina = null (query string del paging)
         *                                    -> qs_order = null (query string del riordino)
         *                                    -> pagina_attuale = 1 (numero della pagina attualmente visualizzata)
         *
         * @return string
         *
         * @throws \Exception
         */
        public function render(?array $options = null): string
        {

            $html = '';
            $qs_pagina = null;
            $qs_ordine = null;
            $pagina_attuale = 1;
            $headers = [];

            if (is_array($options)) {

                // Query string per il paging
                if (isset($options['qs_pagina'])) {
                    if (gettype($options['qs_pagina'] === 'string') || $options['qs_pagina'] === null) {
                        $qs_pagina = $options['qs_pagina'];
                    }
                }

                // Query string per il riordino
                if (isset($options['qs_order'])) {
                    if (gettype($options['qs_order'] === 'string') || $options['qs_order'] === null) {
                        $qs_ordine = $options['qs_order'];
                    }
                }

                // Pagina attuale
                if (isset($options['pagina_attuale'])) {
                    if (gettype($options['pagina_attuale'] === 'int')) {
                        $pagina_attuale = $options['pagina_attuale'];
                    }
                }

                // Intestazioni colonne
                if (isset($options['headers'])) {
                    if (is_array($options['headers'])) {
                        $headers = $options['headers'];
                    }
                }
            }

            if ($qs_pagina !== null) {
                $html = $this->genera_bottoni($pagina_attuale, $qs_pagina);
            }

            if (count($this->data) == 0) {
                return $html;
            }

            $html .= '<div style="overflow-x: scroll">';
            $html .= '<table class="table is-fullwidth is-hoverable">';
            $html .= '<thead>';

            // Controllo che tutti i risultati siano un'implementazione di DataSetFields
            foreach ($this->data->risultato as $risultato) {
                if (!$risultato instanceof DataGridFields) {
                    throw new \Exception(get_class() . ' don\'t implements DataGridFields!');
                }
            }

            $fields = $this->data->risultato[0]->getDataGridFields();

            $order = self::dataTableOrder2array();

            // Stampo l'intestazione della tabella
            foreach ($fields as $key => $value) {

                if (isset($headers[$key])) {
                    $header = $headers[$key]->abbr !== null ? '<abbr title="' . $headers[$key]->abbr . '">' . $headers[$key]->header . '</abbr>' : $headers[$key]->header;

                } else {
                    $header = $key;
                }

                $icon_order = '';
                $order_data = '';
                foreach ($order as $value) {
                    if ($value['column'] === $key) {
                        $icon_order .= $value['order'] == 0 ? '<i class="fas fa-caret-down"></i>' : '<i class="fas fa-caret-up"></i>';
                        $order_data .= $value['order'] == 0 ? ' data-order="0"' : ' data-order="1"';
                    }
                }

                if (!in_array($key, $this->campi_rimossi)) {
                    $html .= "<th class=\"php_HTMLDataGrid\" data-column=\"{$key}\"{$order_data}>{$header}{$icon_order}</th>";
                }
            }

            foreach ($this->colonne_da_aggiungere as $colonna) {
                $html .= "<th>{$colonna[0]}</th>";
            }

            $html .= '</thead><tbody>';


            foreach ($this->data->risultato as $value) {

                $html .= '<tr>';

                foreach ($fields as $field => $valore) {
                    if (!in_array($field, $this->campi_rimossi)) {
                        $html .= "<td>{$value -> HTMLDataGridFormatter($field)}</td>";
                    }
                }

                foreach ($this->colonne_da_aggiungere as $colonna) {

                    $col = $colonna[1];

                    foreach ($fields as $field => $valore) {

                        $col = preg_replace('/{{' . $field . '}}/',
                            $colonna[2] ? $value->{$field} : $value->HTMLDataGridFormatter($field), $col);
                    }

                    $html .= "<td>{$col}</td>";
                }

                $html .= '</tr>';
            }

            $html .= '</tbody></table></div>';

            return $html;
        }

        /**
         * Metodo per convertire il dato passato via query string
         *
         * @param string $dataTableOrder
         *
         * @return array
         */
        public static function dataTableOrder2array(string $dataTableOrder = 'order'): array
        {
            if (!isset($_GET[$dataTableOrder])) {
                return [];
            }

            $order = json_decode($_GET[$dataTableOrder], true);

            foreach ($order as $value) {
                if (!isset($value['column'])) {
                    return [];
                }

                if (!isset($value['order'])) {
                    return [];
                }

                if ($value['order'] !== '0' && $value['order'] !== '1') {
                    return [];
                }
            }

            return $order;
        }


        /**
         * Metodo per generare il link del query string da quelli attuali
         *
         * @param string $page   Riferimento alla pagina
         * @param string $qsName Nome del parametro query string
         *
         * @return string
         */
        private static function genera_link_query_string($page, $qsName = 'p')
        {
            $page = (int)$page;

            if ($_SERVER['QUERY_STRING'] == '') {
                return strtok($_SERVER['REQUEST_URI'], '?') . '?' . urlencode($qsName) . '=' . $page;
            }

            $qs = explode('&', $_SERVER['QUERY_STRING']);
            $tmp = array();
            $found = false;
            foreach ($qs as $value) {
                $value = explode('=', $value);
                $tmp[$value[0]] = $value[1];
            }

            $qs = $tmp;
            foreach ($qs as $key => $value) {
                if ($qs == $qsName) {
                    $found = true;
                    $value = $page;
                }
            }

            if (!$found) {
                $qs[$qsName] = $page;
            }

            $tmp = array();
            $i = 0;
            foreach ($qs as $key => $value) {
                $tmp[$i] = $key . '=' . (string)$value;
                $i++;
            }

            $qs = $tmp;
            $qs = implode('&', $qs);

            return strtok($_SERVER['REQUEST_URI'], '?') . '?' . $qs;
        }

        /**
         * Metodo per aggiungere delle colonne alla fine della tabella
         * Non è possibile effettuare l'ordinamento su questi campi
         *
         * @param string $header    Intestazione
         * @param string $contenuto Contenuto (mettendo tra delle {{graffe}} un campo, questo verrà sostituito con il
         *                          contenuto reale)
         * @param bool   $raw       Indica se il cambo deve essere formattato o meno
         */
        public function aggiungiColonna(string $header = '', string $contenuto = '', bool $raw = true)
        {
            $this->colonne_da_aggiungere[] = [
                $header,
                $contenuto,
                $raw
            ];
        }
    }
}
