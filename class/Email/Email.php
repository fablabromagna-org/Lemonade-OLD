<?php
declare(strict_types=1);

namespace FabLabRomagna\Email {

    use \FabLabRomagna\File;

    /**
     * Class Email
     *
     * @package FabLabRomagna\Email
     *
     * @property-read string $messaggio
     * @property-read string $oggetto
     * @property-read File[] $immagini_incorporate
     * @property-read File[] $allegati
     */
    class Email
    {
        /**
         * @var string $messaggio Testo dell'email
         */
        protected $messaggio;


        /**
         * @var string $oggetto Oggetto della mail
         */
        protected $oggetto;


        /**
         * @var File[] $immagini_incorporate Immagini incorporate
         */
        protected $immagini_incorporate = [];


        /**
         * @var File[] $allegati Allegati all'email
         */
        protected $allegati = [];

        /**
         * Email constructor.
         *
         * @param string $oggetto   Oggetto della mail
         * @param string $messaggio Messaggio
         */
        public function __construct(string $oggetto = '', string $messaggio = '')
        {
            $this->oggetto = $oggetto;
            $this->messaggio = $messaggio;
        }

        /**
         * Metodo per aggiungere un allegato
         *
         * @param File $file        File da associare
         * @param bool $incorporato Indica se la risorsa è incorporata alla mail o allegata
         */
        public function aggiungi_allegato(File $file, bool $incorporato = false)
        {

            if ($incorporato) {
                $this->immagini_incorporate[] = $file;
            } else {
                $this->allegati[] = $file;
            }
        }

        /**
         * Metodo per eliminare un allegato
         *
         * @param File $file File da associare
         *
         * @throws \Exception
         */
        public function elimina_allegato(File $file)
        {
            $tmp = [];

            foreach ($this->immagini_incorporate as $immagine) {
                if ($immagine->id_file !== $file->id_file) {
                    $tmp[] = $immagine;
                }
            }

            $this->immagini_incorporate = $tmp;

            $tmp = [];

            foreach ($this->allegati as $allegato) {
                if ($allegato->id_file !== $file->id_file) {
                    $tmp[] = $allegato;
                }
            }

            $this->allegati = $tmp;
        }

        /**
         * @param $name
         *
         * @return mixed
         */
        public function __get($name)
        {
            return $this->{$name};
        }
    }
}
