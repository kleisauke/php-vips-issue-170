<?php

// Wrap the proof of concept in enough logging / error handling to ensure the test script gets
// a reliable result.
//
// See index.php for the actual VIPS operations

try {
    set_error_handler(
        fn($errno, $errstr, $errfile, $errline) => throw new ErrorException($errstr, $errno, $errno, $errfile, $errline)
    );

    // Actually run the VIPS operations from the reproduction
    require_once __DIR__.'/index.php';

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
