<?php

use Jcupitt\Vips\Config;
use Jcupitt\Vips\FFI;
use Jcupitt\Vips\Image;
use Jcupitt\Vips\Interpretation;
use Psr\Log\AbstractLogger;

try {
    set_error_handler(
        fn($errno, $errstr, $errfile, $errline) => throw new ErrorException($errstr, $errno, $errno, $errfile, $errline)
    );

    require_once(__DIR__.'/../vendor/autoload.php');

    Config::setLogger(new class extends AbstractLogger {
        public function log($level, \Stringable|string $message, array $context = []): void
        {
            $context = [] === $context ? '' : ' '.json_encode($context);
            $line = "[vips - $level] $message".$context;
            printLog(substr($line, 0, 100));
        }
    });

    $image = Image::thumbnail(
        __DIR__.'/photo.jpg', 600,
        ['height' => 10_000_000, 'export-profile' => Interpretation::SRGB]
    )->writeToBuffer('.jpg', ['Q' => 75, 'strip' => TRUE, 'optimize_coding' => TRUE, 'profile'=>Interpretation::SRGB]);

    echo '<img width=600 height=452 style="border:1px solid black" alt="Photo" src="data:image/jpg;base64,'.base64_encode($image).'">';

    $shutdown_behaviour = $_GET['shutdown_behaviour'] ?? 'vips_shutdown';
    if ($shutdown_behaviour === 'vips_shutdown') {
        FFI::shutdown();
    } elseif ($shutdown_behaviour === 'vips_thread_shutdown') {
        FFI::vips()->vips_thread_shutdown();
    } elseif ($shutdown_behaviour !== 'no_shutdown') {
        respondError("Bad argument '$shutdown_behaviour' to ?shutdown_behaviour", 422);
    }

} catch (Throwable $e) {
    respondError("Caught ".get_class($e).": ".$e->getMessage()."\n".$e->getTraceAsString(), 500);
}

function printLog(string $log)
{
    file_put_contents('php://stderr', "$log\n");
}

function respondError(string $error, int $status_code) {
    printLog($error);
    http_response_code($status_code);
    echo $error;
}
