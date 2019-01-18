<?php
declare(strict_types=1);

namespace FabLabRomagna\Email {

    /**
     * Class Configuration
     *
     * @package FabLabRomagna\Email
     *
     * @property-read string $host
     * @property-read int    $port
     * @property-read string $username
     * @property-read string $password
     */
    class Configuration
    {
        /**
         * @var string $host Host del server SMTP
         */
        private $host;


        /**
         * @var int $port Porta del server SMTP
         */
        private $port;


        /**
         * @var string $username Nome utente del server SMTP
         */
        private $username;


        /**
         * @var string $password Password del server SMTP
         */
        private $password;

        /**
         * Configuration constructor.
         *
         * @param string $host     Host del server SMTP
         * @param int    $port     Porta del server SMTP
         * @param string $username Nome utente del server SMTP
         * @param string $password Password del server SMTP
         */
        public function __construct(string $host, int $port, string $username, string $password)
        {
            $this->host = $host;
            $this->port = $port;
            $this->username = $username;
            $this->password = $password;
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
