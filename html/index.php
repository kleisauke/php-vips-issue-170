<?php

use Jcupitt\Vips\FFI;
use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;

try {
    set_error_handler(
        fn($errno, $errstr, $errfile, $errline) => throw new ErrorException($errstr, $errno, $errno, $errfile, $errline)
    );

    require_once(__DIR__.'/../vendor/autoload.php');

    $image = Image::thumbnail(
        __DIR__.'/photo.jpg', 600,
        ['height' => 10_000_000, 'export-profile' => Interpretation::SRGB]
    )->writeToBuffer('.jpg', ['Q' => 75, 'strip' => TRUE, 'optimize_coding' => TRUE, 'profile'=>Interpretation::SRGB]);

    echo '<img width=600 height=452 style="border:1px solid black" alt="Photo" src="data:image/jpg;base64,'.base64_encode($image).'">';

    $shutdown_behaviour = $_GET['shutdown_behaviour'] ?? 'vips_shutdown';
    if ($shutdown_behaviour === 'vips_shutdown') {
        FFI::shutdown();
    } elseif ($shutdown_behaviour !== 'no_shutdown') {
        respondError("Bad argument '$shutdown_behaviour' to ?shutdown_behaviour", 422);
    }

} catch (Throwable $e) {
    respondError("Caught ".get_class($e).": ".$e->getMessage()."\n".$e->getTraceAsString(), 500);
}

function printLog(string $log)
{
    file_put_contents('php://stderr', "\n$log\n");
}

function respondError(string $error, int $status_code) {
    printLog($error);
    http_response_code($status_code);
    echo $error;
}
