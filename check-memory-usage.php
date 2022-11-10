<?php
error_reporting(E_ALL);
set_error_handler(
    fn($errno, $errstr, $errfile, $errline) => throw new ErrorException($errstr, $errno, $errno, $errfile, $errline)
);

$result_summary = trim(file_get_contents(__DIR__.'/result-summary.txt'));
$results        = array_map('trim', explode("\n", $result_summary));

print "Parsing results\n";

$memory_metrics = [];
foreach ($results as $result) {
    print "$result\n";
    if ( ! preg_match('/Memory: (?P<qty>[\d.]+)(?P<unit>[MG]iB) /', $result, $matches)) {
        throw new \UnexpectedValueException('Unexpected result format '.$result);
    }

    $container_memory_mib = match ($matches['unit']) {
        'MiB' => (float) $matches['qty'],
        'GiB' => (float) $matches['qty'] * 1024
    };
    $memory_metrics[]     = $container_memory_mib;
}


$max_memory           = max($memory_metrics);
$final_memory         = $memory_metrics[count($memory_metrics) - 1];
$has_memory_decreased = FALSE;

for ($i = 1; $i < count($memory_metrics); $i++) {
    if ($memory_metrics[$i] < $memory_metrics[$i - 1]) {
        $has_memory_decreased = TRUE;
        break;
    }
}

$is_final_less_than_max = $final_memory < $max_memory;
print "\n";
print "Maximum memory usage: {$max_memory}MiB\n";
print "Has ever decreased?   ".($has_memory_decreased ? 'Yes' : 'No')."\n";
print "Ended less than max?  ".($is_final_less_than_max ? 'Yes' : 'No')."\n";

if ($has_memory_decreased && $is_final_less_than_max) {
    print "Memory usage is stable\n";
    exit(0);
} else {
    print "Memory usage check failed\n";
    exit(1);
}
