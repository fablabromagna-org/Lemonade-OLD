<?php
require_once(__DIR__ . '/../class/autoload.inc.php');

use Gregwar\Captcha\CaptchaBuilder;

try {

    header('Content-Type: image/jpeg');

    $testo = genera_testo_captcha();

    $_SESSION['captcha'] = $testo;

    $builder = new CaptchaBuilder($testo);
    $builder->setMaxBehindLines(3);
    $builder->setMaxFrontLines(3);
    $builder->setInterpolation(false);
    $builder->build(240, 80);
    $builder->output(280);

} catch (Exception $e) {

    header('Content-Type: image/png');

    $path = __DIR__ . '/../images/captcha_error.png';
    $img = fopen($path, 'r');
    echo fread($img, filesize($path));
    fclose($img);
}
?>