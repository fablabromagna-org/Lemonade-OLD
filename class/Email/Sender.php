<?php
declare(strict_types=1);

namespace {

    require_once(__DIR__ . '/../../vendor/autoload.php');
}

namespace FabLabRomagna\Email {

    use FabLabRomagna\File;
    use PHPMailer\PHPMailer\PHPMailer;

    /**
     * Class Sender
     *
     * @package FabLabRomagna\Email
     */
    class Sender
    {
        /**
         * @var PHPMailer $mailer Istanza di PHPMailer
         */
        private $mailer;


        /**
         * @var Configuration $conf Configurazione di SMTP
         */
        private $conf;

        /**
         * Sender constructor.
         *
         * @param Configuration $conf  Configurazione SMTP
         * @param Email         $email Email da inviare
         *
         * @throws \Exception
         */
        public function __construct(Configuration $conf, Email $email)
        {

            $this->mailer = new PHPMailer(true);
            $this->conf = $conf;

            $this->mailer->isSMTP();
            $this->mailer->SMTPAuth = true;

            $this->mailer->Host = $this->conf->host;
            $this->mailer->Port = $this->conf->port;

            $this->mailer->Username = $this->conf->username;
            $this->mailer->Password = $this->conf->password;

            $this->mailer->Subject = $email->oggetto;
            $this->mailer->msgHTML($email->messaggio);

            foreach ($email->allegati as $allegato) {

                /**
                 * @var File $allegato
                 */

                if ($allegato->file === null) {
                    $allegato->richiedi_file();
                }

                $this->mailer->addStringAttachment($allegato->file, $allegato->nome, PHPMailer::ENCODING_BASE64,
                    $allegato->mime);

            }

            foreach ($email->immagini_incorporate as $img) {

                /**
                 * @var File $allegato
                 */

                if ($img->file === null) {
                    $img->richiedi_file();
                }

                $this->mailer->addStringEmbeddedImage($img->file, $img->md5, $img->nome, PHPMailer::ENCODING_BASE64,
                    $img->mime);
            }
        }

        /**
         * Metodo per inviare la
         *
         * @param string[] $a   Destinatario
         * @param string[] $cc  Destinatario in copia carbone
         * @param string[] $ccn Destinatario in copia carbone nascosta
         *
         * @return bool
         * @throws \PHPMailer\PHPMailer\Exception
         */
        public function send($a = [], $cc = [], $ccn = [])
        {
            $this->mailer->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);

            if (defined('EMAIL_REPLY_TO')) {
                $this->mailer->addReplyTo(EMAIL_REPLY_TO, EMAIL_REPLY_TO_NAME);
            }

            foreach ($a as $address) {
                $this->mailer->addAddress($address);
            }

            foreach ($cc as $address) {
                $this->mailer->addCC($address);
            }

            foreach ($ccn as $address) {
                $this->mailer->addbcc($address);
            }

            return $this->mailer->send();
        }
    }
}
